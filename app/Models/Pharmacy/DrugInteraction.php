<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DrugInteraction extends Model
{
    protected $fillable = [
        'generic_id_1',
        'generic_id_2',
        'severity',
        'description',
        'management',
        'reference',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function generic1(): BelongsTo
    {
        return $this->belongsTo(Generic::class, 'generic_id_1');
    }

    public function generic2(): BelongsTo
    {
        return $this->belongsTo(Generic::class, 'generic_id_2');
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeMajorOrAbove(Builder $query): Builder
    {
        return $query->whereIn('severity', ['major', 'contraindicated']);
    }

    // ── Static Helpers ─────────────────────────────────────────────

    /**
     * Check interactions between a list of generic IDs.
     * Used at dispensing time to alert the pharmacist.
     *
     * @param  array $genericIds  IDs of generics being dispensed together
     * @return Collection         Collection of DrugInteraction models found
     */
    public static function checkInteractions(array $genericIds): Collection
    {
        if (count($genericIds) < 2) {
            return collect();
        }

        return static::active()
            ->where(function (Builder $q) use ($genericIds) {
                $q->where(function ($q2) use ($genericIds) {
                    $q2->whereIn('generic_id_1', $genericIds)
                       ->whereIn('generic_id_2', $genericIds);
                })->orWhere(function ($q2) use ($genericIds) {
                    $q2->whereIn('generic_id_2', $genericIds)
                       ->whereIn('generic_id_1', $genericIds);
                });
            })
            ->with(['generic1', 'generic2'])
            ->get()
            ->filter(function ($interaction) use ($genericIds) {
                // Only return pairs where BOTH generics are in the list
                return in_array($interaction->generic_id_1, $genericIds)
                    && in_array($interaction->generic_id_2, $genericIds);
            })
            ->values();
    }

    // ── Accessors ──────────────────────────────────────────────────

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'minor'          => 'blue',
            'moderate'       => 'amber',
            'major'          => 'orange',
            'contraindicated' => 'red',
            default          => 'slate',
        };
    }

    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'minor'          => 'Minor',
            'moderate'       => 'Moderate',
            'major'          => 'Major',
            'contraindicated' => 'Contraindicated',
            default          => ucfirst($this->severity),
        };
    }
}