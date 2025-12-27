<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Geocode an address using Nominatim (OpenStreetMap's free geocoding service)
     */
    public function geocodeAddress($address)
    {
        try {
            $response = Http::get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 5,
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $results = $response->json();
                
                $locations = collect($results)->map(function ($result) {
                    return [
                        'display_name' => $result['display_name'],
                        'latitude' => (float) $result['lat'],
                        'longitude' => (float) $result['lon'],
                        'address' => $result['display_name'],
                    ];
                });

                return $locations->toArray();
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Reverse geocode coordinates to get address
     */
    public function reverseGeocode($latitude, $longitude)
    {
        try {
            $response = Http::get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'display_name' => $result['display_name'] ?? '',
                    'latitude' => (float) $latitude,
                    'longitude' => (float) $longitude,
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Reverse geocoding error: ' . $e->getMessage());
            return null;
        }
    }
}