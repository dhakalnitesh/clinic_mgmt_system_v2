<?php

namespace Database\Factories;

use App\Models\Pharmacy\StockAdjustment;
use App\Models\Pharmacy\Medicine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockAdjustmentFactory extends Factory
{
    protected $model = StockAdjustment::class;

    public function definition(): array
    {
        return [
            'medicine_id' => Medicine::factory(),
            'adjusted_by' => User::factory(),
            'adjustment_type' => fake()->randomElement(['addition', 'deduction', 'correction', 'expired', 'damaged']),
            'quantity' => fake()->numberBetween(-100, 100),
            'reason' => fake()->sentence(),
        ];
    }
}
