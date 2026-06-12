<?php

namespace Database\Factories;

use App\Models\Laboratory\LabTestCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabTestCategoryFactory extends Factory
{
    protected $model = LabTestCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . ' Category',
            'code' => strtoupper(fake()->unique()->lexify('????')),
            'description' => fake()->sentence(),
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
