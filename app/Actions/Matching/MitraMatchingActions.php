<?php

namespace App\Actions\Matching;

use App\Models\Help;
use App\Models\PartnerOnlineState;
use App\Models\User;
use App\Services\HelpMatchingService;
use App\Services\PartnerOnlineService;
use Illuminate\Support\Facades\Log;

class MitraMatchingActions
{
    public function __construct(
        protected PartnerOnlineService $onlineService,
        protected HelpMatchingService $matchingService
    ) {}

    /**
     * Set partner online (Standby).
     *
     * @return array{success: bool, message: string, flash_type: string}
     */
    public function goOnline(User $user, ?float $latitude = null, ?float $longitude = null): array
    {
        if ($this->isRestricted($user)) {
            return [
                'success'    => false,
                'message'    => 'Akun Anda sedang dalam pembatasan fitur (Shadow Ban / SP 3) dan tidak diizinkan untuk online.',
                'flash_type' => 'error',
            ];
        }

        try {
            $this->onlineService->goOnline($user, $latitude, $longitude);
            return [
                'success'    => true,
                'message'    => 'Status Anda sekarang ONLINE (Standby).',
                'flash_type' => 'info',
            ];
        } catch (\RuntimeException $e) {
            return [
                'success'    => false,
                'message'    => $e->getMessage(),
                'flash_type' => 'error',
            ];
        } catch (\Throwable $e) {
            Log::error('[MitraMatchingActions] goOnline error: ' . $e->getMessage(), ['user_id' => $user->id]);
            return [
                'success'    => false,
                'message'    => 'Gagal memperbarui status online.',
                'flash_type' => 'error',
            ];
        }
    }

    /**
     * Start searching for matching orders.
     *
     * @return array{success: bool, message: string, flash_type: string}
     */
    public function startSearching(User $user, ?float $latitude = null, ?float $longitude = null): array
    {
        if ($this->isRestricted($user)) {
            return [
                'success'    => false,
                'message'    => 'Akun Anda sedang dalam pembatasan fitur (Shadow Ban / SP 3) dan tidak diizinkan mencari order bantuan.',
                'flash_type' => 'error',
            ];
        }

        try {
            $this->onlineService->startSearching($user, $latitude, $longitude);
            return [
                'success'    => true,
                'message'    => 'Mode pencarian aktif! Radar mencari order di sekitar Anda.',
                'flash_type' => 'info',
            ];
        } catch (\RuntimeException $e) {
            return [
                'success'    => false,
                'message'    => $e->getMessage(),
                'flash_type' => 'error',
            ];
        } catch (\Throwable $e) {
            Log::error('[MitraMatchingActions] startSearching error: ' . $e->getMessage(), ['user_id' => $user->id]);
            return [
                'success'    => false,
                'message'    => 'Gagal memulai pencarian order.',
                'flash_type' => 'error',
            ];
        }
    }

    /**
     * Stop searching and revert to Online (Standby).
     *
     * @return array{success: bool, message: string, flash_type: string}
     */
    public function stopSearching(User $user): array
    {
        try {
            $this->onlineService->stopSearching($user);
            return [
                'success'    => true,
                'message'    => 'Pencarian dihentikan. Status kembali ke Online (Standby).',
                'flash_type' => 'info',
            ];
        } catch (\RuntimeException $e) {
            return [
                'success'    => false,
                'message'    => $e->getMessage(),
                'flash_type' => 'error',
            ];
        } catch (\Throwable $e) {
            Log::error('[MitraMatchingActions] stopSearching error: ' . $e->getMessage(), ['user_id' => $user->id]);
            return [
                'success'    => false,
                'message'    => 'Gagal menghentikan pencarian.',
                'flash_type' => 'error',
            ];
        }
    }

    /**
     * Go offline.
     *
     * @return array{success: bool, message: string, flash_type: string}
     */
    public function goOffline(User $user): array
    {
        try {
            $this->onlineService->goOffline($user);
            return [
                'success'    => true,
                'message'    => 'Status Anda sekarang OFFLINE.',
                'flash_type' => 'info',
            ];
        } catch (\RuntimeException $e) {
            return [
                'success'    => false,
                'message'    => $e->getMessage(),
                'flash_type' => 'error',
            ];
        } catch (\Throwable $e) {
            Log::error('[MitraMatchingActions] goOffline error: ' . $e->getMessage(), ['user_id' => $user->id]);
            return [
                'success'    => false,
                'message'    => 'Gagal mengubah status ke offline.',
                'flash_type' => 'error',
            ];
        }
    }

