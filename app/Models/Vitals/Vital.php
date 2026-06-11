<?php

namespace App\Models\Vitals;

use App\Models\Consultation\Consultation;
use App\Models\Patient\Patient;
use App\Models\User\User;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vital extends Model
{
    use HasFactory;
   protected $fillable = [
    'patient_id',
    'appointment_id',
    'visit_id',
    'consultation_id',

    'blood_pressure',
    'pulse',
    'temperature',
    'oxygen',
    'height',
    'weight',
    'respiratory_rate',
    'bmi',
    'blood_sugar',
    'notes',

    'created_by',
];

    protected $casts = [
        'pulse' => 'integer',
        'oxygen' => 'integer',
        'respiratory_rate' => 'integer',
        'temperature' => 'decimal:2',
        'height' => 'decimal:2',
        'weight' => 'decimal:2',
        'bmi' => 'decimal:2',
        'blood_sugar' => 'decimal:2',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
