<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class GoodsReceivedNote extends Model
{
    protected $fillable = [
        'supplier_id', 'purchase_order_id', 'received_by', 'verified_by',
        'grn_number', 'received_date', 'invoice_number', 'invoice_date',
        'status', 'subtotal', 'discount_amount', 'tax_amount', 'total_amount',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
        'invoice_date'  => 'date',
        'subtotal'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'    => 'decimal:2',
        'total_amount'  => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GrnItem::class, 'goods_received_note_id');
    }

    public function stockBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }

    // ── Helpers ────────────────────────────────────────────────────
    public static function generateGrnNumber(): string
    {
        $year   = now()->format('Y');
        $prefix = "GRN-{$year}-";
        $last   = static::where('grn_number', 'like', "{$prefix}%")
                        ->orderByDesc('id')->first();
        $seq    = $last ? ((int) substr($last->grn_number, -5)) + 1 : 1;
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

    /**
     * Post GRN: create stock batches from all GRN items and mark as posted.
     * Called after verification.
     */
    public function post(): void
    {
        DB::transaction(function () {
            foreach ($this->items as $item) {
                $batch = StockBatch::create([
                    'medicine_id'            => $item->medicine_id,
                    'supplier_id'            => $this->supplier_id,
                    'goods_received_note_id' => $this->id,
                    'batch_number'           => $item->batch_number,
                    'manufacturing_date'     => $item->manufacturing_date,
                    'expiry_date'            => $item->expiry_date,
                    'quantity_received'      => $item->quantity_received + $item->free_quantity,
                    'quantity_available'     => $item->quantity_received + $item->free_quantity,
                    'purchase_price'         => $item->unit_price,
                    'sale_price'             => $item->sale_price,
                    'mrp'                    => $item->mrp,
                    'is_active'              => true,
                ]);

                // Link GRN item to the batch
                $item->update(['stock_batch_id' => $batch->id]);

                // Update PO received quantities
                if ($item->purchase_order_item_id) {
                    $poItem = PurchaseOrderItem::find($item->purchase_order_item_id);
                    if ($poItem) {
                        $poItem->increment('quantity_received', $item->quantity_received);
                    }
                }
            }

            // Update GRN status
            $this->update(['status' => 'posted']);

            // Update PO status
            if ($this->purchaseOrder) {
                $po = $this->purchaseOrder;
                $po->refresh();
                $status = $po->is_fully_received ? 'received' : 'partial';
                $po->update(['status' => $status]);
            }
        });
    }
}