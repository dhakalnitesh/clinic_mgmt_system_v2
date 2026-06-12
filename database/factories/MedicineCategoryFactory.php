<?php

namespace Database\Factories;

use App\Models\Pharmacy\MedicineCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineCategoryFactory extends Factory
{
    protected $model = MedicineCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . ' Category ' . fake()->randomNumber(4),
            'is_active' => true,
        ];
    }
}
