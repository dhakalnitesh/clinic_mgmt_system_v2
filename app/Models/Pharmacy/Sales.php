<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Sales extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'prescription_id',
        'sale_date',
        'sale_type',
        'cashier_id',
        'pharmacist_id',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_mode',
        'payment_reference',
        'status',
        'notes',
    ];

    protected $casts = [
        'sale_date'       => 'date',
        'subtotal'        => 'decimal:2',
        'discount_value'  => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'change_amount'   => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function pharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesItem::class, 'sale_id');
    }

    public function salesReturns(): HasMany
    {
        return $this->hasMany(SalesReturn::class, 'sale_id');
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereIn('status', ['completed', 'partial_return']);
    }

    public function scopeByDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('sale_date', $date);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('sale_date', today());
    }

    // ── Helpers ────────────────────────────────────────────────────

    /**
     * Calculate totals from sale items.
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');

        $discountAmount = $this->discount_type === 'percent'
            ? round($subtotal * ($this->discount_value / 100), 2)
            : $this->discount_value;

        $taxable  = $subtotal - $discountAmount;
        $taxAmount = $this->items()
            ->selectRaw('SUM((subtotal / (1 + tax_percent/100)) * (tax_percent/100)) as tax')
            ->value('tax') ?? 0;

        $this->update([
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_amount'      => round($taxAmount, 2),
            'total_amount'    => round($taxable + $taxAmount, 2),
        ]);
    }

    // ── Accessors ──────────────────────────────────────────────────

    public function getTotalReturnedAttribute(): float
    {
        return (float) $this->salesReturns()
            ->whereIn('status', ['approved', 'completed'])
            ->sum('total_return_amount');
    }

    public function getNetAmountAttribute(): float
    {
        return $this->total_amount - $this->total_returned;
    }
}