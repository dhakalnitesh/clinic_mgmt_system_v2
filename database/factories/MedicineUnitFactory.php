<?php

namespace Database\Factories;

use App\Models\Pharmacy\MedicineUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineUnitFactory extends Factory
{
    protected $model = MedicineUnit::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . ' Unit ' . fake()->randomNumber(4),
            'abbreviation' => strtoupper(fake()->unique()->lexify('???')),
        ];
    }
}
