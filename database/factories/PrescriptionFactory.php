<?php

namespace Database\Factories;

use App\Models\Pharmacy\Prescription;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'prescription_number' => 'RX-' . now()->format('Y') . '-' . str_pad(fake()->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'prescribed_at' => fake()->dateTimeBetween('-1 month'),
            'status' => fake()->randomElement(['pending', 'partial', 'dispensed']),
            'created_by' => User::factory(),
        ];
    }
}
