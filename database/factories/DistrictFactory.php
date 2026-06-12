<?php

namespace Database\Factories;

use App\Models\Address\District;
use App\Models\Address\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistrictFactory extends Factory
{
    protected $model = District::class;

    public function definition(): array
    {
        return [
            'province_id' => Province::factory(),
            'district_name' => fake()->unique()->word() . ' District',
        ];
    }
}
