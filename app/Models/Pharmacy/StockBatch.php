<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class StockBatch extends Model
{
    protected $fillable = [
        'medicine_id',
        'supplier_id',
        'goods_received_note_id',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'quantity_received',
        'quantity_available',
        'quantity_sold',
        'quantity_adjusted',
        'purchase_price',
        'sale_price',
        'mrp',
        'is_active',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date'        => 'date',
        'purchase_price'     => 'decimal:2',
        'sale_price'         => 'decimal:2',
        'mrp'                => 'decimal:2',
        'is_active'          => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function goodsReceivedNote(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SalesItem::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)->where('quantity_available', '>', 0);
    }

    /**
     * FEFO — First Expiry First Out ordering.
     */
    public function scopeFefo($query)
    {
        return $query->available()->orderBy('expiry_date');
    }

    public function scopeExpired($query)
    {
        return $query->whereDate('expiry_date', '<', now());
    }

    public function scopeNearExpiry($query, int $days = 90)
    {
        return $query->available()
            ->whereDate('expiry_date', '>=', now())
            ->whereDate('expiry_date', '<=', now()->addDays($days));
    }

    // ── Accessors ──────────────────────────────────────────────────

    public function getIsExpiredAttribute(): bool
    {
        return Carbon::parse($this->expiry_date)->isPast();
    }

    public function getDaysToExpiryAttribute(): int
    {
        return (int) now()->diffInDays($this->expiry_date, false);
    }

    public function getExpiryStatusAttribute(): string
    {
        $days = $this->days_to_expiry;

        if ($days < 0)  return 'expired';
        if ($days <= 30) return 'critical';   // ≤ 30 days
        if ($days <= 90) return 'warning';    // 31–90 days
        return 'good';
    }

    // ── Business Logic ─────────────────────────────────────────────

    /**
     * Deduct quantity from this batch (called during sale).
     * Returns false if insufficient stock.
     */
    public function deduct(int $qty): bool
    {
        if ($this->quantity_available < $qty) {
            return false;
        }

        $this->decrement('quantity_available', $qty);
        $this->increment('quantity_sold', $qty);

        if ($this->quantity_available === 0) {
            $this->update(['is_active' => false]);
        }

        return true;
    }

    /**
     * Restore quantity to this batch (called during sales return).
     */
    public function restore(int $qty): void
    {
        $this->increment('quantity_available', $qty);
        $this->decrement('quantity_sold', $qty);
        $this->update(['is_active' => true]);
    }
}