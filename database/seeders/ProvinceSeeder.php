<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{

    public function run(): void
    {
        DB::table('provinces')->insert([
            [
                'id' => 1,
                'province_name' => 'Koshi',
                'created_at' => '2019-01-06 05:17:54',
                'updated_at' => '2019-01-06 05:17:54',
            ],
            [
                'id' => 2,
                'province_name' => 'Mahadesh',
                'created_at' => '2019-01-06 05:18:23',
                'updated_at' => '2019-01-06 05:18:23',
            ],
            [
                'id' => 3,
                'province_name' => 'Bagmati',
                'created_at' => '2019-01-06 05:18:29',
                'updated_at' => '2019-01-06 05:18:29',
            ],
            [
                'id' => 4,
                'province_name' => 'Gandaki',
                'created_at' => '2019-01-06 05:18:38',
                'updated_at' => '2019-01-06 05:18:38',
            ],
            [
                'id' => 5,
                'province_name' => 'Lumbini',
                'created_at' => '2019-01-06 05:18:44',
                'updated_at' => '2019-01-06 05:18:44',
            ],
            [
                'id' => 6,
                'province_name' => 'Karnali',
                'created_at' => '2019-01-06 05:18:52',
                'updated_at' => '2019-01-06 05:18:52',
            ],
            [
                'id' => 7,
                'province_name' => 'Sudurpashchim',
                'created_at' => '2019-01-06 05:18:59',
                'updated_at' => '2019-01-06 05:18:59',
            ],
        ]);
    }
}
