<?php

namespace Database\Factories;

use App\Models\Appointment\Appointment;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'appointment_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'appointment_time' => fake()->randomElement(['09:00', '10:00', '11:00', '14:00', '15:00', '16:00']),
            'status' => fake()->randomElement(['waiting', 'visited']),
            'reasons' => fake()->sentence(),
        ];
    }
}
