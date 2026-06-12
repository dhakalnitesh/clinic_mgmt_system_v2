<?php

namespace App\Models\Pharmacy;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;


class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'stock_batch_id',
        'adjusted_by',
        'approved_by',
        'adjustment_type',
        'quantity',
        'reference_number',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function stockBatch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class);
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('adjustment_type', $type);
    }

    public function scopeAdditions(Builder $query): Builder
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeDeductions(Builder $query): Builder
    {
        return $query->where('quantity', '<', 0);
    }

    // ── Accessors ──────────────────────────────────────────────────

    public function getIsAdditionAttribute(): bool
    {
        return $this->quantity > 0;
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->adjustment_type) {
            'addition'          => 'emerald',
            'correction'        => 'blue',
            'deduction'         => 'amber',
            'expired'           => 'red',
            'damaged'           => 'red',
            'theft'             => 'slate',
            'return_to_supplier' => 'purple',
            default             => 'slate',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->adjustment_type) {
            'addition'          => 'Stock Added',
            'correction'        => 'Stock Correction',
            'deduction'         => 'Manual Deduction',
            'expired'           => 'Expired Write-off',
            'damaged'           => 'Damaged Write-off',
            'theft'             => 'Theft / Shortage',
            'return_to_supplier' => 'Returned to Supplier',
            default             => ucfirst($this->adjustment_type),
        };
    }
}