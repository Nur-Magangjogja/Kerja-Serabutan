<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\BalanceTransaction;
use App\Models\Chat;
use App\Models\Help;
use App\Models\PartnerActivity;
use App\Models\PartnerReport;
use App\Models\User;
use App\Models\UserBalance;
use App\Notifications\ChatMessageNotification;
use App\Notifications\HelpStatusNotification;
use App\Notifications\HelpTakenNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * HelpTransactionService
 *
 * Memusatkan seluruh logika bisnis transaksi bantuan jasa.
 * Setiap aksi berjalan dalam DB::transaction dengan pessimistic locking (lockForUpdate)
 * untuk mencegah race condition, duplikasi penugasan, dan double processing.
 *
 * Aturan Bisnis Inti:
 * 1. Mitra hanya dapat mengambil 1 tugas bantuan aktif sekaligus.
 * 2. Transisi status dikawal oleh State Machine (Help::canTransitionTo).
 * 3. Notifikasi dan pesan chat dikirim secara otomatis ke ruang obrolan (chat)
 *    pada setiap fase (ambil tugas, pembatalan mitra, persetujuan/penolakan batal,
 *    penyelesaian tugas, dan konfirmasi customer).
 * 4. Pembatalan oleh mitra yang disetujui dikenakan denda penalti.
 * 5. Idempotency guard pada kredit dan denda saldo untuk menjamin keadilan 2 belah pihak.
 *
 * Model v2 (Commission-Based / Escrow — berlaku untuk helps dengan model_version = 2):
 * - Escrow Lock: Dana customer ditahan ke Holding saat tugas dibuat.
 * - Split Payment: Saat selesai, Holding dibagi: Earning (mitra) + Platform Fee (kas).
 * - Refund: Jika batal, Holding dikembalikan 100% ke customer (tanpa potongan).
 * - Penalty: Denda mitra tetap dari saldo mitra sendiri (bukan dari escrow).
 */
