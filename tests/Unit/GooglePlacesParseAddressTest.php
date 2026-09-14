<?php

namespace Tests\Unit;

use App\Services\GooglePlacesService;
use Tests\TestCase;

class GooglePlacesParseAddressTest extends TestCase
{
    /**
     * Real Places API (New) response for "Google Jakarta": no locality, and the
     * tower name arrives as a component without types.
     */
    public function test_city_building_and_phone_are_parsed_when_there_is_no_locality(): void
    {
        $place = [
            'formattedAddress' => 'Pacific Century Place Tower Level 45 SCBD Lot 10, Jl. Jenderal Sudirman No.53, Senayan, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12190, Indonesia',
            'internationalPhoneNumber' => '+62 21 5098 5700',
            'addressComponents' => [
                ['longText' => 'Pacific Century Place Tower Level 45 SCBD Lot 10'],
                ['longText' => 'No.53', 'shortText' => 'No.53', 'types' => ['street_number']],
                ['longText' => 'Jalan Jenderal Sudirman', 'shortText' => 'Jl. Jenderal Sudirman', 'types' => ['route']],
                ['longText' => 'Senayan', 'shortText' => 'Senayan', 'types' => ['administrative_area_level_4', 'political']],
                ['longText' => 'Kecamatan Kebayoran Baru', 'shortText' => 'Kec. Kby. Baru', 'types' => ['administrative_area_level_3', 'political']],
                ['longText' => 'Kota Jakarta Selatan', 'shortText' => 'Kota Jakarta Selatan', 'types' => ['administrative_area_level_2', 'political']],
                ['longText' => 'Daerah Khusus Ibukota Jakarta', 'shortText' => 'Daerah Khusus Ibukota Jakarta', 'types' => ['administrative_area_level_1', 'political']],
                ['longText' => 'Indonesia', 'shortText' => 'ID', 'types' => ['country', 'political']],
                ['longText' => '12190', 'shortText' => '12190', 'types' => ['postal_code']],
            ],
            'location' => ['latitude' => -6.2275798, 'longitude' => 106.808674],
            'displayName' => ['text' => 'Google Jakarta'],
        ];

        $parsed = app(GooglePlacesService::class)->parseAddress($place);

        $this->assertSame('No.53 Jalan Jenderal Sudirman', $parsed['address_1']);
        $this->assertSame('Pacific Century Place Tower Level 45 SCBD Lot 10', $parsed['address_2']);
        $this->assertSame('Kota Jakarta Selatan', $parsed['city']);
        $this->assertSame('12190', $parsed['zip']);
        $this->assertSame('ID', $parsed['country']);
        $this->assertSame('Indonesia', $parsed['country_name']);
        $this->assertSame('+62 21 5098 5700', $parsed['phone']);
    }

    public function test_locality_wins_and_missing_phone_is_empty(): void
    {
        $place = [
            'addressComponents' => [
                ['longText' => 'Naveed Cottages Street', 'shortText' => 'Naveed Cottages St', 'types' => ['route']],
                ['longText' => 'Gulistan-e-Johar', 'shortText' => 'Gulistan-e-Johar', 'types' => ['sublocality_level_1', 'sublocality', 'political']],
                ['longText' => 'Karachi', 'shortText' => 'Karachi', 'types' => ['locality', 'political']],
                ['longText' => 'Karachi City', 'shortText' => 'Karachi City', 'types' => ['administrative_area_level_2', 'political']],
                ['longText' => 'Sindh', 'shortText' => 'Sindh', 'types' => ['administrative_area_level_1', 'political']],
                ['longText' => 'Pakistan', 'shortText' => 'PK', 'types' => ['country', 'political']],
            ],
            'displayName' => ['text' => 'Naveed Cottages Street'],
        ];

        $parsed = app(GooglePlacesService::class)->parseAddress($place);

        $this->assertSame('Naveed Cottages Street', $parsed['address_1']);
        $this->assertSame('', $parsed['address_2']);
        $this->assertSame('Karachi', $parsed['city']);
        $this->assertSame('PK', $parsed['country']);
        $this->assertSame('', $parsed['phone']);
    }
}
