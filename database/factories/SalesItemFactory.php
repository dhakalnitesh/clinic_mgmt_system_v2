<?php

namespace Database\Factories;

use App\Models\Pharmacy\SalesItem;
use App\Models\Pharmacy\Sales;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\StockBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesItemFactory extends Factory
{
    protected $model = SalesItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->randomFloat(2, 10, 500);

        return [
            'sale_id' => Sales::factory(),
            'medicine_id' => Medicine::factory(),
            'stock_batch_id' => StockBatch::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ];
    }
}
