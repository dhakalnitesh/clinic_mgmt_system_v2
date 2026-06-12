<?php

namespace App\Models\Laboratory;

use App\Models\Consultation\Consultation;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrder extends Model
{
    use HasFactory;


    protected $appends = ['created_at_bs'];

    protected $fillable = [
        'consultation_id',
        'patient_id',
        'doctor_id',
        'order_number',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * Consultation that generated this lab order.
     */
    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    /**
     * Patient for whom the tests are ordered.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Doctor who ordered the tests.
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    /**
     * Ordered tests.
     */
    public function items()
    {
        return $this->hasMany(LabOrderItem::class);
    }

    /**
     * Results through order items.
     */
    public function results()
    {
        return $this->hasManyThrough(LabResult::class, LabOrderItem::class);
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
