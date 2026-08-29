<?php

namespace App\Services;

use App\Models\PartnerOnlineState;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PartnerOnlineService
{
    public const DEFAULT_HEARTBEAT_TTL = 60; // 60 detik

    /**
     * Dapatkan atau inisialisasi state online mitra.
     */
    public function getOrCreateState(int $userId): PartnerOnlineState
    {
        $state = PartnerOnlineState::firstOrCreate(
            ['user_id' => $userId],
            [
                'matching_status' => PartnerOnlineState::STATUS_OFFLINE,
                'last_seen_at'    => null,
                'searching_since' => null,
            ]
        );

        // Self-Healing Guard: Jika status BUSY tapi tidak memiliki active task di DB, pulihkan ke ONLINE/SEARCHING
        if ($state->matching_status === PartnerOnlineState::STATUS_BUSY) {
            $hasActiveTask = \App\Models\Help::where('mitra_id', $userId)->active()->exists();
            if (!$hasActiveTask) {
                $this->releaseBusy($userId, $state->current_help_id ?? 0);
                $state->refresh();
            }
        }

        return $state;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // TIER 1: MITRA ACTIONS (User-driven)
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Mitra mengaktifkan mode Online (Standby).
     * Transisi yang valid: OFFLINE -> ONLINE.
     */
    public function goOnline(User $mitra, ?float $lat = null, ?float $lng = null): bool
    {
        return DB::transaction(function () use ($mitra, $lat, $lng) {
            $state = PartnerOnlineState::where('user_id', $mitra->id)->lockForUpdate()->first();

            if (!$state) {
                $state = PartnerOnlineState::create([
                    'user_id'         => $mitra->id,
                    'matching_status' => PartnerOnlineState::STATUS_OFFLINE,
                ]);
            }

            // Jika status BUSY tapi tidak ada active task, pulihkan otomatis
            if ($state->matching_status === PartnerOnlineState::STATUS_BUSY) {
                $hasActiveTask = \App\Models\Help::where('mitra_id', $mitra->id)->active()->exists();
                if (!$hasActiveTask) {
                    $state->matching_status = PartnerOnlineState::STATUS_ONLINE;
                    $state->current_help_id = null;
                } else {
                    throw new \RuntimeException('Tidak dapat mengubah status saat sedang mengerjakan bantuan.');
                }
            } elseif ($state->matching_status === PartnerOnlineState::STATUS_OFFER_PENDING) {
                throw new \RuntimeException('Tidak dapat mengubah status saat sedang menerima tawaran.');
            }

            $state->matching_status = PartnerOnlineState::STATUS_ONLINE;
            $state->last_seen_at    = now();
            $state->searching_since = null;

            if ($lat !== null && $lng !== null) {
                $state->latitude  = $lat;
                $state->longitude = $lng;
            }

            $state->save();

            Log::info("[PartnerOnlineService] Mitra #{$mitra->id} is now ONLINE (Standby).");
            return true;
        });
    }

    /**
     * Mitra mengaktifkan mode Mencari Order (Searching).
     * Transisi yang valid: ONLINE -> SEARCHING (atau OFFLINE -> SEARCHING via auto-promote).
     */
    public function startSearching(User $mitra, ?float $lat = null, ?float $lng = null): bool
    {
        return DB::transaction(function () use ($mitra, $lat, $lng) {
            $state = PartnerOnlineState::where('user_id', $mitra->id)->lockForUpdate()->first();

            if (!$state) {
                $state = PartnerOnlineState::create([
                    'user_id'         => $mitra->id,
                    'matching_status' => PartnerOnlineState::STATUS_OFFLINE,
                ]);
            }

            // Jika status BUSY tapi tidak ada active task, pulihkan otomatis
            if ($state->matching_status === PartnerOnlineState::STATUS_BUSY) {
                $hasActiveTask = \App\Models\Help::where('mitra_id', $mitra->id)->active()->exists();
                if (!$hasActiveTask) {
                    $state->matching_status = PartnerOnlineState::STATUS_ONLINE;
                    $state->current_help_id = null;
                } else {
                    throw new \RuntimeException('Tidak dapat mencari order baru saat sedang mengerjakan bantuan.');
                }
            } elseif ($state->matching_status === PartnerOnlineState::STATUS_OFFER_PENDING) {
                throw new \RuntimeException('Tidak dapat mencari order baru saat sedang menerima tawaran.');
            }

            $state->matching_status      = PartnerOnlineState::STATUS_SEARCHING;
            $state->searching_since      = now();
            $state->last_seen_at         = now();
            $state->consecutive_declines = 0;

            if ($lat !== null && $lng !== null) {
                $state->latitude  = $lat;
                $state->longitude = $lng;
            }

            $state->save();

            Log::info("[PartnerOnlineService] Mitra #{$mitra->id} started SEARCHING for orders.");
            return true;
        });
    }

    /**
     * Mitra menghentikan pencarian order (kembali ke Online Standby).
     * Transisi yang valid: SEARCHING -> ONLINE.
     */
    public function stopSearching(User $mitra): bool
    {
        return DB::transaction(function () use ($mitra) {
            $state = PartnerOnlineState::where('user_id', $mitra->id)->lockForUpdate()->first();

            if (!$state) {
                return true;
            }

            if (in_array($state->matching_status, [PartnerOnlineState::STATUS_OFFER_PENDING, PartnerOnlineState::STATUS_BUSY])) {
                throw new \RuntimeException('Tidak dapat menghentikan pencarian saat sedang ada tawaran aktif atau pekerjaan berjalan.');
            }

            if ($state->matching_status === PartnerOnlineState::STATUS_SEARCHING) {
                $state->matching_status      = PartnerOnlineState::STATUS_ONLINE;
                $state->searching_since      = null;
                $state->last_seen_at         = now();
                $state->consecutive_declines = 0;
                $state->save();
            }

            Log::info("[PartnerOnlineService] Mitra #{$mitra->id} stopped searching (ONLINE Standby).");
            return true;
        });
    }

    /**
     * Mitra beralih ke status Offline.
     * Guard: DILARANG offline jika status sedang 'offer_pending' atau 'busy'.
     */
    public function goOffline(User $mitra): bool
    {
        return DB::transaction(function () use ($mitra) {
            $state = PartnerOnlineState::where('user_id', $mitra->id)->lockForUpdate()->first();

            if (!$state) {
                return true;
            }

            if (in_array($state->matching_status, [PartnerOnlineState::STATUS_OFFER_PENDING, PartnerOnlineState::STATUS_BUSY])) {
                throw new \RuntimeException('Tidak dapat offline saat sedang menerima tawaran atau mengerjakan bantuan.');
            }

            $state->matching_status      = PartnerOnlineState::STATUS_OFFLINE;
            $state->searching_since      = null;
            $state->consecutive_declines = 0;
            $state->save();

            Log::info("[PartnerOnlineService] Mitra #{$mitra->id} is now OFFLINE.");
            return true;
        });
    }

    /**
     * Heartbeat periodik dari aplikasi mitra.
     * Mengupdate last_seen_at dan koordinat GPS.
     * Guard: TIDAK PERNAH menghidupkan mitra yang OFFLINE menjadi online secara otomatis.
     */
    public function heartbeat(User $mitra, ?float $lat = null, ?float $lng = null): void
    {
        $state = PartnerOnlineState::where('user_id', $mitra->id)->first();

        if (!$state) {
            return;
        }

        // Jangan revive jika offline
        if ($state->matching_status === PartnerOnlineState::STATUS_OFFLINE) {
            if ($lat !== null && $lng !== null) {
                $state->update([
                    'latitude'  => $lat,
                    'longitude' => $lng,
                ]);
            }
            return;
        }

        $updates = ['last_seen_at' => now()];

        if ($lat !== null && $lng !== null) {
            $updates['latitude']  = $lat;
            $updates['longitude'] = $lng;
        }

        $state->update($updates);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // TIER 2: SYSTEM / MATCHING ENGINE LOCKED TRANSITIONS
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Matching Engine menetapkan tawaran aktif ke mitra (Atomic Lock).
     * Transisi: SEARCHING -> OFFER_PENDING.
     * Syarat: Status wajib 'searching' DAN heartbeat masih segar.
     */
    public function setOfferPending(int $mitraId, int $helpId, ?int $heartbeatTtlSeconds = null): bool
    {
        $ttl = $heartbeatTtlSeconds ?? \App\Models\AppSetting::getHeartbeatTtlSeconds();

        return DB::transaction(function () use ($mitraId, $helpId, $ttl) {
            $state = PartnerOnlineState::where('user_id', $mitraId)->lockForUpdate()->first();

            if (!$state) {
                return false;
            }

            if ($state->matching_status !== PartnerOnlineState::STATUS_SEARCHING) {
                Log::warning("[PartnerOnlineService] Cannot set offer_pending: Mitra #{$mitraId} status is '{$state->matching_status}'");
                return false;
            }

            if (!$state->isHeartbeatFresh($ttl)) {
                Log::warning("[PartnerOnlineService] Cannot set offer_pending: Mitra #{$mitraId} heartbeat is stale.");
                return false;
            }

            $state->matching_status = PartnerOnlineState::STATUS_OFFER_PENDING;
            $state->current_help_id = $helpId;
            $state->save();

            Log::info("[PartnerOnlineService] Mitra #{$mitraId} state -> OFFER_PENDING for Help #{$helpId}.");
            return true;
        });
    }

    /**
     * Mengembalikan status mitra jika penawaran ditolak / timeout (Atomic Lock).
     * Anti-Abuse: Jika menolak/mengabaikan >= 2 kali berturut-turut, demosi ke ONLINE (Standby).
     */
    public function revertFromOfferPending(int $mitraId, int $helpId, ?int $heartbeatTtlSeconds = null): void
    {
        $ttl = $heartbeatTtlSeconds ?? \App\Models\AppSetting::getHeartbeatTtlSeconds();

        DB::transaction(function () use ($mitraId, $helpId, $ttl) {
            $state = PartnerOnlineState::where('user_id', $mitraId)->lockForUpdate()->first();

            if (!$state || $state->matching_status !== PartnerOnlineState::STATUS_OFFER_PENDING || $state->current_help_id != $helpId) {
                return;
            }

            $state->current_help_id = null;

            // Anti-Abuse: Hitung akumulasi penolakan / pengabaian tawaran berturut-turut
            $maxDeclines = \App\Models\AppSetting::getMaxConsecutiveDeclines();
            $newDeclines = ($state->consecutive_declines ?? 0) + 1;

            if ($newDeclines >= $maxDeclines) {
                // Demosi ke Standby (ONLINE) agar mitra harus menekan tombol "Cari Order" kembali secara sadar
                $state->matching_status      = PartnerOnlineState::STATUS_ONLINE;
                $state->searching_since      = null;
                $state->consecutive_declines = 0;
                Log::info("[PartnerOnlineService] Mitra #{$mitraId} reached {$newDeclines} consecutive declines -> demoted to ONLINE Standby.");
            } else {
                $state->consecutive_declines = $newDeclines;
                if ($state->isHeartbeatFresh($ttl)) {
                    $state->matching_status = PartnerOnlineState::STATUS_SEARCHING;
                } else {
                    $state->matching_status = PartnerOnlineState::STATUS_ONLINE;
                    $state->searching_since = null;
                }
            }

            $state->save();
            Log::info("[PartnerOnlineService] Mitra #{$mitraId} reverted from OFFER_PENDING -> '{$state->matching_status}' (Declines: {$newDeclines}/{$maxDeclines}).");
        });
    }

    /**
     * Menetapkan mitra ke status BUSY saat penawaran diterima atau bantuan diambil (Atomic Lock).
     * Transisi: OFFER_PENDING / SEARCHING -> BUSY.
     */
    public function setBusy(int $mitraId, int $helpId): bool
    {
        return DB::transaction(function () use ($mitraId, $helpId) {
            $state = PartnerOnlineState::where('user_id', $mitraId)->lockForUpdate()->first();

            if (!$state) {
                $state = PartnerOnlineState::create([
                    'user_id' => $mitraId,
                ]);
            }

            $state->matching_status      = PartnerOnlineState::STATUS_BUSY;
            $state->current_help_id      = $helpId;
            $state->searching_since      = null;
            $state->consecutive_declines = 0;
            $state->save();

            Log::info("[PartnerOnlineService] Mitra #{$mitraId} state -> BUSY for Help #{$helpId}.");
            return true;
        });
    }

    /**
     * Melepaskan status BUSY setelah bantuan selesai / dibatalkan (Atomic Lock).
     * Transisi: BUSY -> SEARCHING (jika heartbeat segar) atau ONLINE.
     */
    public function releaseBusy(int $mitraId, int $helpId, int $heartbeatTtlSeconds = self::DEFAULT_HEARTBEAT_TTL): void
    {
        DB::transaction(function () use ($mitraId, $helpId, $heartbeatTtlSeconds) {
            $state = PartnerOnlineState::where('user_id', $mitraId)->lockForUpdate()->first();

            if (!$state) {
                return;
            }

            // Hanya lepas jika memang sedang mengerjakan help terkait atau status busy
            if ($state->matching_status === PartnerOnlineState::STATUS_BUSY || $state->current_help_id == $helpId) {
                $state->current_help_id   = null;
                $state->last_completed_at = now();

                if ($state->isHeartbeatFresh($heartbeatTtlSeconds)) {
                    $state->matching_status = PartnerOnlineState::STATUS_SEARCHING;
                    $state->searching_since = now();
                } else {
                    $state->matching_status = PartnerOnlineState::STATUS_ONLINE;
                    $state->searching_since = null;
                }

                $state->save();
                Log::info("[PartnerOnlineService] Mitra #{$mitraId} released from BUSY -> '{$state->matching_status}'.");
            }
        });
    }

    // ═════════════════════════════════════════════════════════════════════════
    // HOUSEKEEPING & CRON
    // ═════════════════════════════════════════════════════════════════════════

    /**
     * Housekeeping untuk mendemotasi mitra 'searching' yang heartbeat-nya mati (stale) menjadi 'online'.
     * Guard: TIDAK PERNAH menyentuh mitra berstatus 'busy' atau 'offline'.
     */
    public function cleanupStaleStates(?int $heartbeatTtlSeconds = null): int
    {
        $ttl    = $heartbeatTtlSeconds ?? \App\Models\AppSetting::getHeartbeatTtlSeconds();
        $cutoff = now()->subSeconds($ttl);

        return DB::transaction(function () use ($cutoff) {
            $staleStates = PartnerOnlineState::where('matching_status', PartnerOnlineState::STATUS_SEARCHING)
                ->where(function ($q) use ($cutoff) {
                    $q->whereNull('last_seen_at')
                      ->orWhere('last_seen_at', '<', $cutoff);
                })
                ->lockForUpdate()
                ->get();

            $demotedCount = 0;
            foreach ($staleStates as $state) {
                $state->matching_status = PartnerOnlineState::STATUS_ONLINE;
                $state->searching_since = null;
                $state->save();
                $demotedCount++;

                Log::info("[PartnerOnlineService] Demoted stale searching mitra #{$state->user_id} to ONLINE.");
            }

            return $demotedCount;
        });
    }
}
