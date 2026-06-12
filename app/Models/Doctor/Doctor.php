<?php
namespace App\Models\Doctor;


use App\Models\Appointment\Appointment;
use App\Models\Consultation\Consultation;
use App\Models\FollowUp\FollowUp;
use App\Models\Laboratory\LabOrder;
use App\Models\Pharmacy\Prescription;
use App\Models\User;
use App\Models\Visit\Visit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'name', 'nmc_number', 'phone', 'photo', 'notes', 'specialization', 'address1', 'availability_schedule', 'consultation_fee'
    ];
    protected $appends = ['created_at_bs', 'photo_url'];

    protected $casts = [
        'consultation_fee' => 'decimal:2',
    ];

    public function getCreatedAtBsAttribute()
    {
        if (!$this->created_at) return null;
        try {
            return \Anuzpandey\LaravelNepaliDate\LaravelNepaliDate::from($this->created_at->format('Y-m-d'))->toNepaliDate();
        } catch (\Throwable) {
            return $this->created_at->format('Y-m-d');
        }
    }

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function schedules()
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function labOrders()
    {
        return $this->hasMany(LabOrder::class, 'doctor_id');
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? '/storage/' . $this->photo : null;
    }
}
