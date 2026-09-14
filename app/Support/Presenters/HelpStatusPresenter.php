<?php

namespace App\Support\Presenters;

use App\Enums\HelpStatus;
use App\Models\Help;

class HelpStatusPresenter
{
    /**
     * Get complete presentation metadata for a Help record, HelpStatus enum, or raw status string.
     *
     * @param Help|HelpStatus|string|null $helpOrStatus
     * @return array{
     *     status: string,
     *     label: string,
     *     badge_class: string,
     *     icon_class: string,
     *     bg_color: string,
     *     text_color: string,
     *     border_color: string,
     *     is_active: bool,
     *     is_terminal: bool,
     *     step_number: int,
     *     description: string
     * }
     */
    public static function for(Help|HelpStatus|string|null $helpOrStatus): array
    {
        if ($helpOrStatus instanceof HelpStatus) {
            $enum = $helpOrStatus;
        } else {
            $statusStr = $helpOrStatus instanceof Help ? $helpOrStatus->status : $helpOrStatus;
            $enum = HelpStatus::tryFromOrNormalize($statusStr) ?? HelpStatus::MENUNGGU_MITRA;
        }

        return match ($enum) {
            HelpStatus::MENUNGGU_MITRA => [
                'status'       => $enum->value,
                'label'        => 'Menunggu Mitra',
                'badge_class'  => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200 dark:border-amber-800/50',
                'icon_class'   => 'heroicon-o-clock',
                'bg_color'     => 'amber',
                'text_color'   => 'text-amber-700 dark:text-amber-400',
                'border_color' => 'border-amber-300 dark:border-amber-700',
                'is_active'    => false,
                'is_terminal'  => false,
                'step_number'  => 1,
                'description'  => 'Pesanan sedang dipublikasikan ke mitra terdekat.',
            ],
            HelpStatus::TAKEN => [
                'status'       => $enum->value,
                'label'        => 'Mitra Ditemukan',
                'badge_class'  => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800/50',
                'icon_class'   => 'heroicon-o-user-check',
                'bg_color'     => 'blue',
                'text_color'   => 'text-blue-700 dark:text-blue-400',
                'border_color' => 'border-blue-300 dark:border-blue-700',
                'is_active'    => true,
                'is_terminal'  => false,
                'step_number'  => 2,
                'description'  => 'Mitra telah menerima order dan bersiap menuju lokasi.',
            ],
            HelpStatus::PARTNER_ON_THE_WAY => [
                'status'       => $enum->value,
                'label'        => 'Mitra Menuju Lokasi',
                'badge_class'  => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/50',
                'icon_class'   => 'heroicon-o-truck',
                'bg_color'     => 'indigo',
                'text_color'   => 'text-indigo-700 dark:text-indigo-400',
                'border_color' => 'border-indigo-300 dark:border-indigo-700',
                'is_active'    => true,
                'is_terminal'  => false,
                'step_number'  => 3,
                'description'  => 'Mitra sedang dalam perjalanan menuju titik lokasi.',
            ],
            HelpStatus::PARTNER_ARRIVED => [
                'status'       => $enum->value,
                'label'        => 'Mitra Sampai di Lokasi',
                'badge_class'  => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300 border-cyan-200 dark:border-cyan-800/50',
                'icon_class'   => 'heroicon-o-map-pin',
                'bg_color'     => 'cyan',
                'text_color'   => 'text-cyan-700 dark:text-cyan-400',
                'border_color' => 'border-cyan-300 dark:border-cyan-700',
                'is_active'    => true,
                'is_terminal'  => false,
                'step_number'  => 4,
                'description'  => 'Mitra telah tiba di lokasi penjemputan / pengerjaan.',
            ],
            HelpStatus::IN_PROGRESS => [
                'status'       => $enum->value,
                'label'        => 'Sedang Dikerjakan',
                'badge_class'  => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300 border-purple-200 dark:border-purple-800/50',
                'icon_class'   => 'heroicon-o-wrench',
                'bg_color'     => 'purple',
                'text_color'   => 'text-purple-700 dark:text-purple-400',
                'border_color' => 'border-purple-300 dark:border-purple-700',
                'is_active'    => true,
                'is_terminal'  => false,
                'step_number'  => 5,
                'description'  => 'Bantuan sedang dalam proses pelaksanaan oleh mitra.',
            ],
            HelpStatus::WAITING_CONFIRMATION => [
                'status'       => $enum->value,
                'label'        => 'Menunggu Konfirmasi',
                'badge_class'  => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300 border-orange-200 dark:border-orange-800/50',
                'icon_class'   => 'heroicon-o-check-circle',
                'bg_color'     => 'orange',
                'text_color'   => 'text-orange-700 dark:text-orange-400',
                'border_color' => 'border-orange-300 dark:border-orange-700',
                'is_active'    => false,
                'is_terminal'  => false,
                'step_number'  => 6,
                'description'  => 'Mitra telah menyelesaikan pekerjaan, menunggu persetujuan pemesan.',
            ],
            HelpStatus::SELESAI => [
                'status'       => $enum->value,
                'label'        => 'Selesai',
                'badge_class'  => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/50',
                'icon_class'   => 'heroicon-o-check-badge',
                'bg_color'     => 'emerald',
                'text_color'   => 'text-emerald-700 dark:text-emerald-400',
                'border_color' => 'border-emerald-300 dark:border-emerald-700',
                'is_active'    => false,
                'is_terminal'  => true,
                'step_number'  => 7,
                'description'  => 'Bantuan telah selesai dan pembayaran telah diteruskan ke mitra.',
            ],
            HelpStatus::DIBATALKAN => [
                'status'       => $enum->value,
                'label'        => 'Dibatalkan',
                'badge_class'  => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border-red-200 dark:border-red-800/50',
                'icon_class'   => 'heroicon-o-x-circle',
                'bg_color'     => 'red',
                'text_color'   => 'text-red-700 dark:text-red-400',
                'border_color' => 'border-red-300 dark:border-red-700',
                'is_active'    => false,
                'is_terminal'  => true,
                'step_number'  => 0,
                'description'  => 'Pesanan bantuan telah dibatalkan.',
            ],
            HelpStatus::PARTNER_CANCEL_REQUESTED => [
                'status'       => $enum->value,
                'label'        => 'Pengajuan Batal Mitra',
                'badge_class'  => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200 dark:border-rose-800/50',
                'icon_class'   => 'heroicon-o-exclamation-triangle',
                'bg_color'     => 'rose',
                'text_color'   => 'text-rose-700 dark:text-rose-400',
                'border_color' => 'border-rose-300 dark:border-rose-700',
                'is_active'    => true,
                'is_terminal'  => false,
                'step_number'  => 0,
                'description'  => 'Mitra mengajukan permohonan pembatalan bantuan.',
            ],
            HelpStatus::CUSTOMER_CANCEL_REQUESTED => [
                'status'       => $enum->value,
                'label'        => 'Pengajuan Batal Pemesan',
                'badge_class'  => 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-300 border-pink-200 dark:border-pink-800/50',
                'icon_class'   => 'heroicon-o-exclamation-circle',
                'bg_color'     => 'pink',
                'text_color'   => 'text-pink-700 dark:text-pink-400',
                'border_color' => 'border-pink-300 dark:border-pink-700',
                'is_active'    => true,
                'is_terminal'  => false,
                'step_number'  => 0,
                'description'  => 'Pemesan mengajukan permohonan pembatalan bantuan.',
            ],
        };
    }
}
