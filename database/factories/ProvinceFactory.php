<?php

namespace Database\Factories;

use App\Models\Address\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProvinceFactory extends Factory
{
    protected $model = Province::class;

    public function definition(): array
    {
        return [
            'province_name' => fake()->unique()->word() . ' Province',
        ];
    }
}
