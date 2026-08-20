<?php

namespace App\Observers;

use App\Models\Help;
use App\Models\BalanceTransaction;
use App\Models\PartnerActivity;
use App\Models\UserBalance;

/**
 * HelpObserver
 *
 * Observer ini berfungsi sebagai FALLBACK untuk event yang terjadi
 * di luar HelpTransactionService (misalnya update dari Admin panel).
 *
 * Untuk aksi yang dilakukan via HelpTransactionService, notifikasi dan
 * activity log sudah diurus langsung di service tersebut agar lebih
 * predictable dan tidak double-fire.
 */
class HelpObserver
{
    /**
     * Handle Help "created".
     *
     * Kirim notifikasi ke mitra-mitra aktif bahwa ada bantuan baru.
     */
    public function created(Help $help): void
    {
        try {
            $customer     = $help->user ?? \App\Models\User::find($help->user_id);
            $customerName = $customer?->name ?? 'Customer';

            // Audit trail
            PartnerActivity::create([
                'user_id'       => $help->user_id,
                'help_id'       => $help->id,
                'activity_type' => 'help_created',
                'description'   => "Customer {$customerName} membuat permohonan bantuan ('{$help->title}')",
                'photo'         => $help->photo,
                'ip_address'    => request()?->ip(),
                'user_agent'    => request()?->header('User-Agent'),
            ]);

            // Notifikasi ke mitra aktif (prioritas kota yang sama)
            $mitraQuery = \App\Models\User::where('role', 'mitra')->where('status', 'active');
            if ($help->city_id) {
                $mitras = (clone $mitraQuery)->where('city_id', $help->city_id)->take(20)->get();
                if ($mitras->isEmpty()) {
                    $mitras = $mitraQuery->take(20)->get();
                }
            } else {
                $mitras = $mitraQuery->take(20)->get();
            }

            foreach ($mitras as $mitra) {
                $settings = $mitra->notification_settings ?? [];
                if (!empty($settings['help_updates']) || !isset($settings['help_updates'])) {
                    try {
                        $mitra->notify(new \App\Notifications\NewHelpAvailableNotification($help));
                    } catch (\Throwable $e) {
                        // Jangan stop loop karena satu gagal
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('[HelpObserver] created: ' . $e->getMessage());
        }
    }

    /**
     * Handle Help "updated".
     *
     * Hanya menangani kasus yang TIDAK dilakukan via HelpTransactionService:
     * - Update dari Admin panel
     * - Update langsung dari Artisan command (misal AutoConfirmHelps)
     * - Kredit saldo mitra saat status berubah ke selesai
     *
     * Untuk mencegah double-notification, observer ini TIDAK mengirim notifikasi
     * ke customer/mitra untuk perubahan status standard (sudah ditangani service).
     */
    public function updated(Help $help): void
    {
        // Kredit mitra saat status berubah ke selesai (bisa dari admin atau auto-confirm)
        if ($help->wasChanged('status')) {
            $newStatus  = $help->status;
            $prevStatus = $help->getOriginal('status');

            $completedStates = [Help::STATUS_SELESAI, 'completed'];

            if (in_array($newStatus, $completedStates) && !in_array($prevStatus, $completedStates)) {
                $this->creditMitraIfNeeded($help);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE
    // ─────────────────────────────────────────────────────────────────────────

    private function creditMitraIfNeeded(Help $help): void
    {
        if (!$help->mitra_id || $help->amount <= 0) {
            return;
        }

        try {
            // Idempotency: cek apakah sudah pernah dikreditkan
            $already = BalanceTransaction::where('user_id', $help->mitra_id)
                ->where('reference_id', $help->id)
                ->where('type', 'credit')
                ->exists();

            if ($already) {
                \Log::info('[HelpObserver] Mitra sudah dikreditkan untuk help #' . $help->id . ', skip.');
                return;
            }

            $userBalance = UserBalance::firstOrCreate(
                ['user_id' => $help->mitra_id],
                ['balance' => 0]
            );

            $userBalance->addBalance(
                $help->amount,
                'Pendapatan Bantuan (' . $help->title . ')',
                $help->id
            );

            \Log::info('[HelpObserver] Mitra dikreditkan via observer (fallback)', [
                'help_id'  => $help->id,
                'mitra_id' => $help->mitra_id,
                'amount'   => $help->amount,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('[HelpObserver] creditMitraIfNeeded error: ' . $e->getMessage(), ['help_id' => $help->id]);
        }
    }
}
