<?php

namespace Database\Factories;

use App\Models\Pharmacy\SupplierReturnItem;
use App\Models\Pharmacy\SupplierReturn;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\StockBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierReturnItemFactory extends Factory
{
    protected $model = SupplierReturnItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 100);
        $unitPrice = fake()->randomFloat(2, 5, 200);

        return [
            'supplier_return_id' => SupplierReturn::factory(),
            'medicine_id' => Medicine::factory(),
            'stock_batch_id' => StockBatch::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ];
    }
}
