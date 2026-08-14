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
            PartnerActivity::create([
                'user_id' => $help->user_id,
                'activity_type' => 'help_created',
                'description' => 'Customer membuat bantuan #' . $help->id,
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
            // Load the mitra relationship
            $mitra = $help->mitra;

            // Send notification to the help requester
            if ($help->user && $mitra) {
                $help->user->notify(new HelpTakenNotification($help, $mitra));
            }

            // Record partner activity for taking the help
            try {
                PartnerActivity::create([
                    'user_id' => $mitra?->id,
                    'activity_type' => 'take_help',
                    'description' => 'Mengambil Bantuan #' . $help->id,
                    'ip_address' => request()?->ip(),
                    'user_agent' => request()?->header('User-Agent'),
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Failed to record PartnerActivity on help taken: ' . $e->getMessage());
            }
        }

        // When help status changes to 'completed' (or localized 'selesai'), credit the mitra's balance
        if ($help->wasChanged('status')) {
            $newStatus = strtolower($help->status ?? '');
            $prevStatus = strtolower($help->getOriginal('status') ?? '');
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
