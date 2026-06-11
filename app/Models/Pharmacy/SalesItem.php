<?php

namespace App\Models\Pharmacy;

use App\Models\Pharmacy\PrescriptionItem as PharmacyPrescriptionItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesItem extends Model
{
    protected $fillable = [
        'sale_id',
        'medicine_id',
        'stock_batch_id',
        'prescription_item_id',
        'quantity',
        'unit_price',
        'discount_percent',
        'tax_percent',
        'subtotal',
        'returned_quantity',
    ];

    protected $casts = [
        'unit_price'       => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'tax_percent'      => 'decimal:2',
        'subtotal'         => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sale_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function stockBatch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class);
    }

    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(PharmacyPrescriptionItem::class);
    }

    public function salesReturnItems(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    // ── Helpers ────────────────────────────────────────────────────

    public function calculateSubtotal(): float
    {
        $gross    = $this->quantity * $this->unit_price;
        $discount = $gross * ($this->discount_percent / 100);
        $taxable  = $gross - $discount;
        $tax      = $taxable * ($this->tax_percent / 100);

        return round($taxable + $tax, 2);
    }

    // ── Accessors ──────────────────────────────────────────────────

    /**
     * Quantity still available to return (original qty - already returned).
     */
    public function getReturnableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->returned_quantity);
    }

    public function getIsFullyReturnedAttribute(): bool
    {
        return $this->returned_quantity >= $this->quantity;
    }
}