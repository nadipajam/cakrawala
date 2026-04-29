<?php

namespace Database\Seeders;

use App\Models\Airline;
use Illuminate\Database\Seeder;

class AirlineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $airlines = [
            [
                'name' => 'Garuda Indonesia',
                'code' => 'GA',
                'description' => 'Maskapai flag carrier Indonesia untuk rute domestik dan internasional.',
            ],
            [
                'name' => 'Citilink',
                'code' => 'QG',
                'description' => 'Maskapai berbiaya hemat dengan fokus rute domestik padat di Indonesia.',
            ],
            [
                'name' => 'Lion Air',
                'code' => 'JT',
                'description' => 'Maskapai swasta Indonesia dengan jaringan domestik yang luas.',
            ],
            [
                'name' => 'Batik Air',
                'code' => 'ID',
                'description' => 'Maskapai layanan penuh Lion Group untuk rute domestik dan regional.',
            ],
            [
                'name' => 'Super Air Jet',
                'code' => 'IU',
                'description' => 'Maskapai domestik berbiaya hemat yang melayani kota-kota besar Indonesia.',
            ],
            [
                'name' => 'Pelita Air',
                'code' => 'IP',
                'description' => 'Maskapai nasional yang aktif melayani rute domestik Indonesia.',
            ],
            [
                'name' => 'Indonesia AirAsia',
                'code' => 'QZ',
                'description' => 'Maskapai berbiaya hemat yang melayani rute domestik dan internasional dari Indonesia.',
            ],
            [
                'name' => 'TransNusa',
                'code' => '8B',
                'description' => 'Maskapai yang menghubungkan berbagai kota di Indonesia dan kawasan regional.',
            ],
        ];

        foreach ($airlines as $airline) {
            Airline::updateOrCreate(['code' => $airline['code']], $airline);
        }

        Airline::query()
            ->whereIn('code', ['ZNA', 'ASK'])
            ->delete();
    }
}
