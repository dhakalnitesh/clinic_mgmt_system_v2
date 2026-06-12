<?php

namespace Database\Factories;

use App\Models\Doctor\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nmc_number' => 'NMC-' . fake()->unique()->randomNumber(5),
            'specialization' => fake()->randomElement(['General Physician', 'Cardiologist', 'Pediatrician', 'Orthopedic']),
            'phone' => '984' . fake()->randomNumber(7, true),
            'consultation_fee' => fake()->randomFloat(2, 200, 1500),
        ];
    }
}