class HelpTransactionService
{
    // ─────────────────────────────────────────────────────────────────────────
    // MITRA ACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Mitra mengambil bantuan dari pool.
     */
    public function takeHelp(Help $help, User $mitra, ?float $lat = null, ?float $lng = null): void
    {
        // 1. Guard: Mitra tidak boleh mengambil tugas baru jika masih memiliki tugas aktif
        $this->assertMitraHasNoActiveTask($mitra);

        // 2. Guard: Batas radius jangkauan maksimal 60 km
        $mitraLat = $lat ?? ($mitra->latitude ? (float) $mitra->latitude : null);
        $mitraLng = $lng ?? ($mitra->longitude ? (float) $mitra->longitude : null);
        if ($mitraLat && $mitraLng && $help->latitude && $help->longitude) {
            $distMeters = app(LocationTrackingService::class)->calculateDistance(
                (float) $mitraLat, (float) $mitraLng,
                (float) $help->latitude, (float) $help->longitude
            );
            $distKm = $distMeters / 1000;
            if ($distKm > 60) {
                throw new \RuntimeException('Lokasi bantuan ini berjarak ' . round($distKm, 1) . ' km, melebihi batas radius jangkauan maksimal Mitra (60 km).');
            }
        }

        // 3. Guard: Mitra yang sebelumnya telah membatalkan bantuan ini tidak boleh mengambilnya kembali
        if ($help->hasCancelledBy($mitra->id)) {
            throw new \RuntimeException('Anda tidak dapat mengambil bantuan ini karena sebelumnya telah Anda batalkan.');
        }

        // 4. Transaksi atomik dengan Pessimistic Locking
        DB::transaction(function () use ($help, $mitra, $lat, $lng) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if (!$lockedHelp || $lockedHelp->mitra_id !== null || $lockedHelp->status !== Help::STATUS_MENUNGGU_MITRA) {
                throw new \RuntimeException('Bantuan ini sudah diambil oleh Rekan Jasa lain atau tidak tersedia lagi.');
            }

            if ($lockedHelp->hasCancelledBy($mitra->id)) {
                throw new \RuntimeException('Anda tidak dapat mengambil bantuan ini karena sebelumnya telah Anda batalkan.');
            }

            if ($lockedHelp->scheduled_at && \Illuminate\Support\Carbon::parse($lockedHelp->scheduled_at)->isFuture()) {
                throw new \RuntimeException('Bantuan ini dijadwalkan untuk waktu yang akan datang dan belum dapat diambil saat ini.');
            }

            $lockedHelp->update([
                'mitra_id'      => $mitra->id,
                'status'        => Help::STATUS_TAKEN,
                'dispatch_mode' => Help::DISPATCH_MODE_ASSIGNED,
                'taken_at'      => now(),
            ]);

            // Ubah status online mitra menjadi BUSY
            app(PartnerOnlineService::class)->setBusy($mitra->id, $lockedHelp->id);

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

        // 3. Notifikasi, Pesan Chat Sambutan, & Audit Log
        $this->notifyHelpTaken($help, $mitra);
        $this->sendWelcomeChat($help, $mitra);
        $this->logActivity(
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

        DB::transaction(function () use ($help) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();
            $lockedHelp->update([
                'status'             => Help::STATUS_PARTNER_ON_THE_WAY,
                'partner_started_at' => $lockedHelp->partner_started_at ?? now(),
            ]);
        });

        $this->sendStatusNotification($help, Help::STATUS_PARTNER_ON_THE_WAY, $help->user, $mitra);
        $this->logActivity(
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

        $this->sendStatusNotification($help, Help::STATUS_PARTNER_ARRIVED, $help->user, $mitra);
        $this->logActivity(
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

        $this->sendStatusNotification($help, Help::STATUS_IN_PROGRESS, $help->user, $mitra);
        $this->sendServiceStartedChat($help, $mitra);
        $this->logActivity(
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

        DB::transaction(function () use ($help, $path, $notes) {
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
        });

        $help->refresh();

        // Kirim pesan chat dengan foto bukti ke customer
        $this->sendCompletionChat($help, $mitra, $path, $notes);
        $this->sendStatusNotification($help, Help::STATUS_WAITING_CONFIRMATION, $help->user, $mitra);
        $this->logActivity(
            $mitra->id,
            $help->id,
            'help_completed_waiting_confirmation',
            "Mitra {$mitra->name} menyelesaikan bantuan dan mengunggah foto bukti (Menunggu Konfirmasi Customer / 24 Jam)",
            $path
        );
    }

    /**
     * Mitra mengajukan permintaan pembatalan.
     */
    public function requestPartnerCancel(Help $help, User $mitra, ?string $reason = null): void
    {
        $this->assertMitraAssigned($help, $mitra);
        $this->assertCanTransition($help, Help::STATUS_PARTNER_CANCEL_REQUESTED);

        $prevStatus = $help->status;

        DB::transaction(function () use ($help, $prevStatus, $reason) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if ($lockedHelp->status === Help::STATUS_PARTNER_CANCEL_REQUESTED) {
                throw new \RuntimeException('Permintaan pembatalan sudah diajukan sebelumnya.');
            }

            $lockedHelp->update([
                'partner_cancel_prev_status'  => $prevStatus,
                'status'                      => Help::STATUS_PARTNER_CANCEL_REQUESTED,
                'partner_cancel_requested_at' => now(),
                'partner_cancel_reason'       => $reason,
            ]);
        });

        // Kirim pesan chat otomatis ke customer bahwa mitra mengajukan pembatalan
        $this->sendCancellationRequestChat($help, $mitra, $reason);
        $this->sendStatusNotification($help, Help::STATUS_PARTNER_CANCEL_REQUESTED, $help->user, $mitra);
        $this->logActivity(
            $mitra->id,
            $help->id,
            'cancel_requested',
            "Mitra {$mitra->name} mengajukan pembatalan bantuan. Alasan: " . ($reason ?: 'Tidak disebutkan')
        );
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

            if (in_array($lockedHelp->status, [Help::STATUS_SELESAI, 'completed'])) {
                throw new \RuntimeException('Pesanan bantuan ini sudah diselesaikan sebelumnya.');
            }

            if ($lockedHelp->escrow_status === Help::ESCROW_STATUS_DISPUTED_FREEZE) {
                throw new \RuntimeException('Pesanan ini sedang dalam proses sengketa/mediasi.');
            }

            // Kreditkan pembayaran ke saldo mitra (atomic release)
            $this->releaseEscrowToMitra($lockedHelp, 'customer_confirm');
        });

        $help->refresh();

        // Kirim pesan chat penutup dari Customer ke Mitra
        $this->sendConfirmationChat($help, $customer, $help->mitra);
        $this->sendStatusNotification($help, Help::STATUS_SELESAI, $help->mitra, $customer);
        $this->logActivity(
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

            $this->releaseEscrowToMitra($lockedHelp, 'auto_confirm');
            $executed = true;
        });

        if ($executed) {
            $help->refresh();
            $this->sendStatusNotification($help, Help::STATUS_SELESAI, $help->mitra, null);
            $this->logActivity(
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

            if ($lockedHelp->escrow_status !== Help::ESCROW_STATUS_HELD) {
                throw new \RuntimeException('Dana bantuan tidak dalam status holding.');
            }

            if ($lockedHelp->status !== Help::STATUS_WAITING_CONFIRMATION) {
                throw new \RuntimeException('Sengketa hanya dapat diajukan saat pesanan menunggu konfirmasi.');
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

        $this->logActivity(
            $customer->id,
            $help->id,
            'dispute_raised',
            "Customer {$customer->name} mengajukan sengketa/komplain. Dana escrow dibekukan (Freeze)."
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
                $this->releaseEscrowToMitra($lockedHelp, 'admin_dispute_release');
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
                app(PartnerOnlineService::class)->releaseBusy($lockedHelp->mitra_id, $lockedHelp->id);
            }
        });

        $help->refresh();
        $this->logActivity(
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
     * Model v1: tidak ada escrow, tidak ada refund (logika lama).
     */
    public function customerCancelHelp(Help $help, User $customer): void
    {
        $this->assertCustomerOwns($help, $customer);
        $this->assertCanTransition($help, Help::STATUS_DIBATALKAN);

        DB::transaction(function () use ($help, $customer) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            $cancellableStatuses = [
                Help::STATUS_MENUNGGU_MITRA,
                'mencari_mitra',
                'menunggu_pembayaran',
                'pending',
            ];

            if (!in_array($lockedHelp->status, $cancellableStatuses, true)) {
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
                $this->refundFromEscrow($lockedHelp, $customer);
            }
        });

        $this->logActivity(
            $customer->id,
            $help->id,
            'help_cancelled',
            "Customer {$customer->name} membatalkan bantuan (Refund dana Rp " . number_format($help->amount, 0, ',', '.') . " ke saldo customer)"
        );
    }

    /**
     * Membatalkan bantuan yang kadaluwarsa secara otomatis dan mengembalikan 100% dana escrow ke customer.
     */
    public function autoCancelExpiredHelp(Help $help, string $reason = 'Batas waktu pencarian Rekan Jasa telah berakhir'): void
    {
        DB::transaction(function () use ($help, $reason) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            $cancellableStatuses = [
                Help::STATUS_MENUNGGU_MITRA,
                'mencari_mitra',
                'menunggu_pembayaran',
                'pending',
            ];

            if (!$lockedHelp || !in_array($lockedHelp->status, $cancellableStatuses, true) || $lockedHelp->mitra_id !== null) {
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
                $this->refundFromEscrow($lockedHelp, $customer);
            }
        });

        if ($help->user) {
            $this->logActivity(
                $help->user_id,
                $help->id,
                'help_auto_cancelled',
                "Permintaan bantuan dibatalkan otomatis oleh sistem. Alasan: {$reason}. Dana telah dikembalikan 100% ke saldo akun Anda."
            );

            try {
                $help->user->notify(new \App\Notifications\HelpStatusNotification($help, Help::STATUS_MENUNGGU_MITRA, Help::STATUS_DIBATALKAN, $help->user));
            } catch (\Throwable $e) {
                // ignore notification delivery error
            }
        }

        Log::info('[HelpTransactionService] autoCancelExpiredHelp success', ['help_id' => $help->id]);
    }

    /**
     * Customer menerima permintaan pembatalan dari mitra.
     * Mitra dikenakan denda penalti (kas admin) dan dilepaskan dari pesanan.
     * Pesanan dikembalikan ke pool status 'menunggu_mitra'.
     * Dana escrow customer TETAP DITAHAN di holding (karena menunggu mitra lain).
     * Jika customer ingin membatalkan pesanan, customer dapat menekan tombol "Batalkan Pesanan" saat status 'menunggu_mitra' untuk menerima refund 100%.
     */
    public function customerAcceptCancel(Help $help, User $customer): void
    {
        $this->assertCustomerOwns($help, $customer);

        if ($help->status !== Help::STATUS_PARTNER_CANCEL_REQUESTED) {
            throw new \RuntimeException('Tidak ada permintaan pembatalan aktif dari Rekan Jasa.');
        }

        $mitraId = $help->mitra_id;
        $formerMitra = $help->mitra ?: ($mitraId ? User::find($mitraId) : null);
        $penaltyFee  = 0;

        DB::transaction(function () use ($help, &$formerMitra, $customer, &$penaltyFee) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if ($lockedHelp->status !== Help::STATUS_PARTNER_CANCEL_REQUESTED) {
                throw new \RuntimeException('Status pesanan telah berubah atau pembatalan sudah diproses.');
            }

            // CATATAN PENTING ALIRAN DANA ESCROW:
            // Dana escrow customer TETAP DITAHAN di holding (tidak direfund di sini),
            // karena pesanan dikembalikan ke pool dengan status 'menunggu_mitra' agar bisa diambil oleh mitra lain.
            // Tidak ada pemotongan denda saldo lagi (sanksi pelanggaran dikelola melalui Daftar Abu-Abu & Surat Peringatan Admin).

            // Tambahkan ID mitra yang membatalkan ke daftar cancelled_mitra_ids
            // agar mitra ini tidak dapat mengambil kembali bantuan ini di masa mendatang
            $cancelledMitraIds = $lockedHelp->cancelled_mitra_ids ?? [];
            if (!is_array($cancelledMitraIds)) {
                $cancelledMitraIds = json_decode((string) $cancelledMitraIds, true) ?? [];
            }
            if ($formerMitra && !in_array($formerMitra->id, $cancelledMitraIds, false)) {
                $cancelledMitraIds[] = $formerMitra->id;
            }

            // Reset seluruh state bantuan kembali ke pool
            $lockedHelp->update([
                'status'                      => Help::STATUS_MENUNGGU_MITRA,
                'mitra_id'                    => null,
                'cancelled_mitra_ids'         => $cancelledMitraIds,
                'partner_cancel_prev_status'  => null,
                'partner_cancel_reason'       => null,
                'partner_cancel_requested_at' => null,
                'partner_current_lat'         => null,
                'partner_current_lng'         => null,
                'partner_initial_lat'         => null,
                'partner_initial_lng'         => null,
                'partner_started_at'          => null,
                'partner_arrived_at'          => null,
                'service_started_at'          => null,
                'service_completed_at'        => null,
                'taken_at'                    => null,
                'proof_photo'                 => null,
                'completion_notes'            => null,
            ]);
        });

        // Notifikasi dan Chat ke Mitra Lama
        if ($formerMitra) {
            try {
                $formerMitra->notify(new HelpStatusNotification(
                    $help,
                    'partner_cancel_requested',
                    'cancel_accepted',
                    $formerMitra
                ));
            } catch (\Throwable $e) {
                Log::warning('[HelpTransactionService] Failed to notify former mitra on cancel accepted: ' . $e->getMessage());
            }

            $this->sendCancellationResolvedChat($help, $formerMitra, $customer, 'accepted');
        }

        $this->logActivity(
            $customer->id,
            $help->id,
            'cancel_accepted',
            "Customer {$customer->name} menyetujui pembatalan bantuan. Pesanan dikembalikan ke pencarian mitra."
        );

        Log::info('[HelpTransactionService] customerAcceptCancel success', [
            'help_id'     => $help->id,
            'mitra_id'    => $formerMitra?->id,
            'model_v2'    => $help->isV2Model(),
        ]);
    }

