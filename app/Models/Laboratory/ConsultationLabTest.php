<?php

namespace App\Models\Laboratory;

use App\Models\Consultation\Consultation;
use Illuminate\Database\Eloquent\Model;

class ConsultationLabTest extends Model
{
    protected $fillable = [
        'consultation_id',
        'lab_test_id',
        'status',
        'notes',
        'created_by',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function labTest()
    {
        return $this->belongsTo(LabTest::class);
    }
}
