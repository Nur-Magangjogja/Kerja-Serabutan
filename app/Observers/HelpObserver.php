<?php

namespace App\Observers;

use App\Models\Help;
use App\Models\BalanceTransaction;
use App\Models\UserBalance;
use App\Notifications\HelpTakenNotification;
use App\Models\PartnerActivity;

class HelpObserver
{
    /**
     * Handle the Help "created" event.
     * Record activity when a customer creates a help (centralized)
     */
    public function created(Help $help): void
    {
        try {
            $customer = $help->user ?? \App\Models\User::find($help->user_id);
            $customerName = $customer?->name ?? 'Customer';

            PartnerActivity::create([
                'user_id' => $help->user_id,
                'help_id' => $help->id,
                'activity_type' => 'help_created',
                'description' => "Customer {$customerName} membuat permohonan bantuan #{$help->id} ('{$help->title}')",
                'photo' => $help->photo,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->header('User-Agent'),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Failed to record PartnerActivity on help created: ' . $e->getMessage());
        }
    }

    /**
     * Handle the Help "updated" event.
     * Send notification when help is taken by a mitra
     */
    public function updated(Help $help): void
    {
        // Check if mitra_id was just set (help was taken)
        if ($help->wasChanged('mitra_id') && $help->mitra_id !== null && $help->getOriginal('mitra_id') === null) {
            $mitra = $help->mitra ?? \App\Models\User::find($help->mitra_id);
            $customer = $help->user ?? \App\Models\User::find($help->user_id);

            // Send notification to the help requester
            if ($customer && $mitra) {
                $customer->notify(new HelpTakenNotification($help, $mitra));

                // Send automatic introductory chat message from Mitra to Customer
                try {
                    $greetingName = $customer->name ? "Halo Kak {$customer->name}" : "Halo Kak";
                    $autoMessage = "{$greetingName}, perkenalkan saya {$mitra->name}. Saya telah mengambil permohonan bantuan Anda '{$help->title}'. Saya akan segera menuju lokasi Anda. Jika ada instruksi atau petunjuk tambahan, silakan infokan di sini ya!";

                    \App\Models\Chat::create([
                        'help_id' => $help->id,
                        'mitra_id' => $mitra->id,
                        'customer_id' => $customer->id,
                        'message' => $autoMessage,
                        'sender_type' => 'mitra',
                        'read_at' => null,
                    ]);

                    $customer->notify(new \App\Notifications\ChatMessageNotification(
                        $help->id,
                        $autoMessage,
                        $mitra->id,
                        $mitra->name
                    ));
                } catch (\Throwable $e) {
                    \Log::warning('Failed to send auto welcome chat message on help taken: ' . $e->getMessage());
                }
            }

            // Record partner activity for taking the help
            try {
                $mitraName = $mitra?->name ?? 'Mitra';
                PartnerActivity::create([
                    'user_id' => $mitra?->id,
                    'help_id' => $help->id,
                    'activity_type' => 'take_help',
                    'description' => "Mitra {$mitraName} mengambil bantuan #{$help->id} ('{$help->title}')",
                    'ip_address' => request()?->ip(),
                    'user_agent' => request()?->header('User-Agent'),
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Failed to record PartnerActivity on help taken: ' . $e->getMessage());
            }
        }

        // When help status changes, notify the customer, record activity, and handle balance updates
        if ($help->wasChanged('status')) {
            $newStatus = strtolower($help->status ?? '');
            $prevStatus = strtolower($help->getOriginal('status') ?? '');

            $customer = $help->user ?? ($help->user_id ? \App\Models\User::find($help->user_id) : null);
            $mitra = $help->mitra ?? ($help->mitra_id ? \App\Models\User::find($help->mitra_id) : null);

            // Automatically notify customer of progress
            if ($help->user_id && $newStatus !== $prevStatus) {
                try {
                    if ($customer) {
                        $customer->notify(new \App\Notifications\HelpStatusNotification($help, $prevStatus, $newStatus, $mitra));
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Failed to send HelpStatusNotification to customer: ' . $e->getMessage());
                }

                // Record PartnerActivity audit trail for admin
                try {
                    $mitraName = $mitra?->name ?? 'Mitra';
                    $customerName = $customer?->name ?? 'Customer';

                    $activityPayload = match($newStatus) {
                        'partner_on_the_way' => [
                            'user_id' => $mitra?->id ?? $help->mitra_id,
                            'activity_type' => 'partner_on_the_way',
                            'description' => "Mitra {$mitraName} berangkat menuju lokasi bantuan #{$help->id}",
                            'photo' => null,
                        ],
                        'partner_arrived' => [
                            'user_id' => $mitra?->id ?? $help->mitra_id,
                            'activity_type' => 'partner_arrived',
                            'description' => "Mitra {$mitraName} tiba di lokasi bantuan #{$help->id}",
                            'photo' => null,
                        ],
                        'in_progress', 'sedang_diproses' => [
                            'user_id' => $mitra?->id ?? $help->mitra_id,
                            'activity_type' => 'help_started',
                            'description' => "Mitra {$mitraName} mulai mengerjakan bantuan #{$help->id}",
                            'photo' => null,
                        ],
                        'waiting_customer_confirmation' => [
                            'user_id' => $mitra?->id ?? $help->mitra_id,
                            'activity_type' => 'help_completed',
                            'description' => "Mitra {$mitraName} menyelesaikan bantuan #{$help->id} dan mengunggah foto bukti",
                            'photo' => $help->proof_photo,
                        ],
                        'completed', 'selesai' => [
                            'user_id' => $customer?->id ?? $help->user_id,
                            'activity_type' => 'help_confirmed',
                            'description' => "Customer {$customerName} mengonfirmasi bantuan #{$help->id} telah selesai",
                            'photo' => $help->proof_photo,
                        ],
                        'partner_cancel_requested' => [
                            'user_id' => $mitra?->id ?? $help->mitra_id,
                            'activity_type' => 'help_cancelled',
                            'description' => "Mitra {$mitraName} mengajukan pembatalan bantuan #{$help->id}",
                            'photo' => null,
                        ],
                        default => null
                    };

                    if ($activityPayload && $activityPayload['user_id']) {
                        PartnerActivity::create([
                            'user_id' => $activityPayload['user_id'],
                            'help_id' => $help->id,
                            'activity_type' => $activityPayload['activity_type'],
                            'description' => $activityPayload['description'],
                            'photo' => $activityPayload['photo'],
                            'ip_address' => request()?->ip(),
                            'user_agent' => request()?->header('User-Agent'),
                        ]);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Failed to log status PartnerActivity: ' . $e->getMessage());
                }
            }

            $completedStates = ['completed', 'selesai'];

            if (in_array($newStatus, $completedStates) && !in_array($prevStatus, $completedStates)) {
                // Only credit if a mitra was assigned and amount is positive
                if ($help->mitra_id && $help->amount > 0) {
                    $mitraId = $help->mitra_id;

                    // Avoid double-crediting by checking existing balance transaction for this help
                    $already = BalanceTransaction::where('user_id', $mitraId)
                        ->where('reference_id', $help->id)
                        ->exists();

                    if (!$already) {
                        // Ensure the mitra has a UserBalance row
                        $userBalance = UserBalance::firstOrCreate([
                            'user_id' => $mitraId,
                        ], [
                            'balance' => 0,
                        ]);

                        // Credit the mitra with a descriptive transaction
                        $description = 'Pendapatan Bantuan #' . $help->id;
                        $userBalance->addBalance($help->amount, $description, $help->id);
                    }
                }
            }
        }
    }
}