    /**
     * Customer menolak permintaan pembatalan dari mitra.
     * Bantuan kembali ke status sebelum pembatalan diajukan.
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

        // Notifikasi dan Chat ke mitra bahwa pembatalan ditolak
        if ($help->mitra) {
            try {
                $help->mitra->notify(new HelpStatusNotification(
                    $help,
                    'partner_cancel_requested',
                    'cancel_rejected',
                    $help->mitra
                ));
            } catch (\Throwable $e) {
                Log::warning('[HelpTransactionService] Failed to notify mitra on cancel rejected: ' . $e->getMessage());
            }

            $this->sendCancellationResolvedChat($help, $help->mitra, $customer, 'rejected');
        }

        $this->logActivity(
            $customer->id,
            $help->id,
            'cancel_rejected',
            "Customer {$customer->name} menolak pembatalan bantuan. Mitra diminta lanjutkan pekerjaan."
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE GUARDS & HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Memastikan mitra tidak memiliki tugas aktif lain yang belum selesai.
     */
    private function assertMitraHasNoActiveTask(User $mitra): void
    {
        $hasActive = Help::where('mitra_id', $mitra->id)
            ->whereIn('status', [
                Help::STATUS_TAKEN,
                'memperoleh_mitra',
                Help::STATUS_PARTNER_ON_THE_WAY,
                Help::STATUS_PARTNER_ARRIVED,
                Help::STATUS_IN_PROGRESS,
                'sedang_diproses',
                Help::STATUS_WAITING_CONFIRMATION,
                Help::STATUS_PARTNER_CANCEL_REQUESTED,
            ])
            ->exists();

        if ($hasActive) {
            throw new \RuntimeException('Anda masih memiliki tugas bantuan aktif yang sedang berjalan. Harap selesaikan tugas tersebut terlebih dahulu sebelum mengambil tugas baru.');
        }
    }

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
     * Melepaskan dana escrow ke saldo mitra dan kas platform secara atomik & idempotent.
     * Mengubah status order menjadi selesai, escrow_status released, payment_status paid, dispatch_mode closed.
     */
    private function releaseEscrowToMitra(Help $lockedHelp, string $triggeredBy = 'customer_confirm'): void
    {
        if (!$lockedHelp->mitra_id || $lockedHelp->amount <= 0) {
            return;
        }

        $netEarning  = $lockedHelp->getNetEarning();
        $platformFee = $lockedHelp->getPlatformFee();

        // 1. Catat Earning Mitra (Idempotent)
        $alreadyCredited = BalanceTransaction::where('user_id', $lockedHelp->mitra_id)
            ->where('reference_id', $lockedHelp->id)
            ->whereIn('type', ['earning', 'topup'])
            ->exists();

        if (!$alreadyCredited) {
            $mitraBalance = UserBalance::firstOrCreate(
                ['user_id' => $lockedHelp->mitra_id],
                ['balance' => 0]
            );

            $mitraBalance->receiveEarning(
                $netEarning,
                $lockedHelp->id,
                "Pendapatan Bantuan '{$lockedHelp->title}'",
                $lockedHelp->order_id,
                "help:{$lockedHelp->id}:earning:{$lockedHelp->mitra_id}"
            );
        }

        // 2. Catat Platform Fee (Idempotent)
        if ($platformFee > 0) {
            $alreadyFee = BalanceTransaction::where('reference_id', $lockedHelp->id)
                ->where('type', 'platform_fee')
                ->exists();

            if (!$alreadyFee) {
                BalanceTransaction::create([
                    'idempotency_key' => "help:{$lockedHelp->id}:platform_fee",
                    'user_id'         => null,
                    'amount'          => $platformFee,
                    'direction'       => 'credit',
                    'type'            => 'platform_fee',
                    'description'     => "Biaya Layanan Platform {$lockedHelp->getCommissionRateLabel()} dari Bantuan '{$lockedHelp->title}'",
                    'reference_id'    => $lockedHelp->id,
                    'reference_type'  => 'help',
                    'order_id'        => $lockedHelp->order_id,
                    'status'          => 'completed',
                ]);
            }
        }

        // 3. Update Status Order
        $lockedHelp->update([
            'status'            => Help::STATUS_SELESAI,
            'escrow_status'     => Help::ESCROW_STATUS_RELEASED,
            'payment_status'    => Help::PAYMENT_STATUS_PAID,
            'rating_status'     => Help::RATING_STATUS_PENDING,
            'dispatch_mode'     => Help::DISPATCH_MODE_CLOSED,
            'completed_at'      => $lockedHelp->completed_at ?? now(),
            'auto_confirmed_at' => ($triggeredBy === 'auto_confirm') ? now() : null,
        ]);

        // Lepaskan status BUSY mitra
        app(PartnerOnlineService::class)->releaseBusy($lockedHelp->mitra_id, $lockedHelp->id);

        Log::info('[HelpTransactionService] releaseEscrowToMitra selesai', [
            'help_id'      => $lockedHelp->id,
            'mitra_id'     => $lockedHelp->mitra_id,
            'net_earning'  => $netEarning,
            'platform_fee' => $platformFee,
            'triggered_by' => $triggeredBy,
        ]);
    }

