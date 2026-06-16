<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->randomNumber(4),
            'shortcode' => strtoupper($this->faker->lexify('????')),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress(),
            'is_active' => true,
            'is_deleted' => false,
        ];
    }
}
