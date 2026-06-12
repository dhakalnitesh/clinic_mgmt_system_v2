<?php

namespace Database\Factories;

use App\Models\Pharmacy\PurchaseOrderItem;
use App\Models\Pharmacy\PurchaseOrder;
use App\Models\Pharmacy\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderItemFactory extends Factory
{
    protected $model = PurchaseOrderItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(50, 500);
        $unitPrice = fake()->randomFloat(2, 5, 200);

        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'medicine_id' => Medicine::factory(),
            'quantity_ordered' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ];
    }
}
