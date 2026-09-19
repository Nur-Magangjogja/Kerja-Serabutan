<?php

namespace App\Enums;

use App\Models\Help;

enum HelpStatus: string
{
    case MENUNGGU_MITRA = 'menunggu_mitra';
    case TAKEN = 'taken';
    case PARTNER_ON_THE_WAY = 'partner_on_the_way';
    case PARTNER_ARRIVED = 'partner_arrived';
    case IN_PROGRESS = 'in_progress';
    case WAITING_CONFIRMATION = 'waiting_customer_confirmation';
    case SELESAI = 'selesai';
    case DIBATALKAN = 'dibatalkan';
    case PARTNER_CANCEL_REQUESTED = 'partner_cancel_requested';
    case CUSTOMER_CANCEL_REQUESTED = 'customer_cancel_requested';

    /**
     * Daftar status string yang aktif dikerjakan oleh mitra.
     *
     * @return array<string>
     */
    public static function activeStatuses(): array
    {
        return [
            self::TAKEN->value,
            self::PARTNER_ON_THE_WAY->value,
            self::PARTNER_ARRIVED->value,
            self::IN_PROGRESS->value,
            self::PARTNER_CANCEL_REQUESTED->value,
            self::CUSTOMER_CANCEL_REQUESTED->value,
        ];
    }

    /**
     * Daftar enum status yang aktif dikerjakan oleh mitra.
     *
     * @return array<self>
     */
    public static function activeStatusEnums(): array
    {
        return [
            self::TAKEN,
            self::PARTNER_ON_THE_WAY,
            self::PARTNER_ARRIVED,
            self::IN_PROGRESS,
            self::PARTNER_CANCEL_REQUESTED,
            self::CUSTOMER_CANCEL_REQUESTED,
        ];
    }

    /**
     * Daftar status string akhir (terminal).
     *
     * @return array<string>
     */
    public static function terminalStatuses(): array
    {
        return [
            self::SELESAI->value,
            self::DIBATALKAN->value,
        ];
    }

    /**
     * Daftar seluruh nilai status kanonik.
     *
     * @return array<string>
     */
    public static function canonicalStatuses(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Konversi atau normalisasi string status (termasuk legacy alias) ke Enum HelpStatus.
     */
    public static function tryFromOrNormalize(?string $status): ?self
    {
        if ($status === null || $status === '') {
            return null;
        }

        $canonical = Help::normalizeStatus($status);
        return self::tryFrom($canonical);
    }

    /**
     * Label representasi bahasa Indonesia yang baku.
     */
    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU_MITRA => 'Menunggu Mitra',
            self::TAKEN => 'Mitra Ditemukan',
            self::PARTNER_ON_THE_WAY => 'Mitra Menuju Lokasi',
            self::PARTNER_ARRIVED => 'Mitra Sampai di Lokasi',
            self::IN_PROGRESS => 'Sedang Dikerjakan',
            self::WAITING_CONFIRMATION => 'Menunggu Konfirmasi',
            self::SELESAI => 'Selesai',
            self::DIBATALKAN => 'Dibatalkan',
            self::PARTNER_CANCEL_REQUESTED => 'Pengajuan Batal Mitra',
            self::CUSTOMER_CANCEL_REQUESTED => 'Pengajuan Batal Pemesan',
        };
    }

    /**
     * Cek apakah status merupakan status aktif di lapangan.
     */
    public function isActive(): bool
    {
        return in_array($this, self::activeStatusEnums(), true);
    }

    /**
     * Cek apakah status sudah selesai atau dibatalkan.
     */
    public function isTerminal(): bool
    {
        return $this === self::SELESAI || $this === self::DIBATALKAN;
    }
}
