<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrnItem extends Model
{
    protected $fillable = [
        'goods_received_note_id',
        'medicine_id',
        'purchase_order_item_id',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'quantity_received',
        'free_quantity',
        'unit_price',
        'sale_price',
        'mrp',
        'discount_percent',
        'tax_percent',
        'subtotal',
        'stock_batch_id',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date'        => 'date',
        'unit_price'         => 'decimal:2',
        'sale_price'         => 'decimal:2',
        'mrp'                => 'decimal:2',
        'discount_percent'   => 'decimal:2',
        'tax_percent'        => 'decimal:2',
        'subtotal'           => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function goodsReceivedNote(): BelongsTo
    {
        return $this->belongsTo(GoodsReceivedNote::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function stockBatch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class);
    }

    // ── Helpers ────────────────────────────────────────────────────

    /**
     * Total quantity including free stock.
     */
    public function getTotalQuantityAttribute(): int
    {
        return $this->quantity_received + $this->free_quantity;
    }

    /**
     * Calculate and set the subtotal from qty, price, discount, tax.
     */
    public function calculateSubtotal(): float
    {
        $gross    = $this->quantity_received * $this->unit_price;
        $discount = $gross * ($this->discount_percent / 100);
        $taxable  = $gross - $discount;
        $tax      = $taxable * ($this->tax_percent / 100);

        return round($taxable + $tax, 2);
    }
}