<?php

namespace Database\Factories\Laboratory;

use App\Models\Consultation\Consultation;
use App\Models\Doctor\Doctor;
use App\Models\Laboratory\LabOrder;
use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabOrderFactory extends Factory
{
    protected $model = LabOrder::class;

    public function definition(): array
    {
        return [
            'consultation_id' => Consultation::factory(),
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'order_number' => 'LAB-' . strtoupper(fake()->bothify('####??')),
            'status' => 'ordered',
            'created_by' => User::factory(),
        ];
    }
}
