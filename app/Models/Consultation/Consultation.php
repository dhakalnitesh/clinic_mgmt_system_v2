<?php

namespace App\Models\Consultation;

use App\Models\Doctor\Doctor;
use App\Models\Laboratory\ConsultationLabTest;
use App\Models\Laboratory\LabOrder;
use App\Models\Patient\Patient;
use App\Models\Pharmacy\Prescription;
use App\Models\Visit\Visit;
use App\Models\Vitals\Vital;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $appends = ['created_at_bs'];

    protected $fillable = [
        'visit_id',
        'appointment_id',
        'patient_id',
        'doctor_id',
        'diagnosis',
        'notes',
        'follow_up_date',
        'consulted_at',
        'chief_complaint',
        'history',
        'examination_notes',
        'consultation_status',
        'created_by',
        'document_path',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function vitals()
    {
        return $this->hasOne(Vital::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function labTests()
{
    return $this->hasMany(ConsultationLabTest::class);
}

public function labOrders()
{
    return $this->hasMany(LabOrder::class);
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
