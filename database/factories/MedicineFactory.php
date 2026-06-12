<?php

namespace Database\Factories;

use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\Generic;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineFactory extends Factory
{
    protected $model = Medicine::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . ' ' . fake()->randomElement(['Tablet', 'Capsule', 'Syrup']),
            'medicine_category_id' => MedicineCategory::factory(),
            'generic_id' => Generic::factory(),
            'medicine_unit_id' => MedicineUnit::factory(),
            'strength' => fake()->randomElement(['500mg', '250mg', '10mg', '5ml']),
            'form' => fake()->randomElement(Medicine::FORMS),
            'purchase_price' => fake()->randomFloat(2, 5, 200),
            'sale_price' => fake()->randomFloat(2, 10, 500),
            'mrp' => fake()->randomFloat(2, 10, 500),
            'tax_percent' => fake()->randomElement([0, 5, 12, 18]),
            'reorder_level' => fake()->numberBetween(5, 50),
            'is_active' => true,
        ];
    }
}
