<?php

namespace Database\Factories;

use App\Models\Pharmacy\PurchaseOrder;
use App\Models\Pharmacy\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'ordered_by' => User::factory(),
            'po_number' => 'PO-' . now()->format('Y') . '-' . str_pad(fake()->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'order_date' => fake()->dateTimeBetween('-1 month'),
            'status' => fake()->randomElement(['draft', 'sent', 'partial', 'received']),
            'subtotal' => fake()->randomFloat(2, 500, 50000),
            'total_amount' => fake()->randomFloat(2, 500, 50000),
        ];
    }
}
