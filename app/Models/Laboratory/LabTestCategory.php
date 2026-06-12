<?php

namespace App\Models\Laboratory;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabTestCategory extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];


    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

    public function labTests(): HasMany
    {
        return $this->hasMany(LabTest::class);
    }
}