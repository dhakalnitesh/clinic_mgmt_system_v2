<?php

namespace Database\Factories\Laboratory;

use App\Models\Laboratory\LabOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LabOrderFactory extends Factory
{
    protected $model = LabOrder::class;

    public function definition(): array
    {
        return [
            'patient_id'   => \App\Models\Patient\Patient::factory(),
            'doctor_id'    => User::factory(),
            'order_number' => 'LAB-' . strtoupper(fake()->bothify('####??')),
            'status'       => 'ordered',
            'created_by'   => User::factory(),
        ];
    }
}
