<?php

namespace App\Support;

final class BookingChangeRequestCatalog
{
    /**
     * @return array<string, array<string, string>>
     */
    public static function types(): array
    {
        return [
            'refund' => [
                'label' => 'Refund',
                'description' => 'Ajukan pengembalian dana untuk booking yang memenuhi syarat.',
            ],
            'reschedule' => [
                'label' => 'Reschedule',
                'description' => 'Ajukan perpindahan jadwal ke flight lain.',
            ],
            'name_correction' => [
                'label' => 'Name Correction',
                'description' => 'Perbaikan penulisan nama penumpang sesuai dokumen.',
            ],
            'cancel_request' => [
                'label' => 'Cancel Request',
                'description' => 'Permintaan pembatalan booking oleh customer.',
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return ['submitted', 'in_review', 'approved', 'rejected', 'completed'];
    }

    public static function isValidType(?string $type): bool
    {
        return is_string($type) && array_key_exists($type, self::types());
    }

    public static function label(?string $type): string
    {
        if (! self::isValidType($type)) {
            return ucfirst(str_replace('_', ' ', (string) $type));
        }

        return self::types()[$type]['label'];
    }
}
