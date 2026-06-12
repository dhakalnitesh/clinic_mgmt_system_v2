<?php

namespace Database\Factories;

use App\Models\Consultation\Consultation;
use App\Models\Visit\Visit;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultationFactory extends Factory
{
    protected $model = Consultation::class;

    public function definition(): array
    {
        return [
            'visit_id' => Visit::factory(),
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'chief_complaint' => fake()->sentence(),
            'diagnosis' => fake()->sentence(),
            'consulted_at' => fake()->dateTimeBetween('-1 week'),
            'consultation_status' => fake()->randomElement(['draft', 'completed', 'cancelled']),
        ];
    }
}
