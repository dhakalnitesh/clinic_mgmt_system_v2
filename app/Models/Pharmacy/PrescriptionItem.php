<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id',
        'medicine_id',
        'medicine_name',
        'generic_id',
        'dosage_instruction',
        'frequency',
        'duration_days',
        'route',
        'quantity_prescribed',
        'quantity_dispensed',
        'is_substitutable',
        'status',
        'instructions',
    ];

    protected $casts = [
        'is_substitutable' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function generic(): BelongsTo
    {
        return $this->belongsTo(Generic::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SalesItem::class);
    }

    // ── Helpers ────────────────────────────────────────────────────

    public function getPendingQuantityAttribute(): int
    {
        return max(0, $this->quantity_prescribed - $this->quantity_dispensed);
    }

    public function getIsFullyDispensedAttribute(): bool
    {
        return $this->quantity_dispensed >= $this->quantity_prescribed;
    }

    /**
     * Record a dispensing — increments dispensed qty and updates status.
     */
    public function dispense(int $qty): void
    {
        $newQty = min(
            $this->quantity_prescribed,
            $this->quantity_dispensed + $qty
        );

        $status = $newQty >= $this->quantity_prescribed ? 'dispensed' : 'partial';

        $this->update([
            'quantity_dispensed' => $newQty,
            'status'             => $status,
        ]);

        // Bubble up to prescription
        $this->prescription->updateStatus();
    }
}