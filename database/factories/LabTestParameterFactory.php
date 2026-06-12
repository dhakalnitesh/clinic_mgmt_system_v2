<?php

namespace Database\Factories;

use App\Models\Laboratory\LabTestParameter;
use App\Models\Laboratory\LabTest;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabTestParameterFactory extends Factory
{
    protected $model = LabTestParameter::class;

    public function definition(): array
    {
        return [
            'lab_test_id' => LabTest::factory(),
            'name' => fake()->unique()->word() . ' Level',
            'unit' => fake()->randomElement(['mg/dL', 'g/dL', 'IU/L', 'mmol/L', '%', 'cells/μL']),
            'reference_range' => fake()->randomElement(['4.0-11.0', '13.5-17.5', '135-145', '3.5-5.0']),
            'display_order' => fake()->numberBetween(1, 50),
            'is_active' => true,
        ];
    }
}
