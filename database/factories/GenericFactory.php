<?php

namespace Database\Factories;

use App\Models\Pharmacy\Generic;
use Illuminate\Database\Eloquent\Factories\Factory;

class GenericFactory extends Factory
{
    protected $model = Generic::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . ' Generic ' . fake()->randomNumber(4),
            'is_active' => true,
        ];
    }
}
