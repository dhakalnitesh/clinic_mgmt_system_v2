<?php

namespace Database\Factories;

use App\Models\Billing\Payment;
use App\Models\Billing\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'payment_method' => fake()->randomElement(['cash', 'card', 'mobile_banking']),
            'payment_date' => fake()->dateTimeBetween('-1 month'),
            'created_by' => User::factory(),
        ];
    }
}
