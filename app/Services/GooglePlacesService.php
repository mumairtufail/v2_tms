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

        $this->log('AUTOCOMPLETE REQUEST', [
            'query'         => $input,
            'session_token' => $sessionToken ?? '(none)',
        ]);

        $response = $this->http()->post('places:autocomplete', $payload);

        $body            = $response->json() ?? [];
        $suggestionCount = count(data_get($body, 'suggestions', []));

        $this->log('AUTOCOMPLETE RESPONSE', [
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

        $this->log('DETAILS REQUEST', [
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

        $this->log('DETAILS RESPONSE', [
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

        $this->log('ADDRESS PARSED', [
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
        Log::channel($this->logChannel)->debug($event, $context);
    }
}
