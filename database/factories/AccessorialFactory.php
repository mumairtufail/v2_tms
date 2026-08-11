<?php

namespace Database\Factories;

use App\Models\Accessorial;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccessorialFactory extends Factory
{
    protected $model = Accessorial::class;

    public function definition(): array
    {
        $accessorialTypes = [
            'Liftgate Service',
            'Inside Delivery',
            'Residential Delivery',
            'Appointment Required',
            'White Glove Service',
            'Hazmat Handling',
            'Freeze Protection',
            'Special Equipment',
            'Tailgate Service',
            'Delivery Notification'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($accessorialTypes),
            'company_id' => Company::factory(),
        ];
    }
}