<?php

namespace Database\Factories;

use App\Models\Pharmacy\Sales;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesFactory extends Factory
{
    protected $model = Sales::class;

    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'cashier_id' => User::factory(),
            'sale_date' => fake()->dateTimeBetween('-1 month'),
            'subtotal' => fake()->randomFloat(2, 100, 5000),
            'total_amount' => fake()->randomFloat(2, 100, 5000),
            'paid_amount' => fake()->randomFloat(2, 100, 5000),
            'payment_mode' => fake()->randomElement(['cash', 'card', 'upi', 'bank_transfer', 'e-sewa', 'khalti']),
            'sale_type' => fake()->randomElement(['counter', 'prescription']),
            'status' => fake()->randomElement(['draft', 'completed']),
        ];
    }
}
