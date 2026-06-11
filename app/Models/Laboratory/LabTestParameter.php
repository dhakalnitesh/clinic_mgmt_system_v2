<?php

namespace App\Models\Laboratory;

use Illuminate\Database\Eloquent\Model;

class LabTestParameter extends Model
{
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
}
