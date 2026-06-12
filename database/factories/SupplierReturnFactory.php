<?php

namespace Database\Factories;

use App\Models\Pharmacy\SupplierReturn;
use App\Models\Pharmacy\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierReturnFactory extends Factory
{
    protected $model = SupplierReturn::class;

    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'returned_by' => User::factory(),
            'return_number' => 'SR-' . now()->format('Y') . '-' . str_pad(fake()->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'return_date' => fake()->dateTimeBetween('-1 month'),
            'status' => fake()->randomElement(['draft', 'completed']),
            'reason' => fake()->randomElement(['expired', 'damaged', 'excess', 'wrong_item', 'quality_issue', 'other']),
            'total_amount' => fake()->randomFloat(2, 100, 10000),
        ];
    }
}
