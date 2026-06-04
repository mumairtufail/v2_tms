# Order Edit: Google Places Full Technical Manifest

This document is the **Source of Truth** for the Google Places address population feature. It contains the complete source code for all components and a detailed explanation of the data flow.

---

## 1. Backend Architecture

The backend handles API proxying, security (API key protection), and complex address parsing.

### Service: `app/Services/GooglePlacesService.php`
This service manages the two-step Google Places V1 API workflow and transforms the raw JSON into a flat structure.

```php
<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GooglePlacesService
{
    private string $apiKey;

    private string $logChannel;

    public function __construct()
    {
        $this->apiKey     = (string) config('services.google.maps_api_key');
        $this->logChannel = (string) config('services.google.places_log_channel', 'google_places');
    }

    public function autocomplete(string $input, ?string $sessionToken = null): array
    {
        $input = trim($input);

        if ($input === '' || mb_strlen($input) < 3) {
            return [];
        }

        $payload = array_filter([
            'input'        => $input,
            'sessionToken' => $sessionToken,
        ], static fn ($v) => $v !== null && $v !== '');

        $this->log('Step 1 | AUTOCOMPLETE REQUEST', [
            'query'         => $input,
            'session_token' => $sessionToken ?? '(none)',
        ]);

        $response = $this->http()->post('places:autocomplete', $payload);

        $body            = $response->json() ?? [];
        $suggestionCount = count(data_get($body, 'suggestions', []));

        $this->log('Step 1 | AUTOCOMPLETE RESPONSE', [
            'http_status'       => $response->status(),
            'success'           => $response->successful() ? 'YES' : 'NO',
            'suggestions_found' => $suggestionCount,
            'raw_body'          => $body,
        ]);

        if (! $response->successful()) {
            $error = $this->extractErrorMessage($body, 'Autocomplete request failed.');
            $this->log('AUTOCOMPLETE ERROR', [
                'reason'      => $error,
                'http_status' => $response->status(),
            ]);
            throw new RuntimeException($error);
        }

        $suggestions = data_get($body, 'suggestions', []);

        $parsed = array_values(array_filter(array_map(function (array $suggestion): ?array {
            $prediction = data_get($suggestion, 'placePrediction');

            if (! is_array($prediction)) {
                return null;
            }

            return [
                'placeId'       => data_get($prediction, 'placeId'),
                'mainText'      => data_get($prediction, 'structuredFormat.mainText.text', ''),
                'secondaryText' => data_get($prediction, 'structuredFormat.secondaryText.text', ''),
            ];
        }, $suggestions)));

        $this->log('AUTOCOMPLETE PARSED', [
            'query'   => $input,
            'results' => array_map(fn ($r) => $r['mainText'] . ' — ' . $r['secondaryText'], $parsed),
        ]);

        return $parsed;
    }

    public function details(string $placeId, ?string $sessionToken = null): array
    {
        $placeId = trim($placeId);

        if ($placeId === '') {
            throw new RuntimeException('Place ID is required.');
        }

        $fields = ['id', 'displayName', 'formattedAddress', 'addressComponents', 'location'];

        $this->log('Step 2 | DETAILS REQUEST', [
            'place_id'      => $placeId,
            'session_token' => $sessionToken ?? '(none)',
            'fields'        => implode(', ', $fields),
        ]);

        $response = $this->http()
            ->withQueryParameters(array_filter([
                'sessionToken' => $sessionToken,
                'fields'       => implode(',', $fields),
            ], static fn ($v) => $v !== null && $v !== ''))
            ->get("places/{$placeId}");

        $body = $response->json() ?? [];

        $this->log('Step 2 | DETAILS RESPONSE', [
            'http_status'      => $response->status(),
            'success'          => $response->successful() ? 'YES' : 'NO',
            'display_name'     => data_get($body, 'displayName.text', '(none)'),
            'formatted_address'=> data_get($body, 'formattedAddress', '(none)'),
            'raw_body'         => $body,
        ]);

        if (! $response->successful()) {
            $error = $this->extractErrorMessage($body, 'Place details request failed.');
            $this->log('DETAILS ERROR', [
                'place_id'    => $placeId,
                'reason'      => $error,
                'http_status' => $response->status(),
            ]);
            throw new RuntimeException($error);
        }

        return $body;
    }

    public function parseAddress(array $place): array
    {
        $components = data_get($place, 'addressComponents', []);

        $getComponent = function (array $types) use ($components): string {
            foreach ($components as $component) {
                if (count(array_diff($types, data_get($component, 'types', []))) === 0) {
                    return (string) data_get($component, 'longText', '');
                }
            }
            return '';
        };

        $getShortComponent = function (array $types) use ($components): string {
            foreach ($components as $component) {
                if (count(array_diff($types, data_get($component, 'types', []))) === 0) {
                    return (string) data_get($component, 'shortText', '');
                }
            }
            return '';
        };

        $streetNumber = $getComponent(['street_number']);
        $route        = $getComponent(['route']);

        $parsed = [
            'company_name'      => (string) data_get($place, 'displayName.text', ''),
            'address_1'         => trim(($streetNumber !== '' ? $streetNumber . ' ' : '') . $route),
            'address_2'         => '',
            'city'              => $getComponent(['locality']) ?: $getComponent(['sublocality_level_1']),
            'state'             => $getShortComponent(['administrative_area_level_1']),
            'zip'               => $getComponent(['postal_code']),
            'country'           => $getShortComponent(['country']),
            'lat'               => data_get($place, 'location.latitude'),
            'lng'               => data_get($place, 'location.longitude'),
            'formatted_address' => (string) data_get($place, 'formattedAddress', ''),
        ];

        $this->log('Step 2 | ADDRESS PARSED', [
            'company'  => $parsed['company_name'],
            'address'  => $parsed['address_1'],
            'city'     => $parsed['city'],
            'state'    => $parsed['state'],
            'zip'      => $parsed['zip'],
            'country'  => $parsed['country'],
            'lat_lng'  => $parsed['lat'] . ', ' . $parsed['lng'],
            'full'     => $parsed['formatted_address'],
        ]);

        return $parsed;
    }

    private function http()
    {
        if ($this->apiKey === '') {
            $this->log('CONFIG ERROR', [
                'reason' => 'GOOGLE_MAPS_API_KEY is not set in .env',
            ]);
            throw new RuntimeException('Google Maps API key is not configured.');
        }

        return Http::baseUrl('https://places.googleapis.com/v1')
            ->timeout(30)
            ->connectTimeout(10)
            ->acceptJson()
            ->asJson()
            ->withHeaders(['X-Goog-Api-Key' => $this->apiKey]);
    }

    private function extractErrorMessage(?array $body, string $fallback): string
    {
        return (string) data_get($body, 'error.message', $fallback);
    }

    private function log(string $event, array $context = []): void
    {
        // Log to both the default places log and the orderedit-location log for unified visibility
        Log::channel($this->logChannel)->debug($event, $context);
        Log::channel('orderedit-location')->info("Google API | " . $event, $context);
    }
}
```

