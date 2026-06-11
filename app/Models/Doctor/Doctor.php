<?php
namespace App\Models\Doctor;


use App\Models\Laboratory\LabOrder;
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

    public function visits()
{
    return $this->hasMany(Visit::class);
}
public function schedules()
{
    return $this->hasMany(DoctorSchedule::class);
}
public function labOrders()
{
    return $this->hasMany(LabOrder::class, 'doctor_id');
}

public function user()
{
    return $this->belongsTo(User::class);
}
}
