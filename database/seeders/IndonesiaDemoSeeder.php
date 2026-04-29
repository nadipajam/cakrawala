<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\Airplane;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\Ticket;
use App\Models\User;
use App\Support\CabinClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class IndonesiaDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $users = $this->seedUsers();
            $passengers = $this->seedPassengers($users);
            $airplanes = $this->seedAirplanes();

            $this->seedSeats($airplanes);

            $flights = $this->seedFlights($airplanes);

            $this->seedBookings($users, $passengers, $flights);
        });
    }

    /**
     * @return array<string, User>
     */
    protected function seedUsers(): array
    {
        $users = [
            'user@cakrawala.com' => [
                'name' => 'Demo User',
                'phone' => '08111111111',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
            'dewi.putri@cakrawala.com' => [
                'name' => 'Dewi Putri Maharani',
                'phone' => '081290001234',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
            'rafi.pratama@cakrawala.com' => [
                'name' => 'Rafi Pratama Saputra',
                'phone' => '081355551111',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
            'intan.lestari@cakrawala.com' => [
                'name' => 'Intan Lestari Wibowo',
                'phone' => '081377770909',
                'password' => Hash::make('password'),
                'role' => 'user',
            ],
        ];

        $records = [];

        foreach ($users as $email => $payload) {
            $records[$email] = User::updateOrCreate(
                ['email' => $email],
                [
                    ...$payload,
                    'email_verified_at' => now(),
                ]
            );
        }

        return $records;
    }

    /**
     * @param  array<string, User>  $users
     * @return array<string, Passenger>
     */
    protected function seedPassengers(array $users): array
    {
        $passengers = [
            [
                'key' => 'aditya_pratama',
                'user_email' => 'user@cakrawala.com',
                'full_name' => 'Aditya Pratama',
                'gender' => 'male',
                'birth_date' => '1992-06-14',
                'passport_number' => 'A1234567',
                'identity_number' => '3174091406920001',
                'nationality' => 'Indonesia',
            ],
            [
                'key' => 'maya_lestari',
                'user_email' => 'user@cakrawala.com',
                'full_name' => 'Maya Lestari',
                'gender' => 'female',
                'birth_date' => '1994-11-05',
                'passport_number' => 'A1234568',
                'identity_number' => '3174090511940002',
                'nationality' => 'Indonesia',
            ],
            [
                'key' => 'dewi_maharani',
                'user_email' => 'dewi.putri@cakrawala.com',
                'full_name' => 'Dewi Putri Maharani',
                'gender' => 'female',
                'birth_date' => '1989-03-17',
                'passport_number' => 'B7654321',
                'identity_number' => '3578081703890003',
                'nationality' => 'Indonesia',
            ],
            [
                'key' => 'aksa_prananda',
                'user_email' => 'dewi.putri@cakrawala.com',
                'full_name' => 'Aksa Prananda',
                'gender' => 'male',
                'birth_date' => '2016-09-21',
                'passport_number' => null,
                'identity_number' => '3578082109160004',
                'nationality' => 'Indonesia',
            ],
            [
                'key' => 'rafi_saputra',
                'user_email' => 'rafi.pratama@cakrawala.com',
                'full_name' => 'Rafi Pratama Saputra',
                'gender' => 'male',
                'birth_date' => '1990-01-28',
                'passport_number' => 'C1029384',
                'identity_number' => '3273052801900005',
                'nationality' => 'Indonesia',
            ],
            [
                'key' => 'intan_wibowo',
                'user_email' => 'intan.lestari@cakrawala.com',
                'full_name' => 'Intan Lestari Wibowo',
                'gender' => 'female',
                'birth_date' => '1993-07-30',
                'passport_number' => 'D5647382',
                'identity_number' => '6471053007930006',
                'nationality' => 'Indonesia',
            ],
            [
                'key' => 'bagas_wibowo',
                'user_email' => 'intan.lestari@cakrawala.com',
                'full_name' => 'Bagas Wibowo',
                'gender' => 'male',
                'birth_date' => '1991-12-09',
                'passport_number' => 'D5647383',
                'identity_number' => '6471050912910007',
                'nationality' => 'Indonesia',
            ],
        ];

        $records = [];

        foreach ($passengers as $payload) {
            $user = $users[$payload['user_email']];

            $records[$payload['key']] = Passenger::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'identity_number' => $payload['identity_number'],
                ],
                Arr::except($payload, ['key', 'user_email'])
            );
        }

        return $records;
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
                'description' => 'Armada Garuda Indonesia untuk rute domestik padat seperti Jakarta, Bali, dan Surabaya.',
            ],
            [
                'registration_number' => 'PK-CQG1',
                'airline_code' => 'QG',
                'model' => 'Airbus A320-200',
                'capacity' => 180,
                'description' => 'Armada Citilink untuk penerbangan point-to-point berbiaya hemat.',
            ],
            [
                'registration_number' => 'PK-LJT1',
                'airline_code' => 'JT',
                'model' => 'Boeing 737-900ER',
                'capacity' => 215,
                'description' => 'Armada Lion Air untuk rute domestik dengan permintaan tinggi.',
            ],
            [
                'registration_number' => 'PK-BID1',
                'airline_code' => 'ID',
                'model' => 'Boeing 737 MAX 8',
                'capacity' => 174,
                'description' => 'Armada Batik Air untuk layanan domestik dan regional kelas penuh.',
            ],
            [
                'registration_number' => 'PK-SIU1',
                'airline_code' => 'IU',
                'model' => 'Airbus A320-200',
                'capacity' => 180,
                'description' => 'Armada Super Air Jet untuk rute generasi muda dan leisure di Indonesia.',
            ],
            [
                'registration_number' => 'PK-PIP1',
                'airline_code' => 'IP',
                'model' => 'Airbus A320-200',
                'capacity' => 180,
                'description' => 'Armada Pelita Air untuk rute domestik antar kota besar Indonesia.',
            ],
            [
                'registration_number' => 'PK-AQZ1',
                'airline_code' => 'QZ',
                'model' => 'Airbus A320-200',
                'capacity' => 180,
                'description' => 'Armada Indonesia AirAsia untuk rute hemat dan leisure dari Indonesia.',
            ],
            [
                'registration_number' => 'PK-T8B1',
                'airline_code' => '8B',
                'model' => 'COMAC ARJ21-700',
                'capacity' => 95,
                'description' => 'Armada TransNusa untuk konektivitas regional dan antarkota menengah.',
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
            'PK-BID1' => ['first_rows' => 0, 'business_rows' => 2],
            'PK-SIU1' => ['first_rows' => 0, 'business_rows' => 0],
            'PK-PIP1' => ['first_rows' => 0, 'business_rows' => 2],
            'PK-AQZ1' => ['first_rows' => 0, 'business_rows' => 0],
            'PK-T8B1' => ['first_rows' => 0, 'business_rows' => 0],
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
     * @return array<string, Flight>
     */
    protected function seedFlights(array $airplanes): array
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
                'flight_number' => 'GA411',
                'airline_code' => 'GA',
                'registration_number' => 'PK-DGA1',
                'from' => 'CGK',
                'to' => 'SUB',
                'departure_time' => Carbon::today()->subDays(5)->setTime(8, 15),
                'arrival_time' => Carbon::today()->subDays(5)->setTime(9, 45),
                'price' => 895000,
                'status' => 'completed',
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
                'status' => 'delayed',
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
            [
                'flight_number' => 'JT793',
                'airline_code' => 'JT',
                'registration_number' => 'PK-LJT1',
                'from' => 'KNO',
                'to' => 'CGK',
                'departure_time' => Carbon::today()->subDays(2)->setTime(9, 20),
                'arrival_time' => Carbon::today()->subDays(2)->setTime(11, 35),
                'price' => 1095000,
                'status' => 'completed',
            ],
            [
                'flight_number' => 'ID6580',
                'airline_code' => 'ID',
                'registration_number' => 'PK-BID1',
                'from' => 'UPG',
                'to' => 'CGK',
                'departure_time' => Carbon::today()->addDays(5)->setTime(15, 45),
                'arrival_time' => Carbon::today()->addDays(5)->setTime(18, 10),
                'price' => 1490000,
                'status' => 'scheduled',
            ],
            [
                'flight_number' => 'ID6581',
                'airline_code' => 'ID',
                'registration_number' => 'PK-BID1',
                'from' => 'CGK',
                'to' => 'UPG',
                'departure_time' => Carbon::today()->addDays(3)->setTime(19, 10),
                'arrival_time' => Carbon::today()->addDays(3)->setTime(21, 30),
                'price' => 1430000,
                'status' => 'cancelled',
            ],
            [
                'flight_number' => 'IU745',
                'airline_code' => 'IU',
                'registration_number' => 'PK-SIU1',
                'from' => 'SUB',
                'to' => 'DPS',
                'departure_time' => Carbon::today()->addDays(6)->setTime(9, 35),
                'arrival_time' => Carbon::today()->addDays(6)->setTime(10, 45),
                'price' => 910000,
                'status' => 'scheduled',
            ],
            [
                'flight_number' => 'IP240',
                'airline_code' => 'IP',
                'registration_number' => 'PK-PIP1',
                'from' => 'CGK',
                'to' => 'YIA',
                'departure_time' => Carbon::today()->addDays(2)->setTime(11, 20),
                'arrival_time' => Carbon::today()->addDays(2)->setTime(12, 35),
                'price' => 735000,
                'status' => 'scheduled',
            ],
            [
                'flight_number' => 'QZ7530',
                'airline_code' => 'QZ',
                'registration_number' => 'PK-AQZ1',
                'from' => 'CGK',
                'to' => 'DPS',
                'departure_time' => Carbon::today()->addDays(7)->setTime(17, 55),
                'arrival_time' => Carbon::today()->addDays(7)->setTime(20, 50),
                'price' => 1295000,
                'status' => 'scheduled',
            ],
            [
                'flight_number' => '8B555',
                'airline_code' => '8B',
                'registration_number' => 'PK-T8B1',
                'from' => 'YIA',
                'to' => 'UPG',
                'departure_time' => Carbon::today()->addDays(8)->setTime(8, 10),
                'arrival_time' => Carbon::today()->addDays(8)->setTime(10, 50),
                'price' => 1190000,
                'status' => 'scheduled',
            ],
        ];

        $records = [];

        foreach ($flights as $payload) {
            $airline = Airline::query()->where('code', $payload['airline_code'])->firstOrFail();
            $departureAirport = Airport::query()->where('code', $payload['from'])->firstOrFail();
            $arrivalAirport = Airport::query()->where('code', $payload['to'])->firstOrFail();

            $records[$payload['flight_number']] = Flight::updateOrCreate(
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

        return $records;
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Passenger>  $passengers
     * @param  array<string, Flight>  $flights
     */
    protected function seedBookings(array $users, array $passengers, array $flights): void
    {
        $bookings = [
            [
                'booking_code' => 'BK-ZNR-260402-001',
                'user_email' => 'user@cakrawala.com',
                'flight_number' => 'GA402',
                'status' => 'pending',
                'payment' => [
                    'payment_method' => 'virtual_account_bca',
                    'payment_status' => 'pending',
                    'transaction_code' => null,
                    'paid_at' => null,
                    'proof_file' => 'payments/demo/bk-znr-260402-001.jpg',
                ],
                'details' => [
                    [
                        'passenger_key' => 'aditya_pratama',
                        'seat_number' => '3A',
                        'boarding_status' => 'not_checked_in',
                        'issue_ticket' => false,
                    ],
                ],
                'booked_at' => Carbon::now()->subDays(4),
                'expires_at' => Carbon::now()->addHours(12),
            ],
            [
                'booking_code' => 'BK-ZNR-260402-002',
                'user_email' => 'dewi.putri@cakrawala.com',
                'flight_number' => 'QG602',
                'status' => 'confirmed',
                'payment' => [
                    'payment_method' => 'qris',
                    'payment_status' => 'paid',
                    'transaction_code' => 'TRX-QG602-2401',
                    'paid_at' => Carbon::now()->subDays(1)->setTime(10, 30),
                    'proof_file' => 'payments/demo/bk-znr-260402-002.jpg',
                ],
                'details' => [
                    [
                        'passenger_key' => 'dewi_maharani',
                        'seat_number' => '10A',
                        'boarding_status' => 'checked_in',
                        'issue_ticket' => true,
                    ],
                    [
                        'passenger_key' => 'aksa_prananda',
                        'seat_number' => '10B',
                        'boarding_status' => 'not_checked_in',
                        'issue_ticket' => true,
                    ],
                ],
                'booked_at' => Carbon::now()->subDays(2),
                'expires_at' => null,
            ],
            [
                'booking_code' => 'BK-ZNR-260402-003',
                'user_email' => 'rafi.pratama@cakrawala.com',
                'flight_number' => 'JT792',
                'status' => 'confirmed',
                'payment' => [
                    'payment_method' => 'bank_transfer_bni',
                    'payment_status' => 'paid',
                    'transaction_code' => 'TRX-JT792-5521',
                    'paid_at' => Carbon::now()->subDays(2)->setTime(14, 5),
                    'proof_file' => 'payments/demo/bk-znr-260402-003.jpg',
                ],
                'details' => [
                    [
                        'passenger_key' => 'rafi_saputra',
                        'seat_number' => '14C',
                        'boarding_status' => 'checked_in',
                        'issue_ticket' => true,
                    ],
                ],
                'booked_at' => Carbon::now()->subDays(3),
                'expires_at' => null,
            ],
            [
                'booking_code' => 'BK-ZNR-260402-004',
                'user_email' => 'intan.lestari@cakrawala.com',
                'flight_number' => 'GA411',
                'status' => 'completed',
                'payment' => [
                    'payment_method' => 'credit_card',
                    'payment_status' => 'paid',
                    'transaction_code' => 'TRX-GA411-7742',
                    'paid_at' => Carbon::now()->subDays(6)->setTime(19, 45),
                    'proof_file' => 'payments/demo/bk-znr-260402-004.jpg',
                ],
                'details' => [
                    [
                        'passenger_key' => 'intan_wibowo',
                        'seat_number' => '2A',
                        'boarding_status' => 'boarded',
                        'issue_ticket' => true,
                    ],
                ],
                'booked_at' => Carbon::now()->subDays(7),
                'expires_at' => null,
            ],
            [
                'booking_code' => 'BK-ZNR-260402-005',
                'user_email' => 'user@cakrawala.com',
                'flight_number' => 'JT793',
                'status' => 'completed',
                'payment' => [
                    'payment_method' => 'virtual_account_mandiri',
                    'payment_status' => 'paid',
                    'transaction_code' => 'TRX-JT793-8033',
                    'paid_at' => Carbon::now()->subDays(4)->setTime(21, 10),
                    'proof_file' => 'payments/demo/bk-znr-260402-005.jpg',
                ],
                'details' => [
                    [
                        'passenger_key' => 'aditya_pratama',
                        'seat_number' => '7A',
                        'boarding_status' => 'boarded',
                        'issue_ticket' => true,
                    ],
                    [
                        'passenger_key' => 'maya_lestari',
                        'seat_number' => '7B',
                        'boarding_status' => 'boarded',
                        'issue_ticket' => true,
                    ],
                ],
                'booked_at' => Carbon::now()->subDays(3),
                'expires_at' => null,
            ],
            [
                'booking_code' => 'BK-ZNR-260402-006',
                'user_email' => 'dewi.putri@cakrawala.com',
                'flight_number' => 'IP240',
                'status' => 'cancelled',
                'payment' => [
                    'payment_method' => 'bank_transfer_bri',
                    'payment_status' => 'refunded',
                    'transaction_code' => 'TRX-IP240-4408',
                    'paid_at' => Carbon::now()->subDay()->setTime(8, 15),
                    'proof_file' => 'payments/demo/bk-znr-260402-006.jpg',
                ],
                'details' => [
                    [
                        'passenger_key' => 'dewi_maharani',
                        'seat_number' => '12C',
                        'boarding_status' => 'not_checked_in',
                        'issue_ticket' => false,
                    ],
                ],
                'booked_at' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->subDays(3),
            ],
            [
                'booking_code' => 'BK-ZNR-260402-007',
                'user_email' => 'rafi.pratama@cakrawala.com',
                'flight_number' => 'IU745',
                'status' => 'pending',
                'payment' => [
                    'payment_method' => 'e_wallet_gopay',
                    'payment_status' => 'pending',
                    'transaction_code' => null,
                    'paid_at' => null,
                    'proof_file' => 'payments/demo/bk-znr-260402-007.jpg',
                ],
                'details' => [
                    [
                        'passenger_key' => 'rafi_saputra',
                        'seat_number' => '16D',
                        'boarding_status' => 'not_checked_in',
                        'issue_ticket' => false,
                    ],
                ],
                'booked_at' => Carbon::now()->subDays(2),
                'expires_at' => Carbon::now()->addHours(18),
            ],
            [
                'booking_code' => 'BK-ZNR-260402-008',
                'user_email' => 'intan.lestari@cakrawala.com',
                'flight_number' => 'QZ7530',
                'status' => 'confirmed',
                'payment' => [
                    'payment_method' => 'qris',
                    'payment_status' => 'paid',
                    'transaction_code' => 'TRX-QZ7530-9091',
                    'paid_at' => Carbon::now()->subDay()->setTime(16, 20),
                    'proof_file' => 'payments/demo/bk-znr-260402-008.jpg',
                ],
                'details' => [
                    [
                        'passenger_key' => 'intan_wibowo',
                        'seat_number' => '11A',
                        'boarding_status' => 'checked_in',
                        'issue_ticket' => true,
                    ],
                    [
                        'passenger_key' => 'bagas_wibowo',
                        'seat_number' => '11B',
                        'boarding_status' => 'not_checked_in',
                        'issue_ticket' => true,
                    ],
                ],
                'booked_at' => Carbon::now()->subDay(),
                'expires_at' => null,
            ],
            [
                'booking_code' => 'BK-ZNR-260402-009',
                'user_email' => 'rafi.pratama@cakrawala.com',
                'flight_number' => 'ID6581',
                'status' => 'cancelled',
                'payment' => [
                    'payment_method' => 'virtual_account_bni',
                    'payment_status' => 'failed',
                    'transaction_code' => null,
                    'paid_at' => null,
                    'proof_file' => 'payments/demo/bk-znr-260402-009.jpg',
                ],
                'details' => [
                    [
                        'passenger_key' => 'rafi_saputra',
                        'seat_number' => '6C',
                        'boarding_status' => 'not_checked_in',
                        'issue_ticket' => false,
                    ],
                ],
                'booked_at' => Carbon::now()->subHours(20),
                'expires_at' => Carbon::now()->subHours(2),
            ],
            [
                'booking_code' => 'BK-ZNR-260402-010',
                'user_email' => 'dewi.putri@cakrawala.com',
                'flight_number' => '8B555',
                'status' => 'confirmed',
                'payment' => [
                    'payment_method' => 'virtual_account_bca',
                    'payment_status' => 'paid',
                    'transaction_code' => 'TRX-8B555-6752',
                    'paid_at' => Carbon::now()->subHours(6),
                    'proof_file' => 'payments/demo/bk-znr-260402-010.jpg',
                ],
                'details' => [
                    [
                        'passenger_key' => 'dewi_maharani',
                        'seat_number' => '5D',
                        'boarding_status' => 'checked_in',
                        'issue_ticket' => true,
                    ],
                ],
                'booked_at' => Carbon::now()->subHours(8),
                'expires_at' => null,
            ],
        ];

        foreach ($bookings as $payload) {
            $user = $users[$payload['user_email']];
            $flight = $flights[$payload['flight_number']]->loadMissing('airplane.seats');
            $totalPrice = count($payload['details']) * (float) $flight->price;

            $booking = Booking::updateOrCreate(
                ['booking_code' => $payload['booking_code']],
                [
                    'user_id' => $user->id,
                    'flight_id' => $flight->id,
                    'total_passengers' => count($payload['details']),
                    'total_price' => $totalPrice,
                    'status' => $payload['status'],
                    'expired_at' => $payload['expires_at'],
                ]
            );

            $booking->payments()->delete();
            $booking->details()->delete();

            foreach ($payload['details'] as $index => $detailPayload) {
                $passenger = $passengers[$detailPayload['passenger_key']];
                $seat = $flight->airplane->seats->firstWhere('seat_number', $detailPayload['seat_number']);

                if (! $seat) {
                    continue;
                }

                $detail = BookingDetail::create([
                    'booking_id' => $booking->id,
                    'passenger_id' => $passenger->id,
                    'seat_id' => $seat->id,
                    'price' => $flight->price,
                    'ticket_number' => $detailPayload['issue_ticket']
                        ? $this->ticketNumber($payload['booking_code'], $index + 1)
                        : null,
                    'boarding_status' => $detailPayload['boarding_status'],
                ]);

                if ($detailPayload['issue_ticket']) {
                    $ticket = Ticket::create([
                        'booking_detail_id' => $detail->id,
                        'qr_code_path' => 'tickets/'.strtolower($payload['booking_code']).'/'.($index + 1).'.png',
                        'pdf_path' => 'tickets/'.strtolower($payload['booking_code']).'/'.($index + 1).'.pdf',
                        'issued_at' => $payload['payment']['paid_at'] ?? $payload['booked_at'],
                    ]);

                    Ticket::query()
                        ->whereKey($ticket->id)
                        ->update([
                            'created_at' => $payload['payment']['paid_at'] ?? $payload['booked_at'],
                            'updated_at' => $payload['payment']['paid_at'] ?? $payload['booked_at'],
                        ]);
                }

                BookingDetail::query()
                    ->whereKey($detail->id)
                    ->update([
                        'created_at' => $payload['booked_at'],
                        'updated_at' => $payload['payment']['paid_at'] ?? $payload['booked_at'],
                    ]);
            }

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => $payload['payment']['payment_method'],
                'amount' => $totalPrice,
                'payment_status' => $payload['payment']['payment_status'],
                'transaction_code' => $payload['payment']['transaction_code'],
                'paid_at' => $payload['payment']['paid_at'],
                'proof_file' => $payload['payment']['proof_file'],
            ]);

            Payment::query()
                ->whereKey($payment->id)
                ->update([
                    'created_at' => $payload['booked_at'],
                    'updated_at' => $payload['payment']['paid_at'] ?? $payload['booked_at'],
                ]);

            Booking::query()
                ->whereKey($booking->id)
                ->update([
                    'created_at' => $payload['booked_at'],
                    'updated_at' => $payload['payment']['paid_at'] ?? $payload['booked_at'],
                ]);
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

    protected function ticketNumber(string $bookingCode, int $sequence): string
    {
        return 'TK-'
            .preg_replace('/[^A-Z0-9]/', '', strtoupper($bookingCode))
            .'-'
            .str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
    }
}
