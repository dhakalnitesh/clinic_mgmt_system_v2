<?php

namespace App\Models\Laboratory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTestParameter extends Model
{
    use HasFactory;


    protected $appends = ['created_at_bs'];

    protected $fillable = [
        'lab_test_id',
        'name',
        'unit',
        'reference_range',
        'display_order',
        'is_active',
    ];

    public function labTest()
    {
        return $this->belongsTo(LabTest::class);
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
