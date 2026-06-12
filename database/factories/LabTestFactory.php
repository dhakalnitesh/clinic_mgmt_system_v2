<?php

namespace Database\Factories;

use App\Models\Laboratory\LabTest;
use App\Models\Laboratory\LabTestCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabTestFactory extends Factory
{
    protected $model = LabTest::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . ' Test',
            'code' => strtoupper(fake()->unique()->lexify('T???')),
            'lab_test_category_id' => LabTestCategory::factory(),
            'price' => fake()->randomFloat(2, 50, 2000),
            'description' => fake()->sentence(),
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
