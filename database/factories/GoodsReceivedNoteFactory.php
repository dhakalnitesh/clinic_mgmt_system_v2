<?php

namespace Database\Factories;

use App\Models\Pharmacy\GoodsReceivedNote;
use App\Models\Pharmacy\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsReceivedNoteFactory extends Factory
{
    protected $model = GoodsReceivedNote::class;

    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'received_by' => User::factory(),
            'grn_number' => 'GRN-' . now()->format('Y') . '-' . str_pad(fake()->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'received_date' => fake()->dateTimeBetween('-1 month'),
            'status' => fake()->randomElement(['pending', 'verified', 'posted']),
            'subtotal' => fake()->randomFloat(2, 500, 50000),
            'total_amount' => fake()->randomFloat(2, 500, 50000),
        ];
    }
}
