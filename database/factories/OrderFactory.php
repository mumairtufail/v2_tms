<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'company_id' => Company::factory(),
            'customer_id' => Customer::factory(),
            'order_number' => 'ORD-' . $this->faker->unique()->randomNumber(6, true),
            'order_type' => $this->faker->randomElement(['point_to_point', 'single_shipper', 'single_consignee', 'sequence']),
            'status' => $this->faker->randomElement(['new', 'in_progress', 'completed', 'cancelled']),
            'ref_number' => $this->faker->optional()->regexify('[A-Z]{3}-[0-9]{3}'),
            'customer_po_number' => $this->faker->optional()->regexify('PO-[0-9]{5}'),
            'special_instructions' => $this->faker->optional()->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}