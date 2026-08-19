<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\BalanceTransaction;
use App\Models\Chat;
use App\Models\Help;
use App\Models\PartnerActivity;
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
 * 4. Pembatalan oleh mitra yang disetujui dikenakan denda penalti (AppSetting / default Rp 5.000).
 * 5. Idempotency guard pada kredit dan denda saldo untuk menjamin keadilan 2 belah pihak.
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

        // 2. Transaksi atomik dengan Pessimistic Locking
        DB::transaction(function () use ($help, $mitra, $lat, $lng) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if (!$lockedHelp || $lockedHelp->mitra_id !== null || $lockedHelp->status !== Help::STATUS_MENUNGGU_MITRA) {
                throw new \RuntimeException('Bantuan ini sudah diambil oleh Rekan Jasa lain atau tidak tersedia lagi.');
            }

            if ($lockedHelp->scheduled_at && \Illuminate\Support\Carbon::parse($lockedHelp->scheduled_at)->isFuture()) {
                throw new \RuntimeException('Bantuan ini dijadwalkan untuk waktu yang akan datang dan belum dapat diambil saat ini.');
            }

            $lockedHelp->update([
                'mitra_id' => $mitra->id,
                'status'   => Help::STATUS_TAKEN,
                'taken_at' => now(),
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

        // 3. Notifikasi, Pesan Chat Sambutan, & Audit Log
        $this->notifyHelpTaken($help, $mitra);
        $this->sendWelcomeChat($help, $mitra);
        $this->logActivity(
            $mitra->id,
            $help->id,
            'take_help',
            "Mitra {$mitra->name} mengambil bantuan #{$help->id} ('{$help->title}')"
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
            "Mitra {$mitra->name} berangkat menuju lokasi bantuan #{$help->id}"
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
            "Mitra {$mitra->name} tiba di lokasi bantuan #{$help->id}"
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
        $this->logActivity(
            $mitra->id,
            $help->id,
            'help_started',
            "Mitra {$mitra->name} mulai mengerjakan bantuan #{$help->id}"
        );
    }

    /**
     * Mitra menyelesaikan pekerjaan dan mengunggah foto bukti.
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
            $data = [
                'status'           => Help::STATUS_WAITING_CONFIRMATION,
                'proof_photo'      => $path,
                'completion_notes' => $notes,
            ];
            if (!$lockedHelp->service_completed_at) {
                $data['service_completed_at'] = now();
            }
            $lockedHelp->update($data);
        });

        // Kirim pesan chat dengan foto bukti ke customer
        $this->sendCompletionChat($help, $mitra, $path, $notes);
        $this->sendStatusNotification($help, Help::STATUS_WAITING_CONFIRMATION, $help->user, $mitra);
        $this->logActivity(
            $mitra->id,
            $help->id,
            'help_completed',
            "Mitra {$mitra->name} menyelesaikan bantuan #{$help->id} dan mengunggah foto bukti",
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
            "Mitra {$mitra->name} mengajukan pembatalan bantuan #{$help->id}. Alasan: " . ($reason ?: 'Tidak disebutkan')
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CUSTOMER ACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Customer mengkonfirmasi bahwa pekerjaan selesai.
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

            $lockedHelp->update([
                'status'       => Help::STATUS_SELESAI,
                'completed_at' => now(),
            ]);

            // Kreditkan pembayaran ke saldo mitra (dengan idempotency lock)
            $this->creditMitra($lockedHelp);
        });

        // Kirim pesan chat penutup dari Customer ke Mitra
        $this->sendConfirmationChat($help, $customer, $help->mitra);
        $this->logActivity(
            $customer->id,
            $help->id,
            'help_confirmed',
            "Customer {$customer->name} mengonfirmasi bantuan #{$help->id} telah selesai",
            $help->proof_photo
        );

        Log::info('[HelpTransactionService] customerConfirmCompletion success', ['help_id' => $help->id]);
    }

    /**
     * Customer membatalkan bantuan sebelum mitra ditemukan.
     */
    public function customerCancelHelp(Help $help, User $customer): void
    {
        $this->assertCustomerOwns($help, $customer);
        $this->assertCanTransition($help, Help::STATUS_DIBATALKAN);

        DB::transaction(function () use ($help) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if ($lockedHelp->status !== Help::STATUS_MENUNGGU_MITRA) {
                throw new \RuntimeException('Bantuan ini tidak dapat dibatalkan secara sepihak karena sudah diambil oleh Rekan Jasa.');
            }

            $lockedHelp->update(['status' => Help::STATUS_DIBATALKAN]);
        });

        $this->logActivity(
            $customer->id,
            $help->id,
            'help_cancelled',
            "Customer {$customer->name} membatalkan bantuan #{$help->id}"
        );
    }

    /**
     * Customer menerima permintaan pembatalan dari mitra.
     * Mitra dikenakan denda penalti, dan bantuan dikembalikan ke pool.
     */
    public function customerAcceptCancel(Help $help, User $customer): void
    {
        $this->assertCustomerOwns($help, $customer);

        if ($help->status !== Help::STATUS_PARTNER_CANCEL_REQUESTED) {
            throw new \RuntimeException('Tidak ada permintaan pembatalan aktif dari Rekan Jasa.');
        }

        $formerMitra = $help->mitra;
        $penaltyFee  = 0;

        DB::transaction(function () use ($help, $formerMitra, &$penaltyFee) {
            $lockedHelp = Help::where('id', $help->id)->lockForUpdate()->first();

            if ($lockedHelp->status !== Help::STATUS_PARTNER_CANCEL_REQUESTED) {
                throw new \RuntimeException('Status pesanan telah berubah atau pembatalan sudah diproses.');
            }

            // Terapkan denda penalti pembatalan ke mitra
            if ($formerMitra) {
                $penaltyFee = $this->applyCancellationPenalty($formerMitra, $lockedHelp);
            }

            // Reset seluruh state bantuan kembali ke pool
            $lockedHelp->update([
                'status'                      => Help::STATUS_MENUNGGU_MITRA,
                'mitra_id'                    => null,
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

            $this->sendCancellationResolvedChat($help, $formerMitra, $customer, 'accepted', $penaltyFee);
        }

        $this->logActivity(
            $customer->id,
            $help->id,
            'cancel_accepted',
            "Customer {$customer->name} menyetujui pembatalan bantuan #{$help->id}. Mitra dikenakan denda Rp " . number_format($penaltyFee, 0, ',', '.') . ". Pesanan dikembalikan ke pool."
        );

        Log::info('[HelpTransactionService] customerAcceptCancel success', [
            'help_id'     => $help->id,
            'mitra_id'    => $formerMitra?->id,
            'penalty_fee' => $penaltyFee,
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
            "Customer {$customer->name} menolak pembatalan bantuan #{$help->id}. Mitra diminta lanjutkan pekerjaan."
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
     * Kredit saldo mitra saat bantuan selesai dikonfirmasi.
     * Dilindungi idempotency check agar tidak double-credit.
     */
    private function creditMitra(Help $help): void
    {
        if (!$help->mitra_id || $help->amount <= 0) {
            return;
        }

        $alreadyCredited = BalanceTransaction::where('user_id', $help->mitra_id)
            ->where('reference_id', $help->id)
            ->where('type', 'topup')
            ->exists();

        if ($alreadyCredited) {
            Log::info('[HelpTransactionService] Mitra sudah dikreditkan untuk help #' . $help->id . ', skip.');
            return;
        }

        $userBalance = UserBalance::firstOrCreate(
            ['user_id' => $help->mitra_id],
            ['balance' => 0]
        );

        $userBalance->addBalance(
            $help->amount,
            'Pendapatan Bantuan #' . $help->id . ' (' . $help->title . ')',
            $help->id
        );

        Log::info('[HelpTransactionService] Mitra dikreditkan', [
            'help_id'  => $help->id,
            'mitra_id' => $help->mitra_id,
            'amount'   => $help->amount,
        ]);
    }

    /**
     * Menerapkan denda penalti pembatalan ke saldo mitra.
     */
    private function applyCancellationPenalty(User $mitra, Help $help): float
    {
        $penaltyFee = (float) AppSetting::get('mitra_cancel_penalty_fee', 5000);
        if ($penaltyFee <= 0) {
            return 0;
        }

        // Idempotency: pastikan denda hanya dikenakan 1 kali per help
        $alreadyPenalized = BalanceTransaction::where('user_id', $mitra->id)
            ->where('reference_id', $help->id)
            ->where('type', 'deduction')
            ->where('description', 'like', '%Denda%')
            ->exists();

        if ($alreadyPenalized) {
            Log::info("[HelpTransactionService] Denda sudah pernah diterapkan untuk mitra {$mitra->id} pada help #{$help->id}");
            return $penaltyFee;
        }

        $userBalance = UserBalance::firstOrCreate(
            ['user_id' => $mitra->id],
            ['balance' => 0]
        );

        $userBalance->deductBalance(
            $penaltyFee,
            "Denda Pembatalan Bantuan #{$help->id} ('{$help->title}')",
            $help->id
        );

        Log::info("[HelpTransactionService] Denda pembatalan Rp {$penaltyFee} dipotong dari mitra {$mitra->id} untuk help #{$help->id}");

        return $penaltyFee;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CHAT & NOTIFICATION HELPERS
    // ─────────────────────────────────────────────────────────────────────────

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

    private function sendCompletionChat(Help $help, User $mitra, string $proofPath, ?string $notes): void
    {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if (!$customer) return;

            $notesText = $notes ? "Catatan: \"{$notes}\". " : '';
            $caption   = "Halo Kak {$customer->name}, pekerjaan '{$help->title}' telah selesai saya kerjakan. {$notesText}Berikut terlampir bukti foto hasil pengerjaan. Mohon periksa dan konfirmasi penyelesaian ya. Terima kasih!";

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

    private function sendCancellationResolvedChat(Help $help, ?User $mitra, ?User $customer, string $action, float $penaltyFee = 0): void
    {
        try {
            if (!$mitra || !$customer) return;

            if ($action === 'accepted') {
                $penaltyText = $penaltyFee > 0
                    ? " Rekan Jasa dikenakan denda penalti pembatalan sebesar Rp " . number_format($penaltyFee, 0, ',', '.') . "."
                    : "";
                $message = "Permintaan pembatalan untuk bantuan '{$help->title}' telah disetujui oleh Customer.{$penaltyText} Pesanan ini telah dikembalikan ke pencarian Rekan Jasa lain.";
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

    private function logActivity(int $userId, int $helpId, string $type, string $description, ?string $photo = null): void
    {
        try {
            PartnerActivity::create([
                'user_id'       => $userId,
                'help_id'       => $helpId,
                'activity_type' => $type,
                'description'   => $description,
                'photo'         => $photo,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[HelpTransactionService] Failed to log PartnerActivity: ' . $e->getMessage());
        }
    }
}
