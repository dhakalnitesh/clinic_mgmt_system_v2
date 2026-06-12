<?php

namespace Database\Factories;

use App\Models\Pharmacy\PrescriptionItem;
use App\Models\Pharmacy\Prescription;
use App\Models\Pharmacy\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionItemFactory extends Factory
{
    protected $model = PrescriptionItem::class;

    public function definition(): array
    {
        return [
            'prescription_id' => Prescription::factory(),
            'medicine_id' => Medicine::factory(),
            'medicine_name' => fake()->word() . ' ' . fake()->randomElement(['Tablet', 'Capsule', 'Syrup']),
            'dosage_instruction' => fake()->randomElement(['1-0-1', '1-1-1', '0-0-1', '1-0-0']),
            'frequency' => fake()->randomElement(['daily', 'bid', 'tid', 'qid', 'weekly']),
            'duration_days' => fake()->numberBetween(3, 30),
            'quantity_prescribed' => fake()->numberBetween(10, 100),
            'is_substitutable' => fake()->boolean(),
            'status' => fake()->randomElement(['pending', 'partial', 'dispensed']),
        ];
    }
}
