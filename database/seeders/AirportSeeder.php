<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class AirportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $airports = [
            [
                'code' => 'CGK',
                'name' => 'Soekarno-Hatta International Airport',
                'city' => 'Jakarta',
                'country' => 'Indonesia',
            ],
            [
                'code' => 'DPS',
                'name' => 'I Gusti Ngurah Rai International Airport',
                'city' => 'Denpasar',
                'country' => 'Indonesia',
            ],
            [
                'code' => 'SUB',
                'name' => 'Juanda International Airport',
                'city' => 'Surabaya',
                'country' => 'Indonesia',
            ],
            [
                'code' => 'KNO',
                'name' => 'Kualanamu International Airport',
                'city' => 'Medan',
                'country' => 'Indonesia',
            ],
            [
                'code' => 'UPG',
                'name' => 'Sultan Hasanuddin International Airport',
                'city' => 'Makassar',
                'country' => 'Indonesia',
            ],
            [
                'code' => 'YIA',
                'name' => 'Yogyakarta International Airport',
                'city' => 'Yogyakarta',
                'country' => 'Indonesia',
            ],
            [
                'code' => 'PNK',
                'name' => 'Supadio International Airport',
                'city' => 'Pontianak',
                'country' => 'Indonesia',
            ],
        ];

        foreach ($airports as $airport) {
            Airport::updateOrCreate(['code' => $airport['code']], $airport);
        }
    }
}
