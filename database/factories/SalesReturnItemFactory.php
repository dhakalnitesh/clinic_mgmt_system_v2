<?php

namespace Database\Factories;

use App\Models\Pharmacy\SalesReturnItem;
use App\Models\Pharmacy\SalesReturn;
use App\Models\Pharmacy\SalesItem;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\StockBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesReturnItemFactory extends Factory
{
    protected $model = SalesReturnItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 10, 500);

        return [
            'sales_return_id' => SalesReturn::factory(),
            'sale_item_id' => SalesItem::factory(),
            'medicine_id' => Medicine::factory(),
            'stock_batch_id' => StockBatch::factory(),
            'quantity_returned' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
            'stock_action' => fake()->randomElement(['return_to_stock', 'write_off']),
        ];
    }
}
