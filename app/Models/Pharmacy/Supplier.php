<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'contact_person', 'phone', 'alternate_phone', 'email',
        'address', 'city', 'state', 'country', 'postal_code',
        'drug_license_no', 'drug_license_expiry', 'pan_vat_no',
        'credit_days', 'credit_limit', 'opening_balance',
        'is_active', 'notes',
    ];

    protected $casts = [
        'drug_license_expiry' => 'date',
        'credit_limit'        => 'decimal:2',
        'opening_balance'     => 'decimal:2',
        'is_active'           => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceivedNotes(): HasMany
    {
        return $this->hasMany(GoodsReceivedNote::class);
    }

    public function stockBatches(): HasMany
    {
        return $this->hasMany(StockBatch::class);
    }

    public function supplierReturns(): HasMany
    {
        return $this->hasMany(SupplierReturn::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Accessors ──────────────────────────────────────────────────
    public function getIsLicenseExpiredAttribute(): bool
    {
        if (! $this->drug_license_expiry) {
            return false;
        }
        return Carbon::parse($this->drug_license_expiry)->isPast();
    }

    public function getTotalPurchasesAttribute(): float
    {
        return (float) $this->purchaseOrders()
            ->whereIn('status', ['received', 'partial'])
            ->sum('total_amount');
    }
}