<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Accessorial;

class AccessorialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Truncate the table to start fresh and reset the auto-incrementing ID.
        // This is generally better than delete() for seeders.
      DB::table('accessorials')->delete();


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

        $accessorialsToInsert = [];
        $companyId = 1; // The company ID to associate these accessorials with.

        // Prepare the array for a bulk insert
        foreach ($accessorialNames as $name) {
            $accessorialsToInsert[] = [
                'name' => $name,
                'company_id' => $companyId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert the data into the database in a single query
        DB::table('accessorials')->insert($accessorialsToInsert);
    }
}