<?php

namespace Database\Factories;

use App\Models\Pharmacy\GrnItem;
use App\Models\Pharmacy\GoodsReceivedNote;
use App\Models\Pharmacy\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrnItemFactory extends Factory
{
    protected $model = GrnItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(50, 500);
        $unitPrice = fake()->randomFloat(2, 5, 200);

        return [
            'goods_received_note_id' => GoodsReceivedNote::factory(),
            'medicine_id' => Medicine::factory(),
            'batch_number' => strtoupper(fake()->unique()->lexify('BATCH-?????-??')),
            'manufacturing_date' => fake()->dateTimeBetween('-2 years', '-1 month'),
            'expiry_date' => fake()->dateTimeBetween('+1 month', '+3 years'),
            'quantity_received' => $quantity,
            'unit_price' => $unitPrice,
            'sale_price' => fake()->randomFloat(2, 10, 500),
            'mrp' => fake()->randomFloat(2, 10, 500),
            'subtotal' => $quantity * $unitPrice,
        ];
    }
}
