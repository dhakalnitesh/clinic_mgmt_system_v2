<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Doctor\Doctor;
use App\Models\Patient\Patient;
use App\Models\User;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $appends = ['created_at_bs'];

    protected $fillable = [
        'consultation_id',
        'patient_id',
        'doctor_id',
        'encounter_id',
        'prescription_number',
        'prescribed_at',
        'status',
        'dispensed_by',
        'dispensed_at',
        'advices',
        'created_by',
        'updated_by',
        'notes',
    ];

    protected $casts = [
        'prescribed_at' => 'date',
        'dispensed_at'  => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Consultation\Consultation::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sales::class);
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // ── Helpers ────────────────────────────────────────────────────

    public static function generatePrescriptionNumber(): string
    {
        $year   = now()->format('Y');
        $prefix = "RX-{$year}-";
        $last   = static::withTrashed()
                        ->where('prescription_number', 'like', "{$prefix}%")
                        ->orderByDesc('id')->first();
        $seq    = $last ? ((int) substr($last->prescription_number, -5)) + 1 : 1;

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Update overall prescription status based on items.
     */
    public function updateStatus(): void
    {
        $items    = $this->items;
        $total    = $items->count();
        $dispensed = $items->where('status', 'dispensed')->count();

        if ($dispensed === 0) {
            $status = 'pending';
        } elseif ($dispensed < $total) {
            $status = 'partial';
        } else {
            $status = 'dispensed';
        }

        $this->update(['status' => $status]);
    }

    public function getCreatedAtBsAttribute()
    {
        if (!$this->created_at) return null;
        try {
            return \Anuzpandey\LaravelNepaliDate\LaravelNepaliDate::from($this->created_at->format('Y-m-d'))->toNepaliDate();
        } catch (\Throwable) {
            return $this->created_at->format('Y-m-d');
        }
    }
}