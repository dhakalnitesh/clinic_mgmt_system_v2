<?php

namespace Database\Factories;

use App\Models\Pharmacy\DrugInteraction;
use App\Models\Pharmacy\Generic;
use Illuminate\Database\Eloquent\Factories\Factory;

class DrugInteractionFactory extends Factory
{
    protected $model = DrugInteraction::class;

    public function definition(): array
    {
        return [
            'generic_id_1' => Generic::factory(),
            'generic_id_2' => Generic::factory(),
            'severity' => fake()->randomElement(['minor', 'moderate', 'major', 'contraindicated']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
