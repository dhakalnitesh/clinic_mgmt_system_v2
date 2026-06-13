<?php

namespace App\Models\Billing;

use App\Models\Patient\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'subtotal',
        'discount',
        'tax_percent',
        'tax_amount',
        'total',
        'paid_amount',
        'refunded_amount',
        'status',
        'due_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    public function scopePending($q)
    {
        $q->where('status', self::STATUS_PENDING);
    }

    public function scopePartial($q)
    {
        $q->where('status', self::STATUS_PARTIAL);
    }

    public function scopePaid($q)
    {
        $q->where('status', self::STATUS_PAID);
    }

    public function scopeCancelled($q)
    {
        $q->where('status', self::STATUS_CANCELLED);
    }

    public function scopeActive($q)
    {
        $q->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIAL]);
    }

    public function scopeOverdue($q)
    {
        $q->active()->whereDate('due_date', '<', now())
          ->orWhere(function ($q2) {
              $q2->active()->whereNull('due_date')->whereDate('created_at', '<', now()->subDays(30));
          });
    }

    public function getDueAmountAttribute()
    {
        return max(0, $this->total - $this->paid_amount + $this->refunded_amount);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
