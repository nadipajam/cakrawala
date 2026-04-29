<?php

namespace App\Support;

final class UserRole
{
    public const ADMIN = 'admin';

    public const STAFF = 'staff';

    public const CUSTOMER = 'customer';

    public const MANAGER = 'manager';

    public const LEGACY_USER = 'user';

    /**
     * @return array<string, array<string, string>>
     */
    public static function all(): array
    {
        return [
            self::ADMIN => [
                'label' => 'Administrator',
                'group' => 'backoffice',
            ],
            self::STAFF => [
                'label' => 'Staff',
                'group' => 'backoffice',
            ],
            self::CUSTOMER => [
                'label' => 'Customer',
                'group' => 'customer',
            ],
            self::MANAGER => [
                'label' => 'Manager',
                'group' => 'backoffice',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return array<int, string>
     */
    public static function customerValues(): array
    {
        return [self::CUSTOMER, self::LEGACY_USER];
    }

    /**
     * @return array<int, string>
     */
    public static function backofficeValues(): array
    {
        return [self::ADMIN, self::STAFF, self::MANAGER];
    }

    public static function isValid(?string $role): bool
    {
        return is_string($role) && in_array($role, self::values(), true);
    }

    public static function normalize(?string $role): string
    {
        if ($role === self::LEGACY_USER) {
            return self::CUSTOMER;
        }

        return self::isValid($role) ? $role : self::CUSTOMER;
    }

    public static function label(?string $role): string
    {
        $role = self::normalize($role);

        return self::all()[$role]['label'];
    }
}
