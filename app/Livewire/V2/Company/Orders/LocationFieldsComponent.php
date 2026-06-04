<?php

namespace App\Livewire\V2\Company\Orders;

use Livewire\Component;
use Livewire\Attributes\Modelable;
use App\Services\GooglePlacesService;
use Illuminate\Support\Facades\Http;

class LocationFieldsComponent extends Component
{
    /**
     * The prefix for the fields ('shipper' or 'consignee').
     */
    public $prefix;

    /**
     * The stop data object from Alpine.
     */
    #[Modelable]
    public $stop;

    /**
     * Autocomplete state
     */
    public $query = '';
    public $googleResults = [];
    public $isGoogleLoading = false;
    public $showDropdown = false;
    public $sessionToken = null;

    public function mount($prefix, $stop = null)
    {
        $this->prefix = $prefix;
        $this->stop = $stop ?? [$prefix => []];
        $this->sessionToken = (string) \Illuminate\Support\Str::uuid();
        
        // Initialize query if company name exists
        if (isset($this->stop[$this->prefix]['company_name'])) {
            $this->query = $this->stop[$this->prefix]['company_name'];
        }
    }

    /**
     * Search trigger Google API
     */
    public function updatedQuery($value)
    {
        \Illuminate\Support\Facades\Log::info('LocationFieldsComponent: updatedQuery called', ['value' => $value]);
        
        // Update the stop data immediately as the user types the company name
        if (!isset($this->stop[$this->prefix])) {
            $this->stop[$this->prefix] = [];
        }
        $this->stop[$this->prefix]['company_name'] = $value;

        if (strlen($value) < 3) {
            $this->googleResults = [];
            $this->showDropdown = false;
            return;
        }

        $this->searchGoogle();
    }

    /**
     * Search with Google Places
     */
    public function searchGoogle()
    {
        if (strlen($this->query) < 3) return;

        $this->isGoogleLoading = true;
        $this->showDropdown = true;

        try {
            $service = app(GooglePlacesService::class);
            $this->googleResults = $service->autocomplete($this->query, $this->sessionToken);
            \Illuminate\Support\Facades\Log::info('LocationFieldsComponent: searchGoogle results', ['count' => count($this->googleResults)]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('LocationFieldsComponent: searchGoogle error', ['message' => $e->getMessage()]);
            $this->googleResults = [];
        } finally {
            $this->isGoogleLoading = false;
        }
    }

    /**
     * Select a Google Place
     */
    public function selectGoogle($placeId)
    {
        $this->isGoogleLoading = true;
        $this->showDropdown = false; // Close immediately on server too
        $this->googleResults = [];   // Clear results immediately
        
        \Illuminate\Support\Facades\Log::info('LocationFieldsComponent: selectGoogle started', ['placeId' => $placeId]);
        
        try {
            $service = app(GooglePlacesService::class);
            $details = $service->details($placeId, $this->sessionToken);
            $parsed = $service->parseAddress($details);

            $this->query = $parsed['company_name'];
            $this->fillFields($parsed);
            
            // Generate new token for next session
            $this->sessionToken = (string) \Illuminate\Support\Str::uuid();

            // Log selection
            \Illuminate\Support\Facades\Log::channel('orderedit-location')->info('Google Place Selected', [
                'prefix' => $this->prefix,
                'placeId' => $placeId,
                'parsed' => $parsed
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('LocationFieldsComponent: selectGoogle error', ['message' => $e->getMessage()]);
            \Illuminate\Support\Facades\Log::channel('orderedit-location')->error('Failed to select Google Place', [
                'error' => $e->getMessage(),
                'placeId' => $placeId
            ]);
        } finally {
            $this->isGoogleLoading = false;
        }
    }

    private function fillFields($data)
    {
        // Copy the array to properly trigger Livewire's change tracking for nested arrays
        $currentStop = $this->stop ?? [];
        if (!isset($currentStop[$this->prefix])) {
            $currentStop[$this->prefix] = [];
        }

        foreach ($data as $key => $value) {
            $currentStop[$this->prefix][$key] = $value;
        }
        
        // Explicitly ensure lat/lng and formatted_address are updated if present in data
        if (isset($data['lat'])) $currentStop[$this->prefix]['lat'] = $data['lat'];
        if (isset($data['lng'])) $currentStop[$this->prefix]['lng'] = $data['lng'];
        if (isset($data['formatted_address'])) $currentStop[$this->prefix]['formatted_address'] = $data['formatted_address'];

        // Re-assigning to trigger #[Modelable] correctly up to the parent
        $this->stop = $currentStop;

        // Also notify via dispatch if any JS needs it
        $this->dispatch('stop-updated-' . $this->prefix, $this->stop);
    }

    public function render()
    {
        return view('livewire.v2.company.orders.location-fields-component');
    }
}