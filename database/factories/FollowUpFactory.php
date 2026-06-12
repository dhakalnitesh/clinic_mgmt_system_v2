<?php

namespace Database\Factories;

use App\Models\FollowUp\FollowUp;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FollowUpFactory extends Factory
{
    protected $model = FollowUp::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'follow_up_date' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => fake()->randomElement(['pending', 'completed', 'missed']),
            'created_by' => User::factory(),
        ];
    }
}
