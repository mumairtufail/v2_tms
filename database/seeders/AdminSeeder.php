<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Manifest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Assuming you're using the User model for the users table

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Insert admin user into the 'users' table
        User::create([
            'f_name' => 'Super',
            'l_name' => 'Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('123456'),
            'address' => '123 Admin Street, Admin City',
            'is_admin' => true,
            'is_deleted' => false,
            'phone' => '1234567890', // Added phone number
        ]);

        $company = Company::create([
            'name' => 'company',
            'address' => '123 Admin Street, Admin City',
            'is_deleted' => false,
            'phone' => '1234567890', // Added phone number 
        ]);
        // Insert admin user into the 'users' table
        User::create([
            'f_name' => 'Admin',
            'l_name' => 'User',
            'email' => 'company@gmail.com',
            'password' => Hash::make('123456'),
            'address' => '123 Admin Street, Admin City',
            'company_id' => $company->id,
            'is_admin' => false,
            'is_deleted' => false,
            'phone' => '1234567890', // Added phone number
        ]);
    }
}
