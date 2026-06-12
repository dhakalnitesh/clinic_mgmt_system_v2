<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('districts')->insert([
            ['id' => 1, 'province_id' => 1, 'district_name' => 'Taplejung'],
            ['id' => 2, 'province_id' => 1, 'district_name' => 'Panchthar'],
            ['id' => 3, 'province_id' => 1, 'district_name' => 'Ilam'],
            ['id' => 4, 'province_id' => 1, 'district_name' => 'Jhapa'],
            ['id' => 5, 'province_id' => 1, 'district_name' => 'Morang'],
            ['id' => 6, 'province_id' => 1, 'district_name' => 'Sunsari'],
            ['id' => 7, 'province_id' => 1, 'district_name' => 'Dhankuta'],
            ['id' => 8, 'province_id' => 1, 'district_name' => 'Terhathum'],
            ['id' => 9, 'province_id' => 1, 'district_name' => 'Sankhuwasabha'],
            ['id' => 10, 'province_id' => 1, 'district_name' => 'Bhojpur'],
            ['id' => 11, 'province_id' => 1, 'district_name' => 'Solukhumbu'],
            ['id' => 12, 'province_id' => 1, 'district_name' => 'Okhaldhunga'],
            ['id' => 13, 'province_id' => 1, 'district_name' => 'Khotang'],
            ['id' => 14, 'province_id' => 1, 'district_name' => 'Udayapur'],

            ['id' => 15, 'province_id' => 2, 'district_name' => 'Saptari'],
            ['id' => 16, 'province_id' => 2, 'district_name' => 'Siraha'],
            ['id' => 17, 'province_id' => 2, 'district_name' => 'Dhanusha'],
            ['id' => 18, 'province_id' => 2, 'district_name' => 'Mahottari'],
            ['id' => 19, 'province_id' => 2, 'district_name' => 'Sarlahi'],
            ['id' => 20, 'province_id' => 2, 'district_name' => 'Rautahat'],
            ['id' => 21, 'province_id' => 2, 'district_name' => 'Bara'],
            ['id' => 22, 'province_id' => 2, 'district_name' => 'Parsa'],

            ['id' => 23, 'province_id' => 3, 'district_name' => 'Sindhuli'],
            ['id' => 24, 'province_id' => 3, 'district_name' => 'Ramechhap'],
            ['id' => 25, 'province_id' => 3, 'district_name' => 'Dolakha'],
            ['id' => 26, 'province_id' => 3, 'district_name' => 'Sindhupalchok'],
            ['id' => 27, 'province_id' => 3, 'district_name' => 'Kavrepalanchok'],
            ['id' => 28, 'province_id' => 3, 'district_name' => 'Lalitpur'],
            ['id' => 29, 'province_id' => 3, 'district_name' => 'Bhaktapur'],
            ['id' => 30, 'province_id' => 3, 'district_name' => 'Kathmandu'],
            ['id' => 31, 'province_id' => 3, 'district_name' => 'Nuwakot'],
            ['id' => 32, 'province_id' => 3, 'district_name' => 'Rasuwa'],
            ['id' => 33, 'province_id' => 3, 'district_name' => 'Dhading'],
            ['id' => 34, 'province_id' => 3, 'district_name' => 'Makwanpur'],
            ['id' => 35, 'province_id' => 3, 'district_name' => 'Chitwan'],

            ['id' => 36, 'province_id' => 4, 'district_name' => 'Gorkha'],
            ['id' => 37, 'province_id' => 4, 'district_name' => 'Lamjung'],
            ['id' => 38, 'province_id' => 4, 'district_name' => 'Tanahun'],
            ['id' => 39, 'province_id' => 4, 'district_name' => 'Syangja'],
            ['id' => 40, 'province_id' => 4, 'district_name' => 'Kaski'],
            ['id' => 41, 'province_id' => 4, 'district_name' => 'Manang'],
            ['id' => 42, 'province_id' => 4, 'district_name' => 'Mustang'],
            ['id' => 43, 'province_id' => 4, 'district_name' => 'Myagdi'],
            ['id' => 44, 'province_id' => 4, 'district_name' => 'Parbat'],
            ['id' => 45, 'province_id' => 4, 'district_name' => 'Baglung'],
            ['id' => 46, 'province_id' => 4, 'district_name' => 'Nawalparasi'],

            ['id' => 47, 'province_id' => 5, 'district_name' => 'Gulmi'],
            ['id' => 48, 'province_id' => 5, 'district_name' => 'Palpa'],
            ['id' => 49, 'province_id' => 5, 'district_name' => 'Rupandehi'],
            ['id' => 50, 'province_id' => 5, 'district_name' => 'Kapilvastu'],
            ['id' => 51, 'province_id' => 5, 'district_name' => 'Arghakhanchi'],
            ['id' => 52, 'province_id' => 5, 'district_name' => 'Pyuthan'],
            ['id' => 53, 'province_id' => 5, 'district_name' => 'Rolpa'],
            ['id' => 54, 'province_id' => 5, 'district_name' => 'Rukum'],
            ['id' => 55, 'province_id' => 5, 'district_name' => 'Dang'],
            ['id' => 56, 'province_id' => 5, 'district_name' => 'Banke'],
            ['id' => 57, 'province_id' => 5, 'district_name' => 'Bardiya'],

            ['id' => 58, 'province_id' => 6, 'district_name' => 'Salyan'],
            ['id' => 59, 'province_id' => 6, 'district_name' => 'Surkhet'],
            ['id' => 60, 'province_id' => 6, 'district_name' => 'Dailekh'],
            ['id' => 61, 'province_id' => 6, 'district_name' => 'Jajarkot'],
            ['id' => 62, 'province_id' => 6, 'district_name' => 'Dolpa'],
            ['id' => 63, 'province_id' => 6, 'district_name' => 'Jumla'],
            ['id' => 64, 'province_id' => 6, 'district_name' => 'Kalikot'],
            ['id' => 65, 'province_id' => 6, 'district_name' => 'Mugu'],
            ['id' => 66, 'province_id' => 6, 'district_name' => 'Humla'],

            ['id' => 67, 'province_id' => 7, 'district_name' => 'Bajura'],
            ['id' => 68, 'province_id' => 7, 'district_name' => 'Bajhang'],
            ['id' => 69, 'province_id' => 7, 'district_name' => 'Achham'],
            ['id' => 70, 'province_id' => 7, 'district_name' => 'Doti'],
            ['id' => 71, 'province_id' => 7, 'district_name' => 'Kailali'],
            ['id' => 72, 'province_id' => 7, 'district_name' => 'Kanchanpur'],
            ['id' => 73, 'province_id' => 7, 'district_name' => 'Dadeldhura'],
            ['id' => 74, 'province_id' => 7, 'district_name' => 'Baitadi'],
            ['id' => 75, 'province_id' => 7, 'district_name' => 'Darchula'],
        ]);
    }
}
