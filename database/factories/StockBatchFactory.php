<?php

namespace Database\Factories;

use App\Models\Pharmacy\StockBatch;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\Supplier;
use App\Models\Pharmacy\GoodsReceivedNote;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockBatchFactory extends Factory
{
    protected $model = StockBatch::class;

    public function definition(): array
    {
        return [
            'medicine_id' => Medicine::factory(),
            'supplier_id' => Supplier::factory(),
            'goods_received_note_id' => GoodsReceivedNote::factory(),
            'batch_number' => strtoupper(fake()->unique()->lexify('BATCH-?????-??')),
            'manufacturing_date' => fake()->dateTimeBetween('-2 years', '-1 month'),
            'expiry_date' => fake()->dateTimeBetween('+1 month', '+3 years'),
            'quantity_received' => fake()->numberBetween(100, 1000),
            'quantity_available' => fake()->numberBetween(50, 500),
            'purchase_price' => fake()->randomFloat(2, 5, 200),
            'sale_price' => fake()->randomFloat(2, 10, 500),
            'mrp' => fake()->randomFloat(2, 10, 500),
            'is_active' => true,
        ];
    }
}
