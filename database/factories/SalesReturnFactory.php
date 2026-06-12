<?php

namespace Database\Factories;

use App\Models\Pharmacy\SalesReturn;
use App\Models\Pharmacy\Sales;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesReturnFactory extends Factory
{
    protected $model = SalesReturn::class;

    public function definition(): array
    {
        return [
            'sale_id' => Sales::factory(),
            'returned_by' => User::factory(),
            'return_number' => 'RET-' . now()->format('Y') . '-' . str_pad(fake()->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'return_date' => fake()->dateTimeBetween('-1 month'),
            'status' => fake()->randomElement(['draft', 'approved', 'completed']),
            'reason' => fake()->randomElement(['wrong_medicine', 'wrong_quantity', 'patient_refused', 'duplicate_sale', 'other']),
            'total_return_amount' => fake()->randomFloat(2, 50, 5000),
        ];
    }
}
