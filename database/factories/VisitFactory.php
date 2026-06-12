<?php

namespace Database\Factories;

use App\Models\Visit\Visit;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitFactory extends Factory
{
    protected $model = Visit::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'token_number' => 'TKN-' . str_pad(fake()->unique()->randomNumber(5), 5, '0', STR_PAD_LEFT),
            'visited_at' => fake()->dateTimeBetween('-1 week'),
            'chief_complaint' => fake()->sentence(),
            'visit_type' => fake()->randomElement(['follow_up', 'walk_in']),
            'status' => fake()->randomElement(['waiting', 'in_consultation', 'completed', 'cancelled']),
        ];
    }
}
