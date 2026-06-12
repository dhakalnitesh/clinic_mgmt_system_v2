<?php

namespace Database\Factories;

use App\Models\Laboratory\LabOrderItem;
use App\Models\Laboratory\LabOrder;
use App\Models\Laboratory\LabTest;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabOrderItemFactory extends Factory
{
    protected $model = LabOrderItem::class;

    public function definition(): array
    {
        return [
            'lab_order_id' => LabOrder::factory(),
            'lab_test_id' => LabTest::factory(),
            'status' => fake()->randomElement(['ordered', 'processing', 'completed']),
        ];
    }
}
