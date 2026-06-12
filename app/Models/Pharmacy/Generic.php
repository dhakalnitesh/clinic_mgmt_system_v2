<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Generic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'pharmacological_class',
        'description', 'is_controlled', 'is_active',
    ];

    protected $casts = [
        'is_controlled' => 'boolean',
        'is_active'     => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────
    public function medicines(): HasMany
    {
        return $this->hasMany(Medicine::class);
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    /**
     * Interactions where this generic is drug A or drug B.
     * We use two separate hasMany and merge in the model.
     */
    public function interactionsAsA(): HasMany
    {
        return $this->hasMany(DrugInteraction::class, 'generic_id_1');
    }

    public function interactionsAsB(): HasMany
    {
        return $this->hasMany(DrugInteraction::class, 'generic_id_2');
    }

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Helpers ────────────────────────────────────────────────────

    /**
     * Get all drug interactions for this generic (as either party).
     */
    public function getAllInteractions()
    {
        return DrugInteraction::where('generic_id_1', $this->id)
            ->orWhere('generic_id_2', $this->id)
            ->where('is_active', true)
            ->get();
    }
}