<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Medicine extends Model
{
    use SoftDeletes;

    protected $table = 'medicines';

    /**
     * All supported dosage forms.
     * Referenced by MedicineController and Create/Edit Vue pages.
     */
    public const FORMS = [
        'tablet', 'capsule', 'syrup', 'suspension', 'injection',
        'cream', 'ointment', 'gel', 'drops', 'inhaler', 'patch',
        'suppository', 'powder', 'lotion', 'solution', 'other',
    ];

    protected $fillable = [
        'medicine_category_id',
        'generic_id',
        'medicine_unit_id',
        'name',
        'strength',
        'form',
        'manufacturer',
        'barcode',
        'hsn_code',
        'purchase_price',
        'sale_price',
        'mrp',
        'tax_percent',
        'reorder_level',
        'reorder_quantity',
        'shelf_location',
        'is_prescription_required',
        'is_controlled',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'purchase_price'           => 'decimal:2',
        'sale_price'               => 'decimal:2',
        'mrp'                      => 'decimal:2',
        'tax_percent'              => 'decimal:2',
        'is_prescription_required' => 'boolean',
        'is_controlled'            => 'boolean',
        'is_active'                => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'medicine_category_id');
    }

    public function generic(): BelongsTo
    {
        return $this->belongsTo(Generic::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MedicineUnit::class, 'medicine_unit_id');
    }

    public function stockBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }

    /**
     * Active batches only (not exhausted), ordered FEFO.
     */
    public function activeBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class)
            ->where('is_active', true)
            ->where('quantity_available', '>', 0)
            ->orderBy('expiry_date');
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%")
              ->orWhereHas('generic', fn ($g) => $g->where('name', 'like', "%{$term}%"));
        });
    }

    public function scopeLowStock(Builder $query): Builder
    {
        // Join with stock_batches sum to compare against reorder_level
        return $query->whereRaw(
            '(SELECT COALESCE(SUM(quantity_available),0)
              FROM stock_batches
              WHERE stock_batches.medicine_id = medicines.id
              AND stock_batches.is_active = 1) <= medicines.reorder_level'
        );
    }

    public function scopeNearExpiry(Builder $query, int $days = 90): Builder
    {
        return $query->whereHas('stockBatches', function ($b) use ($days) {
            $b->where('is_active', true)
              ->where('quantity_available', '>', 0)
              ->whereDate('expiry_date', '<=', now()->addDays($days));
        });
    }

    // ── Computed Attributes (Accessors) ────────────────────────────

    /**
     * Total available quantity across all active batches.
     */
    public function getTotalStockAttribute(): int
    {
        return (int) $this->stockBatches()
            ->where('is_active', true)
            ->sum('quantity_available');
    }

    /**
     * Stock status label for UI badges.
     */
    public function getStockStatusAttribute(): string
    {
        $stock = $this->total_stock;

        if ($stock <= 0) {
            return 'out_of_stock';
        }

        if ($stock <= $this->reorder_level) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    /**
     * Nearest expiry date across active batches.
     */
    public function getNearestExpiryAttribute(): ?string
    {
        $batch = $this->stockBatches()
            ->where('is_active', true)
            ->where('quantity_available', '>', 0)
            ->orderBy('expiry_date')
            ->first();

        return $batch?->expiry_date?->toDateString();
    }

    /**
     * Markup percentage over purchase price.
     */
    public function getMarkupPercentAttribute(): float
    {
        if ($this->purchase_price <= 0) return 0;
        return round((($this->sale_price - $this->purchase_price) / $this->purchase_price) * 100, 2);
    }
}