### Controller: `app/Http/Controllers/Api/GooglePlacesController.php`
Exposes the secure proxy endpoints.

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GooglePlacesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class GooglePlacesController extends Controller
{
    public function __construct(private readonly GooglePlacesService $googlePlacesService)
    {
    }

    public function autocomplete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'input' => ['required', 'string', 'min:3'],
            'sessionToken' => ['nullable', 'string'],
        ]);

        try {
            $results = $this->googlePlacesService->autocomplete(
                $validated['input'],
                $validated['sessionToken'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function details(Request $request, string $placeId): JsonResponse
    {
        $validated = $request->validate([
            'sessionToken' => ['nullable', 'string'],
        ]);

        try {
            $place = $this->googlePlacesService->details(
                $placeId,
                $validated['sessionToken'] ?? null
            );

            $parsed = $this->googlePlacesService->parseAddress($place);

            \Illuminate\Support\Facades\Log::channel('orderedit-location')->info('Google Place Selected', [
                'placeId' => $placeId,
                'parsed' => $parsed,
                'raw_place' => $place,
            ]);

            return response()->json([
                'success' => true,
                'data' => $place,
                'parsed' => $parsed,
            ]);
        } catch (Throwable $exception) {
            \Illuminate\Support\Facades\Log::channel('orderedit-location')->error('Google Place Selection Failed', [
                'placeId' => $placeId,
                'message' => $exception->getMessage(),
                'trace'   => $exception->getTraceAsString()
            ]);
            
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch place details: ' . $exception->getMessage(),
            ], 500);
        }
    }
}
```

---

## 2. Frontend Logic

The frontend uses Alpine.js components to handle user interaction and synchronize data across multiple order legs.

### Logic Component: `resources/js/company-autocomplete.js`
Manages the search state, API calls, and global event dispatching.

```javascript
import { PlacesAPI } from './places';

