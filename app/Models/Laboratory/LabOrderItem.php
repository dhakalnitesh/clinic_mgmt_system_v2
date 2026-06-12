<?php

namespace App\Models\Laboratory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrderItem extends Model
{
    use HasFactory;


    protected $fillable = [
        'lab_order_id',
        'lab_test_id',
        'status',
    ];

    /**
     * Parent lab order.
     */
    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class);
    }
    public function labTest()
{
    return $this->belongsTo(LabTest::class);
}

public function results()
{
    return $this->hasMany(LabResult::class);
}


}
