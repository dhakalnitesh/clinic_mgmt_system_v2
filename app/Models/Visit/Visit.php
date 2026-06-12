<?php

namespace App\Models\Visit;

use App\Models\Appointment\Appointment;
use App\Models\Consultation\Consultation;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use App\Models\Pharmacy\Prescription;
use App\Models\Vitals\Vital;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $appends = ['created_at_bs'];

     protected $fillable = [
        'token_number',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'visited_at',
        'chief_complaint',
        'diagnosis',
        'notes',
        'visit_type',
        'status',
    ];
    public function patient()
{
    return $this->belongsTo(Patient::class);
}

public function doctor()
{
    return $this->belongsTo(Doctor::class);
}

public function prescription()
{
    return $this->hasOne(Prescription::class);
}
public function appointment()
{
    return $this->belongsTo(Appointment::class);
}
public function consultation()
{
    return $this->hasOne(Consultation::class);
}
public function vitals()
{
    return $this->hasMany(Vital::class);
}

public function getCreatedAtBsAttribute()
{
    if (!$this->created_at) return null;
    try {
        return \Anuzpandey\LaravelNepaliDate\LaravelNepaliDate::from($this->created_at->format('Y-m-d'))->toNepaliDate();
    } catch (\Throwable) {
        return $this->created_at->format('Y-m-d');
    }
}
}
