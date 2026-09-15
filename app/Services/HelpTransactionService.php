<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\BalanceTransaction;
use App\Models\Help;
use App\Models\HelpDispatch;
use App\Models\PartnerOnlineState;
use App\Models\PartnerReport;
use App\Models\User;
use App\Models\UserBalance;
use App\Notifications\HelpStatusNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * HelpTransactionService
 *
 * Memusatkan alur orkestrasi transaksi bantuan jasa.
 * Mengoordinasikan HelpEscrowService, HelpChatService, HelpNotificationService,
 * HelpMatchingService, dan PartnerOnlineService.
 * Setiap aksi berjalan dalam DB::transaction dengan pessimistic locking (lockForUpdate).
 */
class HelpTransactionService
{
    protected HelpEscrowService $escrowService;
    protected HelpChatService $chatService;
    protected HelpNotificationService $notificationService;
    protected PartnerOnlineService $onlineService;
    protected HelpMatchingService $matchingService;
    protected HelpTrackingService $trackingService;

    public function __construct(
        HelpEscrowService $escrowService,
        HelpChatService $chatService,
        HelpNotificationService $notificationService,
        PartnerOnlineService $onlineService,
        HelpMatchingService $matchingService,
        HelpTrackingService $trackingService
    ) {
        $this->escrowService       = $escrowService;
        $this->chatService         = $chatService;
        $this->notificationService = $notificationService;
        $this->onlineService       = $onlineService;
        $this->matchingService     = $matchingService;
        $this->trackingService     = $trackingService;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MITRA ACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Memvalidasi kelayakan akun Mitra dan kriteria order secara menyeluruh di level Backend Service (Source of Truth).
     */
    public function assertMitraEligibleToTakeHelp(User $mitra, Help $help, ?float $lat = null, ?float $lng = null): void
    {
        // 1. Validasi Peran Akun
        if ($mitra->role !== 'mitra') {
            throw new \RuntimeException('Hanya pengguna dengan peran Rekan Jasa (Mitra) yang diizinkan mengambil pekerjaan bantuan.');
        }

        // 2. Validasi Status Akun Aktif
        if ($mitra->status !== 'active') {
            throw new \RuntimeException('Akun Anda saat ini tidak aktif (' . ($mitra->status ?? 'non-aktif') . '). Harap hubungi administrator.');
        }

        // 3. Validasi Verifikasi Akun / Email
        if (!$mitra->verified && !$mitra->hasVerifiedEmail()) {
            throw new \RuntimeException('Akun Anda belum terverifikasi untuk mengambil pekerjaan bantuan.');
        }

        // 4. Validasi Pembatasan Akun (Shadow Banned)
        if ($mitra->isShadowBanned()) {
            throw new \RuntimeException('Akun Anda sedang dalam pembatasan fitur (moderasi) dan tidak diizinkan mengambil tugas bantuan.');
        }

        // 5. Validasi Sanksi Disiplin (SP 3 / Pembekuan)
        if ($mitra->warning_level >= 3) {
            throw new \RuntimeException('Akun Anda sedang dalam masa penangguhan (SP 3) akibat pelanggaran kepatuhan.');
        }

        // 5b. Validasi Ketersediaan Bantuan (Belum diambil mitra lain)
        if ($help->mitra_id !== null || $help->status !== Help::STATUS_MENUNGGU_MITRA) {
            throw new \RuntimeException('Bantuan ini sudah diambil oleh Rekan Jasa lain atau tidak tersedia lagi.');
        }

        // 5c. Validasi Expiry Bantuan
        if ($help->isExpired()) {
            app(HelpCancellationService::class)->autoCancelExpiredHelp($help, 'Batas waktu pencarian rekan jasa telah habis.');
            throw new \RuntimeException('Batas waktu pencarian untuk bantuan ini telah habis.');
        }

        // 5d. Validasi Waktu Publikasi / Masuk Pool
        if (!$help->isPublished()) {
            throw new \RuntimeException('Bantuan ini belum dibuka untuk umum di pool.');
        }

        // 6. Validasi Dispatch Mode (Harus Pool untuk pengambilan mandiri)
        if ($help->dispatch_mode && $help->dispatch_mode !== Help::DISPATCH_MODE_POOL) {
            throw new \RuntimeException('Pesanan ini sedang dalam penawaran sequential khusus dan belum dibuka untuk pool umum.');
        }

        // 7. Validasi Riwayat Pembatalan
        if ($help->hasCancelledBy($mitra->id)) {
            throw new \RuntimeException('Anda tidak dapat mengambil bantuan ini karena sebelumnya telah Anda batalkan.');
        }

        // 8. Validasi Jarak Operasional Baku (Maksimal 10.0 KM untuk pesanan instan)
        $maxRadiusKm = (float) AppSetting::MAX_OPERATIONAL_RADIUS_KM;
        $mitraLat = $lat ?? ($mitra->latitude ? (float) $mitra->latitude : null);
        $mitraLng = $lng ?? ($mitra->longitude ? (float) $mitra->longitude : null);

        // Tentukan koordinat titik awal sesuai jenis layanan
        $targetLat = null;
        $targetLng = null;

        if ($help->isPickup()) {
            $targetLat = (float) ($help->pickup_latitude ?: $help->latitude);
            $targetLng = (float) ($help->pickup_longitude ?: $help->longitude);
        } else {
            $targetLat = (float) ($help->latitude ?: 0);
            $targetLng = (float) ($help->longitude ?: 0);
        }

        // Pesanan berjadwal di masa depan (> 1 jam dari sekarang) fleksibel dari lokasi saat ini
        $isFutureScheduled = $help->scheduled_at && \Carbon\Carbon::parse($help->scheduled_at)->isFuture() && \Carbon\Carbon::parse($help->scheduled_at)->diffInMinutes(now()) > 60;

        if (!$isFutureScheduled && $mitraLat && $mitraLng && $targetLat && $targetLng) {
            $distMeters = $this->trackingService->calculateDistance(
                (float) $mitraLat, (float) $mitraLng,
                (float) $targetLat, (float) $targetLng
            );
            $distKm = $distMeters / 1000;
            if ($distKm > $maxRadiusKm) {
                throw new \RuntimeException("Lokasi titik awal bantuan ini berjarak " . round($distKm, 1) . " km dari posisi Anda saat ini, melebihi batas jangkauan operasional maksimal platform ({$maxRadiusKm} km).");
            }
        }
    }

    /**
     * Mitra mengambil bantuan dari pool.
     */
    public function takeHelp(Help $help, User $mitra, ?float $lat = null, ?float $lng = null): void
    {
        // 1. Guard Komprehensif Backend (Single Source of Truth)
        $this->assertMitraEligibleToTakeHelp($mitra, $help, $lat, $lng);

        // 2. Transaksi Atomik dengan Canonical Lock Hierarchy (Mencegah Deadlock & Race Condition)
        // Urutan Kunci Global: Tier 1 (Help) -> Tier 2 (HelpDispatch) -> Tier 3 (PartnerOnlineState)
        $advances = [];

        DB::transaction(function () use ($help, $mitra, $lat, $lng, &$advances) {
            // STEP 1 (Tier 1): Lock baris Help yang ingin diambil
            $lockedHelp = Help::where('id', $help->id)
                ->lockForUpdate()
                ->first();

            if (!$lockedHelp || $lockedHelp->mitra_id !== null || $lockedHelp->status !== Help::STATUS_MENUNGGU_MITRA) {
                throw new \RuntimeException('Bantuan ini sudah diambil oleh Rekan Jasa lain atau tidak tersedia lagi.');
            }

            if ($lockedHelp->isExpired()) {
                throw new \RuntimeException('Batas waktu pencarian untuk bantuan ini telah habis.');
            }

            if ($lockedHelp->dispatch_mode && $lockedHelp->dispatch_mode !== Help::DISPATCH_MODE_POOL) {
                throw new \RuntimeException('Pesanan ini sedang dalam penawaran sequential khusus dan belum dibuka untuk pool umum.');
            }

            if ($lockedHelp->hasCancelledBy($mitra->id)) {
                throw new \RuntimeException('Anda tidak dapat mengambil bantuan ini karena sebelumnya telah Anda batalkan.');
            }

            // STEP 2 (Tier 2): Lock & selesaikan HelpDispatch aktif untuk mitra ini (jika ada pending offer)
            $staleDispatches = HelpDispatch::where('mitra_id', $mitra->id)
                ->where('status', HelpDispatch::STATUS_OFFERED)
                ->lockForUpdate()
                ->get();

            foreach ($staleDispatches as $stale) {
                $stale->update([
                    'status'           => HelpDispatch::STATUS_REJECTED,
                    'responded_at'     => now(),
                    'rejection_reason' => 'Mitra mengambil pekerjaan lain dari open pool',
                ]);
                $advances[] = [
                    'help_id' => $stale->help_id,
                    'round'   => $stale->round,
                    'rank'    => $stale->rank + 1,
                ];
            }

            // STEP 3 (Tier 3): Lock baris PartnerOnlineState mitra
            $partnerState = PartnerOnlineState::where('user_id', $mitra->id)
                ->lockForUpdate()
                ->first();

            if (!$partnerState) {
                $partnerState = PartnerOnlineState::create([
                    'user_id'         => $mitra->id,
                    'matching_status' => PartnerOnlineState::STATUS_SEARCHING,
                ]);
            }

            // Validasi di dalam lock: Mitra tidak boleh BUSY
            if ($partnerState->matching_status === PartnerOnlineState::STATUS_BUSY) {
                throw new \RuntimeException('Anda masih memiliki tugas bantuan aktif yang sedang berjalan. Harap selesaikan tugas tersebut terlebih dahulu sebelum mengambil tugas baru.');
            }

            // Lock & Verifikasi apakah mitra memiliki tugas aktif lain di tabel helps
            $hasActiveHelp = Help::where('mitra_id', $mitra->id)
                ->where('id', '!=', $lockedHelp->id)
                ->active()
                ->lockForUpdate()
                ->exists();

            if ($hasActiveHelp) {
                throw new \RuntimeException('Anda masih memiliki tugas bantuan aktif yang sedang berjalan. Harap selesaikan tugas tersebut terlebih dahulu sebelum mengambil tugas baru.');
            }

            // STEP 4: Mutasi Bersama (Assign Help + Ubah PartnerOnlineState ke BUSY)
            $lockedHelp->update([
                'mitra_id'      => $mitra->id,
                'status'        => Help::STATUS_TAKEN,
                'dispatch_mode' => Help::DISPATCH_MODE_ASSIGNED,
                'taken_at'      => now(),
            ]);

            $partnerState->update([
                'matching_status'      => PartnerOnlineState::STATUS_BUSY,
                'current_help_id'      => $lockedHelp->id,
                'searching_since'      => null,
                'consecutive_declines' => 0,
            ]);

            // Set koordinat awal mitra
            if ($lat && $lng) {
                $lockedHelp->update([
                    'partner_initial_lat' => $lat,
                    'partner_initial_lng' => $lng,
                    'partner_current_lat' => $lat,
                    'partner_current_lng' => $lng,
                ]);
            } elseif ($mitra->latitude && $mitra->longitude) {
                $lockedHelp->update([
                    'partner_initial_lat' => (float) $mitra->latitude,
                    'partner_initial_lng' => (float) $mitra->longitude,
                    'partner_current_lat' => (float) $mitra->latitude,
                    'partner_current_lng' => (float) $mitra->longitude,
                ]);
            }

            $help->refresh();
        });

        // Advance sequential matching untuk order yang ditinggalkan di luar transaksi penguncian
        foreach ($advances as $adv) {
            $this->matchingService->dispatchNextCandidate($adv['help_id'], $adv['round'], $adv['rank']);
        }

        // 5. Otomatis batalkan penawaran pop-up aktif lain yang sedang menggantung dan teruskan ke kandidat berikutnya
        $pendingDispatches = HelpDispatch::where('mitra_id', $mitra->id)
            ->where('status', HelpDispatch::STATUS_OFFERED)
            ->get();

        foreach ($pendingDispatches as $pDispatch) {
            if ($pDispatch->help_id !== $help->id) {
                $this->matchingService->rejectOffer(
                    $pDispatch->id,
                    $mitra,
                    'Mitra telah mengambil bantuan lain dari pool umum'
                );
            }
        }

        // 6. Notifikasi, Pesan Chat Sambutan, & Audit Log
        $this->notificationService->notifyHelpTaken($help, $mitra);
        $this->chatService->sendWelcomeChat($help, $mitra);
        $this->notificationService->logActivity(
            $mitra->id,
            $help->id,
            'take_help',
            "Mitra {$mitra->name} mengambil bantuan ('{$help->title}')"
        );

        Log::info('[HelpTransactionService] takeHelp success', ['help_id' => $help->id, 'mitra_id' => $mitra->id]);
    }

    /**
     * Mitra menyatakan mulai berangkat menuju lokasi.
     */
    public function markOnTheWay(Help $help, User $mitra): void
    {
        $this->assertMitraAssigned($help, $mitra);
        $this->assertCanTransition($help, Help::STATUS_PARTNER_ON_THE_WAY);

        if ($help->isScheduled() && !$help->canPartnerStartDeparture()) {
            $openTime = $help->departure_window_opens_at?->format('H:i') ?? '1 jam sebelum jadwal';
            $targetTime = $help->getScheduledTargetTime()?->format('H:i') ?? '-';
            throw new \RuntimeException("Tugas ini dijadwalkan untuk pukul {$targetTime}. Tombol keberangkatan baru dapat diaktifkan mulai pukul {$openTime} ({$help->departure_lead_minutes} menit sebelum jadwal).");
        }

        DB::transaction(function () use ($help) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();
            $lockedHelp->update([
                'status'             => Help::STATUS_PARTNER_ON_THE_WAY,
                'partner_started_at' => $lockedHelp->partner_started_at ?? now(),
            ]);
        });

        $this->notificationService->sendStatusNotification($help, Help::STATUS_PARTNER_ON_THE_WAY, $help->user, $mitra);
        $this->notificationService->logActivity(
            $mitra->id,
            $help->id,
            'partner_on_the_way',
            "Mitra {$mitra->name} berangkat menuju lokasi bantuan"
        );
    }

