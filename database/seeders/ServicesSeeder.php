<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServicesSeeder extends Seeder
{
    public function run()
    {
        $services = [
            'Deck Delivery',
            'Cross Dock',
            'Swamper/lumper',
            'Van Service',
            'Innovations Logistics',
            'Prepull',
            'Drayage Service',
            'Highway Load',
            'Van Delivery',
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(['name' => $service]);
        }
    }
}
