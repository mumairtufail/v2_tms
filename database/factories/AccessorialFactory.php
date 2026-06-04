<?php

namespace Database\Factories;

use App\Models\Accessorial;
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
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}