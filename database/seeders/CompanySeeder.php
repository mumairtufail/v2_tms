<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;


class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        $companies = [
            [
                'name' => 'Super Admin Company',
                'address' => 'System Admin Address',
                'phone' => '+1234567890',
                'is_active' => true,
                'is_deleted' => false,
            ],
            [
                'name' => 'Demo Logistics Co.',
                'address' => '123 Business Street, City, State 12345',
                'phone' => '+1987654321',
                'is_active' => true,
                'is_deleted' => false,
            ]
        ];

        foreach ($companies as $company) {
            Company::firstOrCreate([
                'name' => $company['name']
            ], $company);
        }

        $this->command->info('Companies seeded successfully!');
    }
}