export default function companyAutocomplete(config = {}) {
    return {
        query: config.initialQuery || '',
        prefix: config.prefix || '',
        stopIndex: config.stopIndex !== undefined ? config.stopIndex : null,
        results: [],
        googleResults: [],
        isLoading: false,
        isGoogleLoading: false,
        showDropdown: false,
        mode: 'google', // 'local' | 'google'
        debug: ['localhost', '127.0.0.1'].includes(window.location.hostname) || window.localStorage.getItem('googlePlacesDebug') === '1',

        init() {
            PlacesAPI.init({ debug: this.debug });

            this.$watch('query', (value) => {
                if (value.length < 3) {
                    this.googleResults = [];
                    this.showDropdown = false;
                }
            });
        },

        async search() {
            // Re-route normal search to directly perform Google search.
            if (this.query.length < 3) return;
            this.mode = 'google';
            this.searchGoogle();
        },

        async searchGoogle() {
            if (this.query.length < 3) return;

            this.mode = 'google';
            this.isGoogleLoading = true;
            this.showDropdown = true;

            try {
                const suggestions = await PlacesAPI.autocomplete(this.query);
                this.googleResults = suggestions.map((s) => ({
                    placeId: s.placeId,
                    mainText: s.mainText,
                    secondaryText: s.secondaryText || '',
                }));
            } catch (error) {
                console.error('[PlacesAPI] search error:', error);
                this.googleResults = [];
            } finally {
                this.isGoogleLoading = false;
            }
        },

        async select(result) {
            console.log('[company-autocomplete] selecting:', result.mainText, 'for leg:', this.stopIndex);
            this.query = result.mainText;
            this.googleResults = [];
            this.showDropdown = true;
            this.isGoogleLoading = true;

            try {
                const placeDetails = await PlacesAPI.getDetails(result.placeId);
                console.log('[company-autocomplete] details received:', placeDetails);
                
                if (placeDetails?.parsed) {
                    // Dispatch a global event that the Order Form can definitely hear
                    window.dispatchEvent(new CustomEvent('google-place-selected', {
                        detail: {
                            ...placeDetails.parsed,
                            targetPrefix: this.prefix,
                            targetIndex: this.stopIndex
                        }
                    }));
                    console.log('[company-autocomplete] global event dispatched');
                }
            } catch (error) {
                console.error('[PlacesAPI] selection error:', error);
            } finally {
                this.isGoogleLoading = false;
                this.showDropdown = false;
            }
        },

        close() {
            this.showDropdown = false;
        },
    };
}
```

---

## 3. UI Implementation (Blade)

### Search Partial: `resources/views/livewire/places-autocomplete.blade.php`
The UI for the search input and result dropdown.

```html
<div
    @mousedown.outside="close()"
>
    <label class="block text-[10px] font-medium text-gray-400 uppercase">Company Name</label>

    <div class="relative group">
        <input type="text"
               x-model="query"
               @input.debounce.300ms="searchGoogle"
               @focus="if(query.length >= 3) showDropdown = true"
               class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
               placeholder="Search company or address via Google...">

        <div x-show="isGoogleLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
            <svg class="animate-spin h-4 w-4 text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    {{-- Dropdown --}}
    <div x-show="showDropdown && query.length >= 3"
         class="absolute left-0 right-0 top-full mt-1 z-[100] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow-xl overflow-hidden"
         x-cloak>

        {{-- Google search loading --}}
        <template x-if="isGoogleLoading">
            <div class="flex items-center gap-2.5 px-4 py-3.5">
                <svg class="animate-spin h-4 w-4 text-blue-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-[12px] text-slate-500 dark:text-slate-400">Searching Google Places...</span>
            </div>
        </template>

        {{-- Google results --}}
        <template x-if="!isGoogleLoading">
            <div>
                <div class="px-3 py-1.5 flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800">
                    <svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 533.5 544.3" xmlns="http://www.w3.org/2000/svg">
                        <path d="M533.5 278.4c0-18.5-1.5-37.1-4.7-55.3H272.1v104.8h147c-6.1 33.8-25.7 63.7-54.4 82.7v68h87.7c51.5-47.4 81.1-117.4 81.1-200.2z" fill="#4285f4"/>
                        <path d="M272.1 544.3c73.4 0 135.3-24.1 180.4-65.7l-87.7-68c-24.4 16.6-55.9 26-92.6 26-71 0-131.2-47.9-152.8-112.3H28.9v70.1c46.2 91.9 140.3 149.9 243.2 149.9z" fill="#34a853"/>
                        <path d="M119.3 324.3c-11.4-33.8-11.4-70.4 0-104.2V150H28.9c-38.6 76.9-38.6 167.5 0 244.4l90.4-70.1z" fill="#fbbc04"/>
                        <path d="M272.1 107.7c38.8-.6 76.3 14 104.4 40.8l77.7-77.7C405 24.6 339.7-.8 272.1 0 169.2 0 75.1 58 28.9 150l90.4 70.1c21.5-64.5 81.8-112.4 152.8-112.4z" fill="#ea4335"/>
                    </svg>
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Google Places</span>
                </div>

                <template x-if="googleResults.length > 0">
                    <div class="max-h-52 overflow-y-auto">
                        <template x-for="result in googleResults" :key="result.placeId">
                            <button type="button"
                                    @mousedown.prevent="select(result)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 flex flex-col gap-0.5 border-b border-slate-100 dark:border-slate-800 last:border-0 transition-colors">
                                <span class="text-sm font-semibold text-slate-800 dark:text-slate-100" x-text="result.mainText"></span>
                                <span class="text-[10px] text-slate-400" x-text="result.secondaryText"></span>
                            </button>
                        </template>
                    </div>
                </template>

                <template x-if="googleResults.length === 0">
                    <div class="px-4 py-4 text-center">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">No results found for "<span class="font-medium text-slate-700 dark:text-slate-200" x-text="query"></span>"</p>
                        <p class="text-[10px] text-slate-400 mt-1">Try a different name or address</p>
                    </div>
                </template>
            </div>
        </template>
    </div>
