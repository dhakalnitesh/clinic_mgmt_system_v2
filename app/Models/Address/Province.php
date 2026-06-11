<?php

namespace App\Models\Address;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = ['province_name'];

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
