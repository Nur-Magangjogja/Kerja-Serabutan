<?php

namespace App\Enums;

enum ChatSenderType: string
{
    case CUSTOMER = 'customer';
    case MITRA = 'mitra';
    case ADMIN = 'admin';
    case SUPER_ADMIN = 'super_admin';
    case SYSTEM = 'system';

    /**
     * Get all available sender types.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Human-readable label for sender.
     */
    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER => 'Customer',
            self::MITRA => 'Mitra',
            self::ADMIN => 'Admin Wilayah',
            self::SUPER_ADMIN => 'Super Admin',
            self::SYSTEM => 'Sistem SayaBantu',
        };
    }
}