    /**
     * Process GPS heartbeat.
     */
    public function heartbeat(User $user, ?float $latitude = null, ?float $longitude = null): void
    {
        $this->onlineService->heartbeat($user, $latitude, $longitude);
    }

    /**
     * Accept a dispatched offer.
     *
     * @return array{success: bool, help: ?Help, message: string, flash_type: string}
     */
    public function acceptOffer(int $dispatchId, User $user): array
    {
        try {
            $help = $this->matchingService->acceptOffer($dispatchId, $user);
            return [
                'success'    => true,
                'help'       => $help,
                'message'    => "Tawaran pekerjaan '{$help->title}' berhasil diterima! Segera menuju lokasi customer.",
                'flash_type' => 'success',
            ];
        } catch (\RuntimeException $e) {
            return [
                'success'    => false,
                'help'       => null,
                'message'    => $e->getMessage(),
                'flash_type' => 'error',
            ];
        } catch (\Throwable $e) {
            Log::error('[MitraMatchingActions] acceptOffer error: ' . $e->getMessage(), ['dispatch_id' => $dispatchId]);
            return [
                'success'    => false,
                'help'       => null,
                'message'    => 'Gagal menerima tawaran pekerjaan.',
                'flash_type' => 'error',
            ];
        }
    }

    /**
     * Reject a dispatched offer.
     *
     * @return array{success: bool, is_demoted_to_standby: bool, message: string, flash_type: string}
     */
    public function rejectOffer(int $dispatchId, User $user, ?string $reason = 'Mitra menolak tawaran'): array
    {
        try {
            $this->matchingService->rejectOffer($dispatchId, $user, $reason);
            $state = $this->onlineService->getOrCreateState($user->id);
            $isStandby = $state->matching_status === PartnerOnlineState::STATUS_ONLINE;

            $message = $isStandby
                ? 'Tawaran dilewati. Batas penolakan berturut-turut tercapai, status dialihkan ke Standby. Klik "Cari Order" saat Anda siap menerima pesanan.'
                : 'Tawaran dilewati. Status tetap AKTIF mencari order baru di sekitar Anda.';

            return [
                'success'                => true,
                'is_demoted_to_standby' => $isStandby,
                'message'                => $message,
                'flash_type'             => 'info',
            ];
        } catch (\Throwable $e) {
            Log::error('[MitraMatchingActions] rejectOffer error: ' . $e->getMessage(), ['dispatch_id' => $dispatchId]);
            return [
                'success'                => false,
                'is_demoted_to_standby' => false,
                'message'                => 'Gagal menolak tawaran.',
                'flash_type'             => 'error',
            ];
        }
    }

    /**
     * Handle expiry of offer.
     *
     * @return array{success: bool, is_demoted_to_standby: bool, message: string}
     */
    public function handleExpiry(int $dispatchId, User $user): array
    {
        try {
            $this->matchingService->handleExpiry($dispatchId, true);
            $state = $this->onlineService->getOrCreateState($user->id);
            $isStandby = $state->matching_status === PartnerOnlineState::STATUS_ONLINE;

            $message = $isStandby
                ? 'Waktu tawaran habis. Status dialihkan ke Standby. Klik "Cari Order" untuk mulai matching lagi.'
                : 'Waktu tawaran habis. Sistem melanjutkan pencarian order baru untuk Anda.';

            return [
                'success'                => true,
                'is_demoted_to_standby' => $isStandby,
                'message'                => $message,
            ];
        } catch (\Throwable $e) {
            Log::error('[MitraMatchingActions] handleExpiry error: ' . $e->getMessage(), ['dispatch_id' => $dispatchId]);
            return [
                'success'                => false,
                'is_demoted_to_standby' => false,
                'message'                => 'Gagal memproses waktu habis.',
            ];
        }
    }

    /**
     * Check if user is restricted from matching.
     */
    public function isRestricted(User $user): bool
    {
        return $user->isShadowBanned() || $user->warning_level >= 3 || $user->status === 'blocked';
    }
}
