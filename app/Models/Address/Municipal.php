<?php

namespace App\Models\Address;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipal extends Model
{
    use HasFactory;

    protected $fillable = ['district_id', 'municipal_name'];

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
