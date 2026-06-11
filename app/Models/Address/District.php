<?php

namespace App\Models\Address;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = ['province_id', 'district_name'];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function municipals()
    {
        return $this->hasMany(Municipal::class);
    }
}