    /**
     * Kredit saldo mitra (wrapper kompatibilitas ke releaseEscrowToMitra).
     */
    private function creditMitra(Help $help): void
    {
        $this->releaseEscrowToMitra($help, 'manual_credit');
    }

    /**
     * MODEL V2: Kembalikan escrow dari Holding ke saldo Customer (Refund 100%).
     *
     * Dipanggil saat tugas dibatalkan. Platform TIDAK memotong komisi apapun.
     * Dana total dikembalikan utuh ke customer.
     */
    private function refundFromEscrow(Help $help, User $customer): void
    {
        // Idempotency: cek sudah pernah direfund
        $alreadyRefunded = BalanceTransaction::where('user_id', $customer->id)
            ->where('reference_id', $help->id)
            ->where('type', 'refund')
            ->exists();

        if ($alreadyRefunded) {
            Log::info('[HelpTransactionService] Refund sudah dilakukan untuk help ' . $help->id . ', skip.');
            return;
        }

        $customerBalance = UserBalance::firstOrCreate(
            ['user_id' => $customer->id],
            ['balance' => 0]
        );

        $refundAmount = (float) ($help->total_amount > 0 ? $help->total_amount : $help->amount);

        $customerBalance->refundToCustomer(
            $refundAmount,
            $help->id,
            $help->order_id,
            "Pengembalian Dana 100% (Bantuan '{$help->title}' Dibatalkan)",
            "help:{$help->id}:refund:{$customer->id}"
        );

        Log::info('[HelpTransactionService] Refund escrow (v2) ke customer', [
            'help_id'     => $help->id,
            'customer_id' => $customer->id,
            'amount'      => $refundAmount,
        ]);

        if ($help->mitra_id) {
            app(PartnerOnlineService::class)->releaseBusy($help->mitra_id, $help->id);
        }
    }

