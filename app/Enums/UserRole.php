<?php

namespace App\Enums;

enum UserRole: string
{
    case CUSTOMER = 'customer';
    case MITRA = 'mitra';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super_admin';

    /**
     * Get all available role values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get a human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER => 'Customer',
            self::MITRA => 'Mitra',
            self::ADMIN => 'Admin Wilayah',
            self::SUPER_ADMIN => 'Super Admin',
        };
    }
}
