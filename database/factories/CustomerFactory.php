<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->company(),
            'customer_email' => $this->faker->unique()->safeEmail(),
            'short_code' => strtoupper($this->faker->lexify('???')),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'US',
            'currency' => 'USD',
            'customer_type' => 'shipper',
            'default_billing_option' => 'shipper',
            'is_active' => true,
            'is_deleted' => false,
            'portal' => false,
            'quote_required' => false,
        ];
    }

    public function withPortalAccess(): static
    {
        return $this->state(fn () => ['portal' => true]);
    }
}
