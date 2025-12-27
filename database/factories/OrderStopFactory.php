<?php

namespace Database\Factories;

use App\Models\OrderStop;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStopFactory extends Factory
{
    protected $model = OrderStop::class;

    public function definition()
    {
        return [
            'order_id' => Order::factory(),
            'stop_type' => 'pickup',
            'sequence_number' => 1,
            'company_name' => $this->faker->company(),
            'address_1' => $this->faker->streetAddress(),
            'address_2' => $this->faker->optional()->secondaryAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'US',
            'contact_name' => $this->faker->name(),
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_email' => $this->faker->email(),
            'notes' => $this->faker->optional()->sentence(),
            'opening_time' => $this->faker->optional()->time('H:i'),
            'closing_time' => $this->faker->optional()->time('H:i'),
            'start_time' => $this->faker->optional()->dateTimeBetween('now', '+1 week'),
            'end_time' => $this->faker->optional()->dateTimeBetween('+1 week', '+2 weeks'),
            'is_appointment' => $this->faker->boolean(),
            'consignee_data' => json_encode([
                'company_name' => $this->faker->company(),
                'address_1' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->stateAbbr(),
                'zip' => $this->faker->postcode(),
            ]),
            'billing_data' => json_encode([
                'customs_broker' => $this->faker->optional()->company(),
                'declared_value' => $this->faker->optional()->randomFloat(2, 100, 50000),
                'currency' => $this->faker->randomElement(['USD', 'CAD', 'EUR']),
            ]),
        ];
    }
}