<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SalesReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sale_id',
        'returned_by',
        'approved_by',
        'return_number',
        'patient_id',
        'return_date',
        'reason',
        'status',
        'total_return_amount',
        'refund_mode',
        'refund_reference',
        'refund_processed',
        'refunded_at',
        'notes',
    ];

    protected $casts = [
        'return_date'         => 'date',
        'total_return_amount' => 'decimal:2',
        'refund_processed'    => 'boolean',
        'refunded_at'         => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sale_id');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereIn('status', ['approved', 'completed']);
    }

    // ── Helpers ────────────────────────────────────────────────────

    public static function generateReturnNumber(): string
    {
        $year   = now()->format('Y');
        $prefix = "RET-{$year}-";
        $last   = static::withTrashed()
                        ->where('return_number', 'like', "{$prefix}%")
                        ->orderByDesc('id')->first();
        $seq    = $last ? ((int) substr($last->return_number, -5)) + 1 : 1;

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function recalculateTotals(): void
    {
        $this->update([
            'total_return_amount' => $this->items()->sum('subtotal'),
        ]);
    }

    /**
     * Complete the return:
     *  - Restore or write-off stock per item's stock_action
     *  - Update sale_items.returned_quantity
     *  - Update parent sale status
     *  - Mark refund details
     */
    public function complete(): void
    {
        DB::transaction(function () {
            foreach ($this->items as $item) {
                // Restore stock to batch if condition is good
                if ($item->stock_action === 'return_to_stock') {
                    $item->stockBatch->restore($item->quantity_returned);
                } else {
                    // Write-off — log as adjustment but don't restore
                    StockAdjustment::create([
                        'medicine_id'     => $item->medicine_id,
                        'stock_batch_id'  => $item->stock_batch_id,
                        'adjusted_by'     => $this->returned_by,
                        'adjustment_type' => 'damaged',
                        'quantity'        => -$item->quantity_returned,
                        'reference_number' => $this->return_number,
                        'reason'          => "Sales return write-off: {$item->condition_note}",
                    ]);
                }

                // Update the original sale item's returned qty
                $item->saleItem->increment('returned_quantity', $item->quantity_returned);
            }

            // Update the original sale's status
            $sale       = $this->sale;
            $allReturned = $sale->items->every(fn ($si) => $si->is_fully_returned);
            $sale->update([
                'status' => $allReturned ? 'returned' : 'partial_return',
            ]);

            // Mark this return as completed
            $this->update([
                'status'           => 'completed',
                'refund_processed' => true,
                'refunded_at'      => now(),
            ]);
        });
    }
}