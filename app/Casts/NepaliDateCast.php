<?php

namespace App\Casts;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class NepaliDateCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (!$value) return null;

        try {
            return LaravelNepaliDate::from($value)->toNepaliDate();
        } catch (\Throwable) {
            return $value;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (!$value) return null;

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $year = (int) substr($value, 0, 4);
            if ($year > 2000) {
                try {
                    return LaravelNepaliDate::from($value)->toEnglishDate();
                } catch (\Throwable) {
                    return $value;
                }
            }
        }

        return $value;
    }
}
