<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\Airplane;
use App\Models\Airport;
use App\Models\Flight;
use App\Models\Seat;
use App\Support\CabinClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IndonesiaDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $airplanes = $this->seedAirplanes();
            $this->seedSeats($airplanes);
            $this->seedFlights($airplanes);
        });
    }

    /**
     * @return array<string, Airplane>
     */
    protected function seedAirplanes(): array
    {
        $airplanes = [
            [
                'registration_number' => 'PK-DGA1',
                'airline_code' => 'GA',
                'model' => 'Boeing 737-800NG',
                'capacity' => 162,
                'description' => 'Armada Garuda Indonesia untuk rute domestik padat.',
            ],
            [
                'registration_number' => 'PK-CQG1',
                'airline_code' => 'QG',
                'model' => 'Airbus A320-200',
                'capacity' => 180,
                'description' => 'Armada Citilink untuk rute point-to-point berbiaya hemat.',
            ],
            [
                'registration_number' => 'PK-LJT1',
                'airline_code' => 'JT',
                'model' => 'Boeing 737-900ER',
                'capacity' => 215,
                'description' => 'Armada Lion Air untuk rute domestik dengan permintaan tinggi.',
            ],
        ];

        $records = [];

        foreach ($airplanes as $payload) {
            $airline = Airline::query()->where('code', $payload['airline_code'])->firstOrFail();

            $records[$payload['registration_number']] = Airplane::updateOrCreate(
                ['registration_number' => $payload['registration_number']],
                [
                    'airline_id' => $airline->id,
                    'model' => $payload['model'],
                    'capacity' => $payload['capacity'],
                    'description' => $payload['description'],
                    'photo' => null,
                ]
            );
        }

        return $records;
    }

    /**
     * @param  array<string, Airplane>  $airplanes
     */
    protected function seedSeats(array $airplanes): void
    {
        $plans = [
            'PK-DGA1' => ['first_rows' => 0, 'business_rows' => 3],
            'PK-CQG1' => ['first_rows' => 0, 'business_rows' => 0],
            'PK-LJT1' => ['first_rows' => 0, 'business_rows' => 0],
        ];

        foreach ($plans as $registration => $plan) {
            $this->syncSeats(
                $airplanes[$registration],
                $plan['business_rows'],
                $plan['first_rows']
            );
        }
    }

    /**
     * @param  array<string, Airplane>  $airplanes
     */
    protected function seedFlights(array $airplanes): void
    {
        $flights = [
            [
                'flight_number' => 'GA402',
                'airline_code' => 'GA',
                'registration_number' => 'PK-DGA1',
                'from' => 'CGK',
                'to' => 'DPS',
                'departure_time' => Carbon::today()->addDays(2)->setTime(7, 0),
                'arrival_time' => Carbon::today()->addDays(2)->setTime(9, 55),
                'price' => 1650000,
                'status' => 'scheduled',
            ],
            [
                'flight_number' => 'QG602',
                'airline_code' => 'QG',
                'registration_number' => 'PK-CQG1',
                'from' => 'CGK',
                'to' => 'SUB',
                'departure_time' => Carbon::today()->addDay()->setTime(6, 10),
                'arrival_time' => Carbon::today()->addDay()->setTime(7, 40),
                'price' => 825000,
                'status' => 'scheduled',
            ],
            [
                'flight_number' => 'JT792',
                'airline_code' => 'JT',
                'registration_number' => 'PK-LJT1',
                'from' => 'CGK',
                'to' => 'KNO',
                'departure_time' => Carbon::today()->addDays(4)->setTime(13, 15),
                'arrival_time' => Carbon::today()->addDays(4)->setTime(15, 35),
                'price' => 1145000,
                'status' => 'scheduled',
            ],
        ];

        foreach ($flights as $payload) {
            $airline = Airline::query()->where('code', $payload['airline_code'])->firstOrFail();
            $departureAirport = Airport::query()->where('code', $payload['from'])->firstOrFail();
            $arrivalAirport = Airport::query()->where('code', $payload['to'])->firstOrFail();

            Flight::updateOrCreate(
                ['flight_number' => $payload['flight_number']],
                [
                    'airline_id' => $airline->id,
                    'airplane_id' => $airplanes[$payload['registration_number']]->id,
                    'departure_airport_id' => $departureAirport->id,
                    'arrival_airport_id' => $arrivalAirport->id,
                    'departure_time' => $payload['departure_time'],
                    'arrival_time' => $payload['arrival_time'],
                    'price' => $payload['price'],
                    'status' => $payload['status'],
                ]
            );
        }
    }

    protected function syncSeats(Airplane $airplane, int $businessRows = 0, int $firstRows = 0): void
    {
        $airplane->seats()->delete();

        $rows = [];
        $rowNumber = 1;
        $remainingSeats = $airplane->capacity;

        for ($index = 0; $index < $firstRows; $index++) {
            $rows[] = ['row' => $rowNumber++, 'class' => 'first', 'columns' => CabinClass::layout('first')];
            $remainingSeats -= count(CabinClass::layout('first'));
        }

        for ($index = 0; $index < $businessRows; $index++) {
            $rows[] = ['row' => $rowNumber++, 'class' => 'business', 'columns' => CabinClass::layout('business')];
            $remainingSeats -= count(CabinClass::layout('business'));
        }

        while ($remainingSeats > 0) {
            $columns = CabinClass::layout('economy');

            if ($remainingSeats < count($columns)) {
                $columns = array_slice($columns, 0, $remainingSeats);
            }

            $rows[] = ['row' => $rowNumber++, 'class' => 'economy', 'columns' => $columns];
            $remainingSeats -= count($columns);
        }

        foreach ($rows as $row) {
            foreach ($row['columns'] as $column) {
                Seat::create([
                    'airplane_id' => $airplane->id,
                    'seat_number' => $row['row'].$column,
                    'class' => $row['class'],
                ]);
            }
        }
    }
}