    /**
     * Menerapkan denda penalti pembatalan ke saldo mitra.
     *
     * Denda dicatat sebagai tipe 'penalty' (bukan 'deduction') agar jelas
     * bahwa ini adalah sanksi atas pelanggaran dan uangnya masuk ke kas administrasi.
     */
    private function applyCancellationPenalty(User $mitra, Help $help): float
    {
        $penaltyFee = (float) AppSetting::get('mitra_cancel_penalty_fee', 5000);
        if ($penaltyFee <= 0) {
            $penaltyFee = 5000;
        }

        // Idempotency: pastikan denda hanya dikenakan 1 kali per help
        // Cek pada tipe 'penalty' (tipe khusus denda pembatalan)
        $alreadyPenalized = BalanceTransaction::where('user_id', $mitra->id)
            ->where('reference_id', $help->id)
            ->where('type', 'penalty')
            ->exists();

        // Backward compat: cek juga denda lama yang masih bertipe 'deduction'
        if (!$alreadyPenalized) {
            $alreadyPenalized = BalanceTransaction::where('user_id', $mitra->id)
                ->where('reference_id', $help->id)
                ->where('type', 'deduction')
                ->where('description', 'like', '%Denda%')
                ->exists();
        }

        if ($alreadyPenalized) {
            Log::info("[HelpTransactionService] Denda sudah pernah diterapkan untuk mitra {$mitra->id} pada help {$help->id}");
            return $penaltyFee;
        }

        $userBalance = UserBalance::firstOrCreate(
            ['user_id' => $mitra->id],
            ['balance' => 0]
        );

        // Gunakan applyPenalty() agar tercatat sebagai tipe 'penalty',
        // bukan 'deduction', sehingga jelas ini adalah denda → kas administrasi.
        $userBalance->applyPenalty(
            $penaltyFee,
            "Pembatalan Tugas Bantuan ('{$help->title}') • Catatan Kepatuhan",
            $help->id,
            $help->order_id
        );

        Log::info("[HelpTransactionService] Denda pembatalan Rp {$penaltyFee} (penalty) dipotong dari mitra {$mitra->id} untuk help {$help->id}");

        return $penaltyFee;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIVITY LOG & NOTIFICATION HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Catat aktivitas mitra / customer ke PartnerActivity & ActivityLog.
     */
    private function logActivity($userId, $helpId, string $activityType, ?string $description = null, ?string $photo = null): void
    {
        try {
            \App\Models\PartnerActivity::create([
                'user_id'       => $userId,
                'help_id'       => $helpId,
                'activity_type' => $activityType,
                'description'   => $description,
                'photo'         => $photo,
                'ip_address'    => function_exists('request') ? request()?->ip() : null,
                'user_agent'    => function_exists('request') ? request()?->header('User-Agent') : null,
            ]);

            \App\Models\ActivityLog::record(
                $userId,
                $activityType,
                $description ?? "Aktivitas bantuan #{$helpId}"
            );
        } catch (\Throwable $e) {
            Log::warning('[HelpTransactionService] logActivity failed: ' . $e->getMessage(), [
                'user_id'       => $userId,
                'help_id'       => $helpId,
                'activity_type' => $activityType,
            ]);
        }
    }

    private function notifyHelpTaken(Help $help, User $mitra): void
    {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if ($customer) {
                $customer->notify(new HelpTakenNotification($help, $mitra));
            }
        } catch (\Throwable $e) {
            Log::warning('[HelpTransactionService] Failed to send HelpTakenNotification: ' . $e->getMessage());
        }
    }

