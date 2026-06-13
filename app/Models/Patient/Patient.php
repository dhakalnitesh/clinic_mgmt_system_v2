<?php

namespace App\Models\Patient;

use App\Models\Address\Province;
use App\Models\Address\District;
use App\Models\Address\Municipal;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Models\Laboratory\LabOrder;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address1',
        'address2',
        'age',
        'gender',
        'notes',
        'citizenship_type',
        'province_id',
        'district_id',
        'municipal_id',
    ];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function municipal()
    {
        return $this->belongsTo(Municipal::class);
    }

    protected $appends = ['created_at_bs'];

    public function getCreatedAtBsAttribute()
    {
        if (!$this->created_at) return null;
        try {
            return \Anuzpandey\LaravelNepaliDate\LaravelNepaliDate::from($this->created_at->format('Y-m-d'))->toNepaliDate();
        } catch (\Throwable) {
            return $this->created_at->format('Y-m-d');
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {

            $year = now()->format('Y');

            $lastPatient = self::whereYear('created_at', $year)
                ->latest('id')
                ->first();

            $number = 1;

            if ($lastPatient && $lastPatient->uhid) {

                $lastNumber = (int) substr($lastPatient->uhid, -3);

                $number = $lastNumber + 1;
            }

$patient->uhid = 'PAT-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);        });
    }
    public function visits()
{
    return $this->hasMany(Visit::class);
}

public function labOrders()
{
    return $this->hasMany(LabOrder::class);
}

public function payments()
{
    return $this->hasManyThrough(Payment::class, Invoice::class, 'patient_id', 'invoice_id', 'id', 'id');
}
}