    /**
     * Mitra menyatakan sudah tiba di lokasi.
     */
    public function markArrived(Help $help, User $mitra): void
    {
        $this->assertMitraAssigned($help, $mitra);
        $this->assertCanTransition($help, Help::STATUS_PARTNER_ARRIVED);

        DB::transaction(function () use ($help) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();
            $lockedHelp->update([
                'status'             => Help::STATUS_PARTNER_ARRIVED,
                'partner_arrived_at' => $lockedHelp->partner_arrived_at ?? now(),
            ]);
        });

        $this->notificationService->sendStatusNotification($help, Help::STATUS_PARTNER_ARRIVED, $help->user, $mitra);
        $this->notificationService->logActivity(
            $mitra->id,
            $help->id,
            'partner_arrived',
            "Mitra {$mitra->name} tiba di lokasi bantuan"
        );
    }

    /**
     * Mitra mulai mengerjakan pekerjaan.
     */
    public function startService(Help $help, User $mitra): void
    {
        $this->assertMitraAssigned($help, $mitra);
        $this->assertCanTransition($help, Help::STATUS_IN_PROGRESS);

        DB::transaction(function () use ($help) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();
            $lockedHelp->update([
                'status'             => Help::STATUS_IN_PROGRESS,
                'service_started_at' => $lockedHelp->service_started_at ?? now(),
            ]);
        });

        $this->notificationService->sendStatusNotification($help, Help::STATUS_IN_PROGRESS, $help->user, $mitra);
        $this->chatService->sendServiceStartedChat($help, $mitra);
        $this->notificationService->logActivity(
            $mitra->id,
            $help->id,
            'help_started',
            "Mitra {$mitra->name} mulai mengerjakan bantuan"
        );
    }

    /**
     * Mitra menyelesaikan pekerjaan dan mengunggah foto bukti.
     * Status beralih ke waiting_customer_confirmation dengan window konfirmasi 24 jam.
     * Dana escrow TETAP DITAHAN (held) sampai dikonfirmasi atau auto-confirm.
     */
    public function submitCompletion(Help $help, User $mitra, $proofPhoto, ?string $notes = null): void
    {
        $this->assertMitraAssigned($help, $mitra);
        $this->assertCanTransition($help, Help::STATUS_WAITING_CONFIRMATION);

        $path = $proofPhoto instanceof UploadedFile
            ? $proofPhoto->store('helps/proofs', 'public')
            : $proofPhoto;

        DB::transaction(function () use ($help, $path, $notes, $mitra) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();
            $now = now();
            $data = [
                'status'                   => Help::STATUS_WAITING_CONFIRMATION,
                'escrow_status'            => Help::ESCROW_STATUS_HELD,
                'dispatch_mode'            => Help::DISPATCH_MODE_ASSIGNED,
                'proof_photo'              => $path,
                'completion_notes'         => $notes,
                'service_completed_at'     => $lockedHelp->service_completed_at ?? $now,
                'confirmation_deadline_at' => $now->copy()->addHours(24),
            ];
            $lockedHelp->update($data);

            // Lepaskan status BUSY mitra agar mitra dapat langsung mencari / mengambil tugas bantuan baru
            $this->onlineService->releaseBusy($mitra->id, $lockedHelp->id);
        });

        $help->refresh();

        // Kirim pesan chat dengan foto bukti ke customer
        $this->chatService->sendCompletionChat($help, $mitra, $path, $notes);
        $this->notificationService->sendStatusNotification($help, Help::STATUS_WAITING_CONFIRMATION, $help->user, $mitra);
        $this->notificationService->logActivity(
            $mitra->id,
            $help->id,
            'help_completed_waiting_confirmation',
            "Mitra {$mitra->name} menyelesaikan bantuan dan mengunggah foto bukti (Menunggu Konfirmasi Customer / 24 Jam)",
            $path
        );
    }

    /**
     * @deprecated Gunakan HelpCancellationService::submitPartnerCancelRequest() secara langsung.
     */
    public function requestPartnerCancel(Help $help, User $mitra, ?string $reason = null): void
    {
        app(HelpCancellationService::class)->submitPartnerCancelRequest($help, $mitra, $reason ?? 'Pembatalan oleh Rekan Jasa');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CUSTOMER ACTIONS & ESCROW RELEASE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Customer mengkonfirmasi bahwa pekerjaan selesai.
     * Melepaskan dana escrow ke saldo mitra (atomic & idempotent).
     */
    public function customerConfirmCompletion(Help $help, User $customer): void
    {
        $this->assertCustomerOwns($help, $customer);
        $this->assertCanTransition($help, Help::STATUS_SELESAI);

        DB::transaction(function () use ($help) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if ($lockedHelp->status === Help::STATUS_SELESAI) {
                throw new \RuntimeException('Pesanan bantuan ini sudah diselesaikan sebelumnya.');
            }

            if ($lockedHelp->escrow_status === Help::ESCROW_STATUS_DISPUTED_FREEZE) {
                throw new \RuntimeException('Pesanan ini sedang dalam proses sengketa/mediasi.');
            }

            // Kreditkan pembayaran ke saldo mitra (atomic release via HelpEscrowService)
            $this->escrowService->releaseEscrowToMitra($lockedHelp, 'customer_confirm');
        });

        $help->refresh();

        // Kirim pesan chat penutup dari Customer ke Mitra
        $this->chatService->sendConfirmationChat($help, $customer, $help->mitra);
        $this->notificationService->sendStatusNotification($help, Help::STATUS_SELESAI, $help->mitra, $customer);
        $this->notificationService->logActivity(
            $customer->id,
            $help->id,
            'help_confirmed',
            "Customer {$customer->name} mengonfirmasi bantuan telah selesai (Dana diteruskan ke Mitra)",
            $help->proof_photo
        );

        Log::info('[HelpTransactionService] customerConfirmCompletion success', ['help_id' => $help->id]);
    }

    /**
     * Auto-confirm pesanan yang telah melewati batas waktu 24 jam tanpa konfirmasi manual/dispute.
     * Re-evaluasi kondisi secara ketat di dalam lock.
     */
    public function autoConfirmExpiredConfirmation(Help $help): bool
    {
        $executed = false;

        DB::transaction(function () use ($help, &$executed) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if (!$lockedHelp) {
                return;
            }

            // Re-evaluasi kondisi di dalam lock
            if (
                $lockedHelp->escrow_status !== Help::ESCROW_STATUS_HELD ||
                $lockedHelp->status !== Help::STATUS_WAITING_CONFIRMATION ||
                $lockedHelp->disputed_at !== null ||
                !$lockedHelp->confirmation_deadline_at ||
                $lockedHelp->confirmation_deadline_at->isFuture()
            ) {
                return;
            }

            $this->escrowService->releaseEscrowToMitra($lockedHelp, 'auto_confirm');
            $executed = true;
        });

        if ($executed) {
            $help->refresh();
            $this->notificationService->sendStatusNotification($help, Help::STATUS_SELESAI, $help->mitra, null);
            $this->notificationService->logActivity(
                $help->user_id,
                $help->id,
                'help_auto_confirmed',
                "Pesanan dikonfirmasi selesai otomatis oleh sistem setelah batas waktu 24 jam berakhir."
            );
            Log::info('[HelpTransactionService] autoConfirmExpiredConfirmation success', ['help_id' => $help->id]);
        }

        return $executed;
    }

    /**
     * Customer mengajukan sengketa / komplain atas pekerjaan mitra.
     * Membekukan dana escrow (disputed_freeze) dan mencatat laporan PartnerReport.
     */
    public function raiseDispute(Help $help, User $customer, string $reason): PartnerReport
    {
        $this->assertCustomerOwns($help, $customer);

        $report = DB::transaction(function () use ($help, $customer, $reason) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->dispute_resolved_at !== null) {
                throw new \RuntimeException('Sengketa untuk pesanan ini telah diputuskan oleh Admin secara final dan tidak dapat diajukan kembali.');
            }

            if ($lockedHelp->escrow_status !== Help::ESCROW_STATUS_HELD) {
                throw new \RuntimeException('Dana bantuan tidak berada dalam status holding (telah dicairkan atau telah dibatalkan). Sengketa pembekuan tidak dapat diajukan.');
            }

            if ($lockedHelp->status !== Help::STATUS_WAITING_CONFIRMATION) {
                throw new \RuntimeException('Sengketa hanya dapat diajukan saat pesanan menunggu konfirmasi penyelesaian.');
            }

            if ($lockedHelp->disputed_at !== null || $lockedHelp->escrow_status === Help::ESCROW_STATUS_DISPUTED_FREEZE) {
                throw new \RuntimeException('Sengketa untuk pesanan ini sudah diajukan sebelumnya.');
            }

            $lockedHelp->update([
                'escrow_status'  => Help::ESCROW_STATUS_DISPUTED_FREEZE,
                'disputed_at'    => now(),
                'dispute_reason' => $reason,
            ]);

            $refundAmt = (float) ($lockedHelp->total_amount > 0 ? $lockedHelp->total_amount : $lockedHelp->amount);

            $partnerReport = PartnerReport::create([
                'reporter_id'      => $customer->id,
                'reported_id'      => $lockedHelp->mitra_id,
                'reported_help_id' => $lockedHelp->id,
                'report_type'      => 'dispute',
                'title'            => "Sengketa Bantuan #{$lockedHelp->id}: {$lockedHelp->title}",
                'message'          => $reason,
                'refund_amount'    => $refundAmt,
                'refund_status'    => 'requested',
                'status'           => 'pending',
            ]);

            return $partnerReport;
        });

        $help->refresh();

        // Notifikasi ke Mitra
        if ($help->mitra) {
            try {
                $help->mitra->notify(new HelpStatusNotification(
                    $help,
                    Help::STATUS_WAITING_CONFIRMATION,
                    'disputed',
                    $customer
                ));
            } catch (\Throwable $e) {
                Log::warning('[HelpTransactionService] Failed to notify mitra of dispute: ' . $e->getMessage());
            }
        }

        $this->notificationService->logActivity(
            $customer->id,
            $help->id,
            'dispute_raised',
            "Customer {$customer->name} mengajukan sengketa/komplain. Dana escrow dibekukan (Freeze)."
        );

        return $report;
    }

    /**
     * Customer mengajukan klaim garansi 1x24 jam pasca-konfirmasi pada bantuan yang telah selesai.
     * Menarik kembali (clawback) earning mitra ke dalam escrow holding (disputed_freeze) dan mencatat PartnerReport.
     */
    public function claimWarrantyAndClawbackEscrow(
        Help $help,
        User $customer,
        string $reason,
        ?string $evidencePath = null,
        string $reportType = 'klaim_refund_pekerjaan_fiktif'
    ): PartnerReport {
        $this->assertCustomerOwns($help, $customer);

        $report = DB::transaction(function () use ($help, $customer, $reason, $evidencePath, $reportType) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->dispute_resolved_at !== null) {
                throw new \RuntimeException('Sengketa untuk pesanan ini telah diputuskan oleh Admin secara final dan tidak dapat diajukan kembali.');
            }

            // Pastikan garansi 1x24 jam belum kadaluarsa jika pesanan telah selesai
            if ($lockedHelp->status === Help::STATUS_SELESAI) {
                if (!$lockedHelp->completed_at || \Carbon\Carbon::parse($lockedHelp->completed_at)->addHours(24)->isPast()) {
                    throw new \RuntimeException('Masa garansi asuransi 1x24 jam untuk pesanan ini telah berakhir.');
                }
            }

            // Cek jika sudah pernah direfund
            $alreadyRefunded = BalanceTransaction::where('user_id', $customer->id)
                ->where('reference_id', $lockedHelp->id)
                ->where('type', 'refund')
                ->exists();
            if ($alreadyRefunded) {
                throw new \RuntimeException('Dana bantuan ini sudah pernah dikembalikan (refund) sebelumnya.');
            }

            // 1. Jika dana sudah pernah dicairkan ke Mitra (escrow released), tarik kembali (clawback) dana earning mitra ke escrow holding
            if ($lockedHelp->escrow_status === Help::ESCROW_STATUS_RELEASED && $lockedHelp->mitra_id) {
                $netEarning = (float) $lockedHelp->getNetEarning();
                $mitraBalance = UserBalance::firstOrCreate(
                    ['user_id' => $lockedHelp->mitra_id],
                    ['balance' => 0]
                );

                $mitraBalance->decrement('balance', $netEarning);

                BalanceTransaction::create([
                    'idempotency_key' => "help:{$lockedHelp->id}:escrow_clawback:{$lockedHelp->mitra_id}:" . now()->timestamp,
                    'user_id'         => $lockedHelp->mitra_id,
                    'amount'          => $netEarning,
                    'direction'       => 'debit',
                    'type'            => 'deduction',
                    'description'     => "Penahanan Kembali Dana Bantuan '{$lockedHelp->title}' untuk Mediasi Klaim Garansi 1x24 Jam",
                    'reference_id'    => $lockedHelp->id,
                    'reference_type'  => 'help',
                    'order_id'        => $lockedHelp->order_id,
                    'status'          => 'completed',
                ]);
            }

            // 2. Ubah status pesanan menjadi disputed freeze & waiting confirmation
            $lockedHelp->update([
                'status'         => Help::STATUS_WAITING_CONFIRMATION,
                'escrow_status'  => Help::ESCROW_STATUS_DISPUTED_FREEZE,
                'disputed_at'    => now(),
                'dispute_reason' => $reason,
            ]);

            $refundAmt = (float) ($lockedHelp->total_amount > 0 ? $lockedHelp->total_amount : $lockedHelp->amount);

            // 3. Buat PartnerReport
            $partnerReport = PartnerReport::create([
                'reporter_id'        => $customer->id,
                'reported_user_id'   => $lockedHelp->mitra_id,
                'reported_help_id'   => $lockedHelp->id,
                'reported_help_text' => $lockedHelp->title,
                'reported_user_text' => $lockedHelp->mitra?->name,
                'title'              => "Klaim Garansi 1x24 Jam: Bantuan #{$lockedHelp->id} - {$lockedHelp->title}",
                'message'            => $reason,
                'evidence_photo'     => $evidencePath,
                'report_type'        => $reportType,
                'category'           => 'dari_customer',
                'status'             => 'pending',
                'refund_status'      => 'requested',
                'refund_amount'      => $refundAmt,
            ]);

            return $partnerReport;
        });

        $help->refresh();

        // Notifikasi ke Mitra
        if ($help->mitra) {
            try {
                $help->mitra->notify(new HelpStatusNotification(
                    $help,
                    Help::STATUS_WAITING_CONFIRMATION,
                    'disputed',
                    $customer
                ));
            } catch (\Throwable $e) {
                Log::warning('[HelpTransactionService] Failed to notify mitra of warranty claim: ' . $e->getMessage());
            }
        }

        $this->notificationService->logActivity(
            $customer->id,
            $help->id,
            'warranty_claim_escrow_clawback',
            "Customer {$customer->name} mengajukan klaim garansi 1x24 jam. Dana earning mitra ditarik kembali ke Escrow Holding untuk mediasi Admin."
        );

        return $report;
    }

    /**
     * Admin menyelesaikan sengketa dengan Full Release, Full Refund, atau Partial Split.
     */
    public function resolveDispute(Help $help, User $admin, string $resolutionType, array $splitData = []): void
    {
        DB::transaction(function () use ($help, $admin, $resolutionType, $splitData) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->firstOrFail();

            if ($lockedHelp->escrow_status !== Help::ESCROW_STATUS_DISPUTED_FREEZE) {
                throw new \RuntimeException('Pesanan ini tidak berada dalam status pembekuan sengketa (disputed_freeze).');
            }

            $grossAmount = (float) ($lockedHelp->total_amount > 0 ? $lockedHelp->total_amount : $lockedHelp->amount);

            if ($resolutionType === 'full_release') {
                $this->escrowService->releaseEscrowToMitra($lockedHelp, 'admin_dispute_release');
                $lockedHelp->update([
                    'dispute_resolved_at' => now(),
                    'dispute_resolved_by' => $admin->id,
                ]);
            } elseif ($resolutionType === 'full_refund') {
                $customer = $lockedHelp->user ?? User::find($lockedHelp->user_id);
                if (!$customer) {
                    throw new \RuntimeException('Customer tidak ditemukan.');
                }

                $customerBalance = UserBalance::firstOrCreate(
                    ['user_id' => $customer->id],
                    ['balance' => 0]
                );

                $customerBalance->refundToCustomer(
                    $grossAmount,
                    $lockedHelp->id,
                    $lockedHelp->order_id,
                    "Refund 100% Sengketa Bantuan '{$lockedHelp->title}'",
                    "help:{$lockedHelp->id}:refund:{$customer->id}"
                );

                $lockedHelp->update([
                    'status'              => Help::STATUS_DIBATALKAN,
                    'escrow_status'       => Help::ESCROW_STATUS_REFUNDED,
                    'payment_status'      => Help::PAYMENT_STATUS_REFUNDED,
                    'dispatch_mode'       => Help::DISPATCH_MODE_CLOSED,
                    'dispute_resolved_at' => now(),
                    'dispute_resolved_by' => $admin->id,
                ]);
            } elseif ($resolutionType === 'partial_split') {
                $partnerAmount  = (float) ($splitData['partner_amount'] ?? 0);
                $platformFee    = (float) ($splitData['platform_fee'] ?? 0);
                $customerRefund = (float) ($splitData['customer_refund'] ?? 0);

                if (abs(($partnerAmount + $platformFee + $customerRefund) - $grossAmount) > 0.01) {
                    throw new \RuntimeException("Total partial split (Rp " . number_format($partnerAmount + $platformFee + $customerRefund, 0) . ") tidak sama dengan nilai gross (Rp " . number_format($grossAmount, 0) . ").");
                }

                // 1. Credit Mitra
                if ($partnerAmount > 0 && $lockedHelp->mitra_id) {
                    $mitraBalance = UserBalance::firstOrCreate(
                        ['user_id' => $lockedHelp->mitra_id],
                        ['balance' => 0]
                    );
                    $mitraBalance->receiveEarning(
                        $partnerAmount,
                        $lockedHelp->id,
                        "Pendapatan Parsial Penyelesaian Sengketa '{$lockedHelp->title}'",
                        $lockedHelp->order_id,
                        "help:{$lockedHelp->id}:dispute_earning:{$lockedHelp->mitra_id}"
                    );
                }

                // 2. Refund Customer
                if ($customerRefund > 0) {
                    $customer = $lockedHelp->user ?? User::find($lockedHelp->user_id);
                    if ($customer) {
                        $custBal = UserBalance::firstOrCreate(
                            ['user_id' => $customer->id],
                            ['balance' => 0]
                        );
                        $custBal->refundToCustomer(
                            $customerRefund,
                            $lockedHelp->id,
                            $lockedHelp->order_id,
                            "Refund Parsial Penyelesaian Sengketa '{$lockedHelp->title}'",
                            "help:{$lockedHelp->id}:dispute_refund:{$customer->id}"
                        );
                    }
                }

                // 3. Platform Fee
                if ($platformFee > 0) {
                    BalanceTransaction::create([
                        'idempotency_key' => "help:{$lockedHelp->id}:dispute_fee",
                        'user_id'         => null,
                        'amount'          => $platformFee,
                        'direction'       => 'credit',
                        'type'            => 'platform_fee',
                        'description'     => "Biaya Platform Penyelesaian Sengketa Bantuan '{$lockedHelp->title}'",
                        'reference_id'    => $lockedHelp->id,
                        'reference_type'  => 'help',
                        'order_id'        => $lockedHelp->order_id,
                        'status'          => 'completed',
                    ]);
                }

                $lockedHelp->update([
                    'status'              => Help::STATUS_SELESAI,
                    'escrow_status'       => Help::ESCROW_STATUS_PARTIAL_REFUND,
                    'payment_status'      => Help::PAYMENT_STATUS_PARTIALLY_REFUNDED,
                    'dispatch_mode'       => Help::DISPATCH_MODE_CLOSED,
                    'rating_status'       => Help::RATING_STATUS_PENDING,
                    'dispute_resolved_at' => now(),
                    'dispute_resolved_by' => $admin->id,
                ]);
            } else {
                throw new \InvalidArgumentException("Tipe resolusi sengketa '{$resolutionType}' tidak valid.");
            }

            // Update status PartnerReport jika ada
            PartnerReport::where('reported_help_id', $lockedHelp->id)
                ->where('status', 'pending')
                ->update([
                    'status'              => 'resolved',
                    'resolved_at'         => now(),
                    'resolved_by'         => $admin->id,
                    'refund_status'       => $resolutionType === 'full_release' ? 'rejected' : 'approved',
                    'refund_processed_at' => now(),
                    'refund_processed_by' => $admin->id,
                ]);

            // Lepaskan status BUSY mitra jika ada
            if ($lockedHelp->mitra_id) {
                $this->onlineService->releaseBusy($lockedHelp->mitra_id, $lockedHelp->id);
            }
        });

        $help->refresh();
        $this->notificationService->logActivity(
            $admin->id,
            $help->id,
            'dispute_resolved',
            "Admin {$admin->name} menyelesaikan sengketa dengan resolusi: {$resolutionType}"
        );
        Log::info('[HelpTransactionService] resolveDispute success', ['help_id' => $help->id, 'resolution' => $resolutionType]);
    }

    /**
     * Customer membatalkan bantuan sebelum mitra ditemukan.
     * Model v2: refund escrow 100% ke customer.
     */
    public function customerCancelHelp(Help $help, User $customer): void
    {
        $this->assertCustomerOwns($help, $customer);
        $this->assertCanTransition($help, Help::STATUS_DIBATALKAN);

        DB::transaction(function () use ($help, $customer) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if ($lockedHelp->status !== Help::STATUS_MENUNGGU_MITRA) {
                throw new \RuntimeException('Bantuan ini tidak dapat dibatalkan secara sepihak karena sudah diambil oleh Rekan Jasa.');
            }

            $lockedHelp->update([
                'status'         => Help::STATUS_DIBATALKAN,
                'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
                'escrow_status'  => Help::ESCROW_STATUS_REFUNDED,
                'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
            ]);

            // Kembalikan escrow ke customer (refund 100%)
            if ($lockedHelp->amount > 0) {
                $this->escrowService->refundFromEscrow($lockedHelp, $customer);
            }
        });

        $this->notificationService->logActivity(
            $customer->id,
            $help->id,
            'help_cancelled',
            "Customer {$customer->name} membatalkan bantuan (Refund dana Rp " . number_format($help->amount, 0, ',', '.') . " ke saldo customer)"
        );

        // Bebaskan mitra jika saat ini order sedang ditawarkan ke mitra (offer_pending)
        try {
            $offeredDispatches = HelpDispatch::with('mitra')
                ->where('help_id', $help->id)
                ->where('status', HelpDispatch::STATUS_OFFERED)
                ->get();

            foreach ($offeredDispatches as $dispatch) {
                $dispatch->update([
                    'status'           => HelpDispatch::STATUS_CANCELLED,
                    'responded_at'     => now(),
                    'rejection_reason' => 'Permintaan bantuan dibatalkan oleh pemesan saat proses pencarian.',
                ]);

                $this->onlineService->releaseCancelledOffer($dispatch->mitra_id, $help->id);

                if ($dispatch->mitra) {
                    $dispatch->mitra->notify(new HelpStatusNotification($help, null, 'customer_cancelled_during_matching', null));
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[HelpTransactionService] Failed to release offered dispatches on cancel: ' . $e->getMessage());
        }
    }

    /**
     * @deprecated Gunakan HelpCancellationService::checkAndAutoCancelExpiredRequests() / auto-cancel.
     */
    public function autoCancelExpiredHelp(Help $help, string $reason = 'Batas waktu pencarian Rekan Jasa telah berakhir'): void
    {
        DB::transaction(function () use ($help, $reason) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if (!$lockedHelp || $lockedHelp->status !== Help::STATUS_MENUNGGU_MITRA || $lockedHelp->mitra_id !== null) {
                return;
            }

            $lockedHelp->update([
                'status'         => Help::STATUS_DIBATALKAN,
                'dispatch_mode'  => Help::DISPATCH_MODE_CLOSED,
                'escrow_status'  => Help::ESCROW_STATUS_REFUNDED,
                'payment_status' => Help::PAYMENT_STATUS_REFUNDED,
                'admin_notes'    => "Dibatalkan otomatis oleh sistem. Alasan: {$reason}",
            ]);

            // Kembalikan dana escrow 100% ke customer
            $customer = $lockedHelp->user;
            if ($customer && $lockedHelp->amount > 0) {
                $this->escrowService->refundFromEscrow($lockedHelp, $customer);
            }
        });
    }

    /**
     * @deprecated Gunakan HelpCancellationService::customerAcceptPartnerCancellation() secara langsung.
     */
    public function customerAcceptCancel(Help $help, User $customer): void
    {
        app(HelpCancellationService::class)->customerAcceptPartnerCancellation($help, $customer);
    }

    /**
     * @deprecated Gunakan HelpCancellationService secara langsung.
     */
    public function customerRejectCancel(Help $help, User $customer): void
    {
        $this->assertCustomerOwns($help, $customer);

        if ($help->status !== Help::STATUS_PARTNER_CANCEL_REQUESTED) {
            throw new \RuntimeException('Tidak ada permintaan pembatalan aktif dari Rekan Jasa.');
        }

        $prevStatus = $help->partner_cancel_prev_status ?: Help::STATUS_TAKEN;

        DB::transaction(function () use ($help, $prevStatus) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if ($lockedHelp->status !== Help::STATUS_PARTNER_CANCEL_REQUESTED) {
                throw new \RuntimeException('Status pesanan telah berubah.');
            }

            $lockedHelp->update([
                'status'                      => $prevStatus,
                'partner_cancel_prev_status'  => null,
                'partner_cancel_reason'       => null,
                'partner_cancel_requested_at' => null,
            ]);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ESCROW DELEGATES
    // ─────────────────────────────────────────────────────────────────────────

    public function refundFromEscrowDirect(Help $help, User $customer, float $refundAmount, string $note = 'Pengembalian Dana'): void
    {
        $this->escrowService->refundFromEscrowDirect($help, $customer, $refundAmount, $note);
    }

    public function payoutPartialFromEscrowDirect(Help $help, User $mitra, float $payoutAmount, string $note = 'Kompensasi'): void
    {
        $this->escrowService->payoutPartialFromEscrowDirect($help, $mitra, $payoutAmount, $note);
    }

    public function refundFromEscrow(Help $help, User $customer): void
    {
        $this->escrowService->refundFromEscrow($help, $customer);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE GUARDS
    // ─────────────────────────────────────────────────────────────────────────

    private function assertMitraAssigned(Help $help, User $mitra): void
    {
        if ($help->mitra_id !== $mitra->id) {
            throw new \RuntimeException('Anda tidak memiliki izin untuk aksi pada bantuan ini.');
        }
    }

    private function assertCustomerOwns(Help $help, User $customer): void
    {
        if ($help->user_id !== $customer->id) {
            throw new \RuntimeException('Anda tidak memiliki izin untuk aksi pada bantuan ini.');
        }
    }

    private function assertCanTransition(Help $help, string $toStatus): void
    {
        if (!$help->canTransitionTo($toStatus)) {
            throw new \RuntimeException(
                "Transisi status dari '{$help->status}' ke '{$toStatus}' tidak diizinkan."
            );
        }
    }

    /**
     * Memproses persetujuan refund dari laporan aduan.
     * Mengembalikan dana escrow 100% ke saldo dompet customer.
     */
    public function processReportRefund(PartnerReport $report, User $admin, ?string $adminNotes = null): void
    {
        DB::transaction(function () use ($report, $admin, $adminNotes) {
            $customer = $report->reporter ?? User::find($report->reporter_id);
            if (!$customer) {
                throw new \Exception('Data pelapor (Customer) tidak ditemukan.');
            }

            $help = $report->reportedHelp ?? ($report->reported_help_id ? Help::find($report->reported_help_id) : null);

            if ($help) {
                $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();
                if ($lockedHelp) {
                    // Cek jika dana escrow bantuan sudah pernah dirilis ke Mitra
                    if ($lockedHelp->escrow_status === Help::ESCROW_STATUS_RELEASED) {
                        throw new \Exception('Dana escrow untuk bantuan ini telah dicairkan ke Mitra. Refund otomatis tidak dapat diproses.');
                    }
                    // Cek jika sengketa telah diputuskan sebelumnya secara final
                    if ($lockedHelp->dispute_resolved_at !== null) {
                        throw new \Exception('Sengketa pada pesanan ini telah diputuskan secara final oleh Admin sebelumnya. Tidak dapat memproses refund baru.');
                    }
                }

                // Cek idempotensi refund: jangan sampai bantuan yang sama direfund 2x
                $alreadyRefunded = BalanceTransaction::where('user_id', $customer->id)
                    ->where('reference_id', $help->id)
                    ->where('type', 'refund')
                    ->exists();
                if ($alreadyRefunded) {
                    throw new \Exception('Dana bantuan ini sudah pernah dikembalikan (refund) ke customer sebelumnya.');
                }
            }

            // Hitung nominal refund
            $refundAmount = (float) $report->refund_amount;
            if ($refundAmount <= 0 && $help) {
                $refundAmount = (float) ($help->total_amount > 0 ? $help->total_amount : $help->amount);
            }

            if ($refundAmount <= 0) {
                throw new \Exception('Nominal refund tidak valid atau bernilai 0.');
            }

            // Kembalikan dana ke saldo customer
            $customerBalance = UserBalance::firstOrCreate(
                ['user_id' => $customer->id],
                ['balance' => 0]
            );

            $customerBalance->refundToCustomer(
                $refundAmount,
                $help?->id,
                $help?->order_id,
                "Pengembalian Dana Refund (Laporan #{$report->id}: '{$report->title}')",
                "report:{$report->id}:refund:{$customer->id}"
            );

            $notesEntry = $adminNotes ? "[Refund Disetujui]: " . trim($adminNotes) : "[Refund Disetujui oleh {$admin->name}]";
            $updatedNotes = $report->admin_notes ? $report->admin_notes . "\n" . $notesEntry : $notesEntry;

            $report->update([
                'refund_status'       => 'approved',
                'refund_amount'       => $refundAmount,
                'refund_processed_at' => now(),
                'refund_processed_by' => $admin->id,
                'status'              => 'resolved',
                'resolved_at'         => now(),
                'resolved_by'         => $admin->id,
                'admin_notes'         => $updatedNotes,
            ]);

            // Jika bantuan terkait belum dibatalkan, set status bantuan menjadi dibatalkan & escrow refunded
            if ($help) {
                $help->update([
                    'status'        => Help::STATUS_DIBATALKAN,
                    'escrow_status' => Help::ESCROW_STATUS_REFUNDED,
                    'payment_status'=> Help::PAYMENT_STATUS_REFUNDED,
                    'dispatch_mode' => Help::DISPATCH_MODE_CLOSED,
                ]);
            }

            Log::info('[HelpTransactionService] Refund laporan disetujui', [
                'report_id'     => $report->id,
                'customer_id'   => $customer->id,
                'refund_amount' => $refundAmount,
                'admin_id'      => $admin->id,
            ]);
        });
    }

    /**
     * Menolak permohonan refund pada laporan aduan dengan alasan resmi.
     * Penolakan komplain customer berarti kemenangan bagi mitra: dana escrow dicairkan ke mitra & order diselesaikan.
     */
    public function rejectReportRefund(PartnerReport $report, User $admin, string $reason): void
    {
        DB::transaction(function () use ($report, $admin, $reason) {
            $notesEntry = "[Refund/Komplain Ditolak]: " . trim($reason);
            $updatedNotes = $report->admin_notes ? $report->admin_notes . "\n" . $notesEntry : $notesEntry;

            $report->update([
                'refund_status'       => 'rejected',
                'status'              => 'resolved',
                'resolved_at'         => now(),
                'resolved_by'         => $admin->id,
                'admin_notes'         => $updatedNotes,
            ]);

            // Jika laporan ini terkait pesanan bantuan yang sedang dibekukan / ditahan,
            // penolakan komplain customer berarti kemenangan bagi mitra: cairkan dana escrow ke Mitra & selesaikan order!
            $help = $report->reportedHelp;
            if ($help) {
                $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();
                if ($lockedHelp && in_array($lockedHelp->escrow_status, [Help::ESCROW_STATUS_DISPUTED_FREEZE, Help::ESCROW_STATUS_HELD])) {
                    if ($lockedHelp->mitra_id) {
                        $this->escrowService->releaseEscrowToMitra($lockedHelp, 'admin_dispute_release');
                    }
                    $lockedHelp->update([
                        'status'              => Help::STATUS_SELESAI,
                        'escrow_status'       => Help::ESCROW_STATUS_RELEASED,
                        'payment_status'      => Help::PAYMENT_STATUS_PAID,
                        'rating_status'       => Help::RATING_STATUS_PENDING,
                        'dispatch_mode'       => Help::DISPATCH_MODE_CLOSED,
                        'dispute_resolved_at' => now(),
                        'dispute_resolved_by' => $admin->id,
                    ]);
                }
            }

            Log::info('[HelpTransactionService] Refund laporan ditolak & dana diteruskan ke Mitra', [
                'report_id' => $report->id,
                'admin_id'  => $admin->id,
                'reason'    => $reason,
            ]);
        });
    }
}
