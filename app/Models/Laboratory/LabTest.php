<?php

namespace App\Models\Laboratory;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'lab_test_category_id',
        'price',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Category of the lab test.
     */
    public function category()
    {
        return $this->belongsTo(LabTestCategory::class, 'lab_test_category_id');
    }

    /**
     * User who created the lab test.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Active tests only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function parameters()
{
    return $this->hasMany(LabTestParameter::class)
        ->orderBy('display_order');
}
}