<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NepalAddressSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            ['id' => 1, 'province_name' => 'Province No. 1'],
            ['id' => 2, 'province_name' => 'Madhesh Province'],
            ['id' => 3, 'province_name' => 'Bagmati Province'],
            ['id' => 4, 'province_name' => 'Gandaki Province'],
            ['id' => 5, 'province_name' => 'Lumbini Province'],
            ['id' => 6, 'province_name' => 'Karnali Province'],
            ['id' => 7, 'province_name' => 'Sudurpashchim Province'],
        ];
        DB::table('provinces')->insert($provinces);

        $districts = [
            // Province 1
            ['id' => 1, 'province_id' => 1, 'district_name' => 'Taplejung'],
            ['id' => 2, 'province_id' => 1, 'district_name' => 'Sankhuwasabha'],
            ['id' => 3, 'province_id' => 1, 'district_name' => 'Solukhumbu'],
            ['id' => 4, 'province_id' => 1, 'district_name' => 'Khotang'],
            ['id' => 5, 'province_id' => 1, 'district_name' => 'Okhaldhunga'],
            ['id' => 6, 'province_id' => 1, 'district_name' => 'Udayapur'],
            ['id' => 7, 'province_id' => 1, 'district_name' => 'Bhojpur'],
            ['id' => 8, 'province_id' => 1, 'district_name' => 'Dhankuta'],
            ['id' => 9, 'province_id' => 1, 'district_name' => 'Terhathum'],
            ['id' => 10, 'province_id' => 1, 'district_name' => 'Panchthar'],
            ['id' => 11, 'province_id' => 1, 'district_name' => 'Ilam'],
            ['id' => 12, 'province_id' => 1, 'district_name' => 'Jhapa'],
            ['id' => 13, 'province_id' => 1, 'district_name' => 'Morang'],
            ['id' => 14, 'province_id' => 1, 'district_name' => 'Sunsari'],

            // Province 2 - Madhesh
            ['id' => 15, 'province_id' => 2, 'district_name' => 'Saptari'],
            ['id' => 16, 'province_id' => 2, 'district_name' => 'Siraha'],
            ['id' => 17, 'province_id' => 2, 'district_name' => 'Dhanusha'],
            ['id' => 18, 'province_id' => 2, 'district_name' => 'Mahottari'],
            ['id' => 19, 'province_id' => 2, 'district_name' => 'Sarlahi'],
            ['id' => 20, 'province_id' => 2, 'district_name' => 'Rautahat'],
            ['id' => 21, 'province_id' => 2, 'district_name' => 'Bara'],
            ['id' => 22, 'province_id' => 2, 'district_name' => 'Parsa'],

            // Province 3 - Bagmati
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

            // Province 4 - Gandaki
            ['id' => 36, 'province_id' => 4, 'district_name' => 'Gorkha'],
            ['id' => 37, 'province_id' => 4, 'district_name' => 'Manang'],
            ['id' => 38, 'province_id' => 4, 'district_name' => 'Mustang'],
            ['id' => 39, 'province_id' => 4, 'district_name' => 'Myagdi'],
            ['id' => 40, 'province_id' => 4, 'district_name' => 'Kaski'],
            ['id' => 41, 'province_id' => 4, 'district_name' => 'Lamjung'],
            ['id' => 42, 'province_id' => 4, 'district_name' => 'Tanahun'],
            ['id' => 43, 'province_id' => 4, 'district_name' => 'Nawalpur'],
            ['id' => 44, 'province_id' => 4, 'district_name' => 'Syangja'],
            ['id' => 45, 'province_id' => 4, 'district_name' => 'Parbat'],
            ['id' => 46, 'province_id' => 4, 'district_name' => 'Baglung'],

            // Province 5 - Lumbini
            ['id' => 47, 'province_id' => 5, 'district_name' => 'Rukum East'],
            ['id' => 48, 'province_id' => 5, 'district_name' => 'Rolpa'],
            ['id' => 49, 'province_id' => 5, 'district_name' => 'Pyuthan'],
            ['id' => 50, 'province_id' => 5, 'district_name' => 'Gulmi'],
            ['id' => 51, 'province_id' => 5, 'district_name' => 'Arghakhanchi'],
            ['id' => 52, 'province_id' => 5, 'district_name' => 'Palpa'],
            ['id' => 53, 'province_id' => 5, 'district_name' => 'Nawalparasi West'],
            ['id' => 54, 'province_id' => 5, 'district_name' => 'Rupandehi'],
            ['id' => 55, 'province_id' => 5, 'district_name' => 'Kapilvastu'],
            ['id' => 56, 'province_id' => 5, 'district_name' => 'Dang'],
            ['id' => 57, 'province_id' => 5, 'district_name' => 'Banke'],
            ['id' => 58, 'province_id' => 5, 'district_name' => 'Bardiya'],

            // Province 6 - Karnali
            ['id' => 59, 'province_id' => 6, 'district_name' => 'Dolpa'],
            ['id' => 60, 'province_id' => 6, 'district_name' => 'Mugu'],
            ['id' => 61, 'province_id' => 6, 'district_name' => 'Humla'],
            ['id' => 62, 'province_id' => 6, 'district_name' => 'Jumla'],
            ['id' => 63, 'province_id' => 6, 'district_name' => 'Kalikot'],
            ['id' => 64, 'province_id' => 6, 'district_name' => 'Dailekh'],
            ['id' => 65, 'province_id' => 6, 'district_name' => 'Jajarkot'],
            ['id' => 66, 'province_id' => 6, 'district_name' => 'Rukum West'],
            ['id' => 67, 'province_id' => 6, 'district_name' => 'Salyan'],
            ['id' => 68, 'province_id' => 6, 'district_name' => 'Surkhet'],

            // Province 7 - Sudurpashchim
            ['id' => 69, 'province_id' => 7, 'district_name' => 'Bajura'],
            ['id' => 70, 'province_id' => 7, 'district_name' => 'Bajhang'],
            ['id' => 71, 'province_id' => 7, 'district_name' => 'Doti'],
            ['id' => 72, 'province_id' => 7, 'district_name' => 'Achham'],
            ['id' => 73, 'province_id' => 7, 'district_name' => 'Darchula'],
            ['id' => 74, 'province_id' => 7, 'district_name' => 'Baitadi'],
            ['id' => 75, 'province_id' => 7, 'district_name' => 'Dadeldhura'],
            ['id' => 76, 'province_id' => 7, 'district_name' => 'Kanchanpur'],
            ['id' => 77, 'province_id' => 7, 'district_name' => 'Kailali'],
        ];
        DB::table('districts')->insert($districts);

        $municipals = [
            // Kathmandu valley key municipalities
            ['id' => 1, 'district_id' => 30, 'municipal_name' => 'Kathmandu Metropolitan'],
            ['id' => 2, 'district_id' => 30, 'municipal_name' => 'Kirtipur'],
            ['id' => 3, 'district_id' => 30, 'municipal_name' => 'Budhanilkantha'],
            ['id' => 4, 'district_id' => 30, 'municipal_name' => 'Chandragiri'],
            ['id' => 5, 'district_id' => 30, 'municipal_name' => 'Dakshinkali'],
            ['id' => 6, 'district_id' => 30, 'municipal_name' => 'Gokarneshwar'],
            ['id' => 7, 'district_id' => 30, 'municipal_name' => 'Nagarjun'],
            ['id' => 8, 'district_id' => 30, 'municipal_name' => 'Shankharapur'],
            ['id' => 9, 'district_id' => 30, 'municipal_name' => 'Tarakeshwar'],
            ['id' => 10, 'district_id' => 30, 'municipal_name' => 'Tokha'],

            ['id' => 11, 'district_id' => 28, 'municipal_name' => 'Lalitpur Metropolitan'],
            ['id' => 12, 'district_id' => 28, 'municipal_name' => 'Godawari'],
            ['id' => 13, 'district_id' => 28, 'municipal_name' => 'Mahalaxmi'],
            ['id' => 14, 'district_id' => 28, 'municipal_name' => 'Bajrabarahi'],

            ['id' => 15, 'district_id' => 29, 'municipal_name' => 'Bhaktapur'],
            ['id' => 16, 'district_id' => 29, 'municipal_name' => 'Madhyapur Thimi'],
            ['id' => 17, 'district_id' => 29, 'municipal_name' => 'Changunarayan'],
            ['id' => 18, 'district_id' => 29, 'municipal_name' => 'Suryabinayak'],

            // Key cities in other districts
            ['id' => 19, 'district_id' => 13, 'municipal_name' => 'Biratnagar Metropolitan'],
            ['id' => 20, 'district_id' => 13, 'municipal_name' => 'Urlabari'],
            ['id' => 21, 'district_id' => 14, 'municipal_name' => 'Dharan'],
            ['id' => 22, 'district_id' => 14, 'municipal_name' => 'Itahari'],
            ['id' => 23, 'district_id' => 14, 'municipal_name' => 'Inaruwa'],
            ['id' => 24, 'district_id' => 12, 'municipal_name' => 'Bhadrapur'],
            ['id' => 25, 'district_id' => 12, 'municipal_name' => 'Mechinagar'],
            ['id' => 26, 'district_id' => 12, 'municipal_name' => 'Damak'],
            ['id' => 27, 'district_id' => 12, 'municipal_name' => 'Arjundhara'],

            ['id' => 28, 'district_id' => 54, 'municipal_name' => 'Butwal'],
            ['id' => 29, 'district_id' => 54, 'municipal_name' => 'Devdaha'],
            ['id' => 30, 'district_id' => 54, 'municipal_name' => 'Siddharthanagar'],
            ['id' => 31, 'district_id' => 54, 'municipal_name' => 'Lumbini Sanskritik'],

            ['id' => 32, 'district_id' => 40, 'municipal_name' => 'Pokhara Metropolitan'],
            ['id' => 33, 'district_id' => 35, 'municipal_name' => 'Bharatpur Metropolitan'],
            ['id' => 34, 'district_id' => 35, 'municipal_name' => 'Ratnanagar'],
            ['id' => 35, 'district_id' => 35, 'municipal_name' => 'Khairahani'],
            ['id' => 36, 'district_id' => 35, 'municipal_name' => 'Madi'],

            ['id' => 37, 'district_id' => 56, 'municipal_name' => 'Ghorahi'],
            ['id' => 38, 'district_id' => 56, 'municipal_name' => 'Tulsipur'],
            ['id' => 39, 'district_id' => 57, 'municipal_name' => 'Nepalgunj'],
            ['id' => 40, 'district_id' => 68, 'municipal_name' => 'Birendranagar'],
            ['id' => 41, 'district_id' => 77, 'municipal_name' => 'Dhangadhi'],
            ['id' => 42, 'district_id' => 77, 'municipal_name' => 'Tikapur'],
            ['id' => 43, 'district_id' => 76, 'municipal_name' => 'Mahendranagar'],
            ['id' => 44, 'district_id' => 75, 'municipal_name' => 'Amargadhi'],
            ['id' => 45, 'district_id' => 17, 'municipal_name' => 'Janakpur'],

            ['id' => 46, 'district_id' => 20, 'municipal_name' => 'Gaur'],
            ['id' => 47, 'district_id' => 21, 'municipal_name' => 'Kalaiya'],
            ['id' => 48, 'district_id' => 22, 'municipal_name' => 'Birgunj Metropolitan'],
            ['id' => 49, 'district_id' => 15, 'municipal_name' => 'Rajbiraj'],
            ['id' => 50, 'district_id' => 19, 'municipal_name' => 'Malangwa'],
        ];
        DB::table('municipals')->insert($municipals);
    }
}
