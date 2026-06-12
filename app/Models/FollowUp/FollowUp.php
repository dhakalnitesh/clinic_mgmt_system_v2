<?php

namespace App\Models\FollowUp;

use App\Models\Consultation\Consultation;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    use HasFactory;

    protected $appends = ['created_at_bs', 'follow_up_date_bs'];

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'visit_id',
        'consultation_id',
        'follow_up_date',
        'notes',
        'status',
        'completed_notes',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
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

    public function getFollowUpDateBsAttribute()
    {
        if (!$this->follow_up_date) return null;
        try {
            return \Anuzpandey\LaravelNepaliDate\LaravelNepaliDate::from($this->follow_up_date->format('Y-m-d'))->toNepaliDate();
        } catch (\Throwable) {
            return $this->follow_up_date->format('Y-m-d');
        }
    }
}
