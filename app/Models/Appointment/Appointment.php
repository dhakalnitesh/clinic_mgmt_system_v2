<?php

namespace App\Models\Appointment;

use App\Casts\NepaliDateCast;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'status',
        'consultation_fee',
        'reason'
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => NepaliDateCast::class,
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}   
