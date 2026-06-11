<?php

namespace App\Models\Laboratory;

use App\Models\Consultation\Consultation;
use App\Models\Patient\Patient;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrder extends Model
{
    use HasFactory;

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
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Ordered tests.
     */
    public function items()
    {
        return $this->hasMany(LabOrderItem::class);
    }
}
