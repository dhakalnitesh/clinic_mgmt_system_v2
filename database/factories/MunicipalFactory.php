<?php

namespace Database\Factories;

use App\Models\Address\Municipal;
use App\Models\Address\District;
use Illuminate\Database\Eloquent\Factories\Factory;

class MunicipalFactory extends Factory
{
    protected $model = Municipal::class;

    public function definition(): array
    {
        return [
            'district_id' => District::factory(),
            'municipal_name' => fake()->unique()->word() . ' Municipality',
        ];
    }
}
