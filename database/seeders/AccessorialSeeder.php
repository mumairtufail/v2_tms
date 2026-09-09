<?php

namespace Database\Seeders;

use App\Models\Accessorial;
use App\Models\Company;
use Illuminate\Database\Seeder;

class AccessorialSeeder extends Seeder
{
    public function run(): void
    {
        $accessorialNames = [
            'After hours delivery',
            'After hours pickup',
            'Appointment delivery',
            'Appointment pickup',
            'Attempted delivery',
            'Attempted pickup',
            'Bonded',
            'Border crossing',
            'Chassis detention',
            'Construction site delivery',
            'Construction site pickup',
            'Container liftup',
            'Detention time delivery',
            'Detention time pickup',
            'Driver assist at destination',
            'Driver assist at origin',
            'Driver layover',
            'Drop trailer',
            'Excess valuation',
            'Extra leg',
            'Flatbed',
            'Handbomb at destination',
            'Handbomb at origin',
            'Haz mat',
            'Heated service',
            'Inside delivery',
            'Inside pickup',
            'Limited access delivery',
            'Limited access pickup',
            'Lumper at destination',
            'Lumper at origin',
            'Notification delivery',
            'Notification pickup',
            'Pmr',
            'Port fee gct',
            'Power only',
            'Prepull bc',
            'Prepull cal- edm',
            'Redelivery charge',
            'Redirect delivery',
            'Redirect pickup',
            'Reefer',
            'Reefer service',
            'Residential delivery',
            'Residential pickup',
            'Single shipment',
            'Step deck',
            'Storage',
            'Tailgate delivery',
            'Tailgate pickup',
            'Tarping',
            'Tradeshow',
            'Trailer detention',
            'Van',
            'Yard storage',
        ];

        $companies = Company::query()->get();

        if ($companies->isEmpty()) {
            $this->command?->warn('AccessorialSeeder skipped: no companies found. Run company seeders first.');

            return;
        }

        foreach ($companies as $company) {
            foreach ($accessorialNames as $name) {
                Accessorial::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $name,
                    ]
                );
            }
        }
    }
}
