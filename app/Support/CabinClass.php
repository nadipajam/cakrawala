<?php

namespace App\Support;

final class CabinClass
{
    public const ORDER = ['first', 'business', 'economy'];

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'first' => [
                'label' => 'First Class',
                'short_label' => 'First',
                'description' => 'Suite-style seats with the widest personal space.',
                'multiplier' => 2.35,
                'layout' => ['A', 'D'],
            ],
            'business' => [
                'label' => 'Business Class',
                'short_label' => 'Business',
                'description' => 'Priority cabin with wider recliner seating.',
                'multiplier' => 1.6,
                'layout' => ['A', 'C', 'D', 'F'],
            ],
            'economy' => [
                'label' => 'Economy Class',
                'short_label' => 'Economy',
                'description' => 'Standard cabin with efficient 3-3 seating.',
                'multiplier' => 1.0,
                'layout' => ['A', 'B', 'C', 'D', 'E', 'F'],
            ],
        ];
    }

    public static function isValid(?string $class): bool
    {
        return is_string($class) && array_key_exists($class, self::all());
    }

    public static function normalize(?string $class, string $fallback = 'economy'): string
    {
        return self::isValid($class) ? $class : $fallback;
    }

    public static function label(?string $class): string
    {
        $class = self::normalize($class);

        return self::all()[$class]['label'];
    }

    public static function shortLabel(?string $class): string
    {
        $class = self::normalize($class);

        return self::all()[$class]['short_label'];
    }

    public static function description(?string $class): string
    {
        $class = self::normalize($class);

        return self::all()[$class]['description'];
    }

    public static function multiplier(?string $class): float
    {
        $class = self::normalize($class);

        return (float) self::all()[$class]['multiplier'];
    }

    /**
     * @return array<int, string>
     */
    public static function layout(?string $class): array
    {
        $class = self::normalize($class);

        return self::all()[$class]['layout'];
    }

    public static function price(float $basePrice, ?string $class): float
    {
        return round($basePrice * self::multiplier($class), 2);
    }
}
