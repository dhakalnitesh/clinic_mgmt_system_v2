<?php

namespace Database\Factories;

use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['male', 'female']),
            'age' => fake()->numberBetween(1, 90),
            'address1' => fake()->address(),
        ];
    }
}
