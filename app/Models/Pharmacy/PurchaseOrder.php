<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_id', 'ordered_by', 'approved_by',
        'po_number', 'order_date', 'expected_delivery_date',
        'status', 'subtotal', 'discount_amount', 'tax_amount',
        'total_amount', 'notes',
    ];

    protected $casts = [
        'order_date'             => 'date',
        'expected_delivery_date' => 'date',
        'subtotal'               => 'decimal:2',
        'discount_amount'        => 'decimal:2',
        'tax_amount'             => 'decimal:2',
        'total_amount'           => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceivedNotes(): HasMany
    {
        return $this->hasMany(GoodsReceivedNote::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'partial']);
    }

    // ── Helpers ────────────────────────────────────────────────────

    /**
     * Auto-generate PO number: PO-YYYY-00001
     */
    public static function generatePoNumber(): string
    {
        $year   = now()->format('Y');
        $prefix = "PO-{$year}-";
        $last   = static::where('po_number', 'like', "{$prefix}%")
                        ->orderByDesc('id')->first();
        $seq    = $last ? ((int) substr($last->po_number, -5)) + 1 : 1;
        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $this->update([
            'subtotal'     => $subtotal,
            'total_amount' => $subtotal - $this->discount_amount + $this->tax_amount,
        ]);
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        return $this->items->every(
            fn ($item) => $item->quantity_received >= $item->quantity_ordered
        );
    }
}