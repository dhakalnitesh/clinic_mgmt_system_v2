<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SupplierReturn extends Model
{
    protected $fillable = [
        'supplier_id',
        'goods_received_note_id',
        'returned_by',
        'return_number',
        'return_date',
        'reason',
        'status',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'return_date'  => 'date',
        'total_amount' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function goodsReceivedNote(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class);
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierReturnItem::class);
    }

    // ── Helpers ────────────────────────────────────────────────────

    public static function generateReturnNumber(): string
    {
        $year   = now()->format('Y');
        $prefix = "SR-{$year}-";
        $last   = static::where('return_number', 'like', "{$prefix}%")
                        ->orderByDesc('id')->first();
        $seq    = $last ? ((int) substr($last->return_number, -5)) + 1 : 1;

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    public function recalculateTotals(): void
    {
        $this->update([
            'total_amount' => $this->items()->sum('subtotal'),
        ]);
    }

    /**
     * Complete the return — deducts stock from batches.
     */
    public function complete(): void
    {
     DB::transaction(function () {
            foreach ($this->items as $item) {
                $item->stockBatch->decrement('quantity_available', $item->quantity);

                // Mark batch inactive if exhausted
                if ($item->stockBatch->quantity_available <= 0) {
                    $item->stockBatch->update(['is_active' => false]);
                }

                // Log as stock adjustment
                StockAdjustment::create([
                    'medicine_id'     => $item->medicine_id,
                    'stock_batch_id'  => $item->stock_batch_id,
                    'adjusted_by'     => $this->returned_by,
                    'adjustment_type' => 'return_to_supplier',
                    'quantity'        => -$item->quantity,
                    'reference_number' => $this->return_number,
                    'reason'          => "Supplier return: {$this->reason}",
                ]);
            }

            $this->update(['status' => 'completed']);
        });
    }
}