</div>
```

### Main Partial: `resources/views/v2/company/orders/partials/location-fields.blade.php`
Synchronizes the global selection event with the parent Alpine.js form state.

```html
<div class="grid grid-cols-1 gap-4 p-4 bg-white dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-800 shadow-sm"
     x-data="companyAutocomplete({ 
        initialQuery: stop.{{ $prefix }}.company_name,
        prefix: '{{ $prefix }}',
        stopIndex: stopIndex
     })"
     @google-place-selected.window="
        if ($event.detail.targetIndex === stopIndex && $event.detail.targetPrefix === '{{ $prefix }}') {
            console.log('--- Leg ' + stopIndex + ' ({{ $prefix }}) catching global sync ---');
            const data = $event.detail;
            
            // 1. Sync search box
            query = data.company_name || '';
            
            // 2. Build updated stop data
            const freshStop = JSON.parse(JSON.stringify(stops[stopIndex]));
            freshStop.{{ $prefix }} = {
                ...freshStop.{{ $prefix }},
                company_name: data.company_name || '',
                address_1: data.address_1 || '',
                address_2: data.address_2 || '',
                city: data.city || '',
                state: data.state || '',
                zip: data.zip || '',
                country: data.country || '',
                lat: data.lat || null,
                lng: data.lng || null
            };
            
            // 3. Update global state - this triggers total form reactivity
            stops[stopIndex] = freshStop;
            console.log('--- Global Sync Complete for Leg ' + stopIndex + ' ---');
        }
     ">
    {{-- Row 1: Company Name with Google Places Autocomplete --}}
    <div class="relative" @input="stop.{{ $prefix }}.company_name = query">
        @include('livewire.places-autocomplete')
    </div>

    {{-- Row 2: Address 1 --}}
    <div :class="isGoogleLoading ? 'opacity-50 animate-pulse' : ''">
        <label class="block text-[10px] font-medium text-gray-400 uppercase">Address 1</label>
        <input type="text"
               x-model="stop.{{ $prefix }}.address_1"
               class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
               placeholder="Street address">
    </div>

    {{-- ... other address fields ... --}}

    {{-- Row 5: Country --}}
    <div :class="isGoogleLoading ? 'opacity-50 animate-pulse' : ''">
        <label class="block text-[10px] font-medium text-gray-400 uppercase">Country</label>
        <input type="text"
               x-model="stop.{{ $prefix }}.country"
               class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
               placeholder="Country (e.g. US, DE)">
    </div>
</div>
```

---

## 4. Multi-Leg Scoping Logic

The Order Form can have an unlimited number of legs (stops). Each leg contains a **Shipper** and a **Consignee**.

*   **Prefixing:** Every location box is passed a `$prefix` variable (`'shipper'` or `'consignee'`).
*   **Indexing:** Every location box is aware of its `stopIndex` from the parent `x-for` loop.
*   **Global Dispatching:** When a place is selected, the data is broadcast to the entire browser window with a `targetIndex` and `targetPrefix`.
*   **Targeted Syncing:** Only the specific location box that matches both the **Index** and the **Prefix** will process the data. This prevents a selection in Leg 0's Shipper from accidentally populating Leg 1's Consignee.

---

## 5. Field Synchronization List

| Field | Source from Google | Purpose |
| :--- | :--- | :--- |
| `company_name` | `displayName.text` | Primary search/display field |
| `address_1` | `street_number` + `route` | Primary physical address |
| `city` | `locality` or `sublocality` | City level filtering |
| `state` | `administrative_area_level_1` (short) | State/Region code |
| `zip` | `postal_code` | Postal routing |
| `country` | `country` (short) | International routing |
| `lat` | `location.latitude` | Mapping & distance calculation |
| `lng` | `location.longitude` | Mapping & distance calculation |

---

## 6. Monitoring & Troubleshooting

*   **Backend Log:** `storage/logs/orderedit-location.log` (Chronological API hits)
*   **Frontend Log:** F12 Console (Event traces and reactivity confirmations)
*   **API Timeout:** Explicitly set to **30 seconds** to prevent cURL 500 errors on complex lookups.
