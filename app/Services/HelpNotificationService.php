<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Help;
use App\Models\PartnerActivity;
use App\Models\User;
use App\Notifications\HelpStatusNotification;
use App\Notifications\HelpTakenNotification;
use Illuminate\Support\Facades\Log;

class HelpNotificationService
{
    /**
     * Catat aktivitas mitra / customer ke PartnerActivity & ActivityLog.
     */
    public function logActivity($userId, $helpId, string $activityType, ?string $description = null, ?string $photo = null): void
    {
        try {
            PartnerActivity::create([
                'user_id'       => $userId,
                'help_id'       => $helpId,
                'activity_type' => $activityType,
                'description'   => $description,
                'photo'         => $photo,
                'ip_address'    => function_exists('request') ? request()?->ip() : null,
                'user_agent'    => function_exists('request') ? request()?->header('User-Agent') : null,
            ]);

            ActivityLog::record(
                $userId,
                $activityType,
                $description ?? "Aktivitas bantuan"
            );
        } catch (\Throwable $e) {
            Log::warning('[HelpNotificationService] logActivity failed: ' . $e->getMessage(), [
                'user_id'       => $userId,
                'help_id'       => $helpId,
                'activity_type' => $activityType,
            ]);
        }
    }

    /**
     * Notifikasi ke customer saat bantuan diambil mitra.
     */
    public function notifyHelpTaken(Help $help, User $mitra): void
    {
        try {
            $customer = $help->user ?? User::find($help->user_id);
            if ($customer) {
                $customer->notify(new HelpTakenNotification($help, $mitra));
            }
        } catch (\Throwable $e) {
            Log::warning('[HelpNotificationService] Failed to send HelpTakenNotification: ' . $e->getMessage());
        }
    }

    /**
     * Notifikasi perubahan status bantuan.
     */
    public function sendStatusNotification(Help $help, string $newStatus, ?User $recipient, ?User $actor): void
    {
        try {
            if (!$recipient) return;

            $recipient->notify(new HelpStatusNotification(
                $help,
                $help->status,
                $newStatus,
                $actor
            ));
        } catch (\Throwable $e) {
            Log::warning('[HelpNotificationService] sendStatusNotification failed: ' . $e->getMessage(), [
                'help_id'    => $help->id,
                'new_status' => $newStatus,
            ]);
        }
    }
}
