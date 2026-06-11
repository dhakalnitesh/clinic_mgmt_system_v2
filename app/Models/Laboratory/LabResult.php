<?php

namespace App\Models\Laboratory;

use Illuminate\Database\Eloquent\Model;

class LabResult extends Model
{
    protected $fillable = [
        'lab_order_item_id',
        'lab_test_parameter_id',
        'result_value',
        'remarks',
    ];

    public function orderItem()
    {
        return $this->belongsTo(LabOrderItem::class);
    }

    public function parameter()
    {
        return $this->belongsTo(LabTestParameter::class);
    }
}
