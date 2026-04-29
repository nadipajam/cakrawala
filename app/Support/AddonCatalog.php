<?php

namespace App\Support;

final class AddonCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'baggage_10kg' => [
                'type' => 'baggage',
                'name' => 'Extra Baggage 10kg',
                'description' => 'Tambahan bagasi terdaftar 10kg.',
                'unit_price' => 120000,
                'max_qty' => 3,
                'scope' => 'passenger',
            ],
            'baggage_20kg' => [
                'type' => 'baggage',
                'name' => 'Extra Baggage 20kg',
                'description' => 'Tambahan bagasi terdaftar 20kg.',
                'unit_price' => 210000,
                'max_qty' => 2,
                'scope' => 'passenger',
            ],
            'priority_boarding' => [
                'type' => 'priority',
                'name' => 'Priority Boarding',
                'description' => 'Masuk pesawat lebih awal dan antrean prioritas.',
                'unit_price' => 85000,
                'max_qty' => 1,
                'scope' => 'passenger',
            ],
            'meal_hot' => [
                'type' => 'service',
                'name' => 'Hot Meal',
                'description' => 'Pilihan makanan hangat saat penerbangan.',
                'unit_price' => 75000,
                'max_qty' => 2,
                'scope' => 'passenger',
            ],
            'travel_insurance' => [
                'type' => 'insurance',
                'name' => 'Travel Insurance',
                'description' => 'Perlindungan kecelakaan dan pembatalan perjalanan.',
                'unit_price' => 55000,
                'max_qty' => 1,
                'scope' => 'booking',
            ],
            'fast_track' => [
                'type' => 'service',
                'name' => 'Fast Track Airport',
                'description' => 'Layanan jalur cepat di area keberangkatan.',
                'unit_price' => 95000,
                'max_qty' => 1,
                'scope' => 'passenger',
            ],
        ];
    }

    public static function isValid(?string $code): bool
    {
        return is_string($code) && array_key_exists($code, self::all());
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function find(?string $code): ?array
    {
        if (! self::isValid($code)) {
            return null;
        }

        return self::all()[$code];
    }
}
