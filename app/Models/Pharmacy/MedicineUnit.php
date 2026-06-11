<?php

namespace App\Models\Pharmacy;

use App\Models\Pharmacy\Medicine as PharmacyMedicine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineUnit extends Model
{
    protected $fillable = ['name', 'abbreviation'];

    public function medicines()
    {
        return $this->hasMany(PharmacyMedicine::class);
    }
}