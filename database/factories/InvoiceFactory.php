<?php

namespace Database\Factories;

use App\Models\Billing\Invoice;
use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-BILL-' . str_pad(fake()->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'patient_id' => Patient::factory(),
            'subtotal' => fake()->randomFloat(2, 200, 10000),
            'total' => fake()->randomFloat(2, 200, 10000),
            'status' => fake()->randomElement(['pending', 'paid', 'partial', 'cancelled']),
            'created_by' => User::factory(),
        ];
    }
}