    private function sendWelcomeChat(Help $help, User $mitra): void
    {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if (!$customer || !$mitra) return;

            $greeting = $customer->name ? "Halo Kak {$customer->name}" : "Halo Kak";
            $message  = "{$greeting}, perkenalkan saya {$mitra->name}. Saya telah mengambil permohonan bantuan Anda '{$help->title}'. Saya akan segera menuju lokasi Anda. Jika ada instruksi tambahan, silakan infokan di sini!";

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'message'     => $message,
                'sender_type' => 'mitra',
                'read_at'     => null,
            ]);

            $customer->notify(new ChatMessageNotification($help->id, $message, $mitra->id, $mitra->name));
        } catch (\Throwable $e) {
            Log::warning('[HelpTransactionService] Failed to send welcome chat: ' . $e->getMessage());
        }
    }

    private function sendServiceStartedChat(Help $help, User $mitra): void
    {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if (!$customer || !$mitra) return;

            $greeting = $customer->name ? "Halo Kak {$customer->name}" : "Halo Kak";
            $message  = "{$greeting}, saya ({$mitra->name}) telah mulai mengerjakan permohonan bantuan Anda '{$help->title}'. Pelayanan saat ini dalam proses pengerjaan. Jika ada instruksi atau hal yang perlu dikoordinasikan, silakan infokan di chat ini ya!";

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'message'     => $message,
                'sender_type' => 'mitra',
                'read_at'     => null,
            ]);

            $customer->notify(new ChatMessageNotification($help->id, $message, $mitra->id, $mitra->name));
        } catch (\Throwable $e) {
            Log::warning('[HelpTransactionService] Failed to send service started chat: ' . $e->getMessage());
        }
    }

    private function sendCompletionChat(Help $help, User $mitra, string $proofPath, ?string $notes): void
    {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if (!$customer) return;

            $notesText = $notes ? "Catatan: \"{$notes}\". " : '';
            $caption   = "Halo Kak {$customer->name}, pekerjaan '{$help->title}' telah selesai saya kerjakan. {$notesText}Berikut terlampir bukti foto hasil pengerjaan. Tugas ini telah otomatis diselesaikan dan Anda dapat langsung memberikan rating. Terima kasih!";

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'message'     => $caption,
                'photo'       => $proofPath,
                'sender_type' => 'mitra',
                'read_at'     => null,
            ]);

            $customer->notify(new ChatMessageNotification($help->id, $caption, $mitra->id, $mitra->name));
        } catch (\Throwable $e) {
            Log::warning('[HelpTransactionService] Failed to send completion chat: ' . $e->getMessage());
        }
    }

    private function sendConfirmationChat(Help $help, User $customer, ?User $mitra): void
    {
        try {
            if (!$mitra) return;

            $message = "Terima kasih Kak {$mitra->name}, pekerjaan '{$help->title}' telah saya konfirmasi selesai. Pembayaran telah diteruskan ke saldo akun Anda. Semoga sukses selalu!";

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'message'     => $message,
                'sender_type' => 'customer',
                'read_at'     => null,
            ]);

            $mitra->notify(new ChatMessageNotification($help->id, $message, $customer->id, $customer->name));
        } catch (\Throwable $e) {
            Log::warning('[HelpTransactionService] Failed to send confirmation chat: ' . $e->getMessage());
        }
    }

    private function sendCancellationRequestChat(Help $help, User $mitra, ?string $reason = null): void
    {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if (!$customer || !$mitra) return;

            $greeting   = $customer->name ? "Halo Kak {$customer->name}" : "Halo Kak";
            $reasonText = !empty($reason) ? " dengan alasan: \"{$reason}\"" : "";
            $message    = "{$greeting}, mohon maaf saya mengajukan pembatalan untuk permohonan bantuan '{$help->title}'{$reasonText}. Mohon kesediaannya untuk memeriksa dan memberikan persetujuan pada detail pesanan Anda. Terima kasih dan mohon maaf atas ketidaknyamanannya.";

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'message'     => $message,
                'sender_type' => 'mitra',
                'read_at'     => null,
            ]);

            $customer->notify(new ChatMessageNotification($help->id, $message, $mitra->id, $mitra->name));
        } catch (\Throwable $e) {
            Log::warning('[HelpTransactionService] Failed to send cancellation request chat: ' . $e->getMessage());
        }
    }

    private function sendCancellationResolvedChat(Help $help, ?User $mitra, ?User $customer, string $action): void
    {
        try {
            if (!$mitra || !$customer) return;

            if ($action === 'accepted') {
                $message = "Permintaan pembatalan untuk bantuan '{$help->title}' telah disetujui oleh Customer. Pesanan ini telah dikembalikan ke pencarian Rekan Jasa lain.";
            } else {
                $message = "Halo Rekan Jasa {$mitra->name}, permintaan pembatalan Anda untuk bantuan '{$help->title}' ditolak oleh Customer. Mohon untuk melanjutkan pengerjaan bantuan ini.";
            }

            Chat::create([
                'help_id'     => $help->id,
                'mitra_id'    => $mitra->id,
                'customer_id' => $customer->id,
                'message'     => $message,
                'sender_type' => 'customer',
                'read_at'     => null,
            ]);

            $mitra->notify(new ChatMessageNotification($help->id, $message, $customer->id, $customer->name));
        } catch (\Throwable $e) {
            Log::warning('[HelpTransactionService] Failed to send cancellation resolution chat: ' . $e->getMessage());
        }
    }

    private function sendStatusNotification(Help $help, string $newStatus, ?User $recipient, ?User $actor): void
    {
        if (!$recipient) return;

        try {
            $recipient->notify(new HelpStatusNotification($help, $help->getOriginal('status') ?? '', $newStatus, $actor));
        } catch (\Throwable $e) {
            Log::warning('[HelpTransactionService] Failed to send HelpStatusNotification: ' . $e->getMessage(), [
                'help_id'    => $help->id,
                'new_status' => $newStatus,
            ]);
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
                "Pengembalian Dana Refund (Laporan #{$report->id}: '{$report->title}')"
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

            // Jika bantuan terkait belum selesai/batal, set status bantuan menjadi dibatalkan
            if ($help && in_array($help->status, ['in_progress', 'active', 'sedang_diproses', 'pending', 'menunggu_mitra'])) {
                $help->update(['status' => Help::STATUS_DIBATALKAN]);
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
     */
    public function rejectReportRefund(PartnerReport $report, User $admin, string $reason): void
    {
        DB::transaction(function () use ($report, $admin, $reason) {
            $notesEntry = "[Refund Ditolak]: " . trim($reason);
            $updatedNotes = $report->admin_notes ? $report->admin_notes . "\n" . $notesEntry : $notesEntry;

            $report->update([
                'refund_status'       => 'rejected',
                'admin_notes'         => $updatedNotes,
            ]);

            Log::info('[HelpTransactionService] Refund laporan ditolak', [
                'report_id' => $report->id,
                'admin_id'  => $admin->id,
                'reason'    => $reason,
            ]);
        });
    }
}

