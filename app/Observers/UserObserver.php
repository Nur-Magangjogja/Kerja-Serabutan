<?php

namespace App\Observers;

use App\Models\User;
use App\Models\PartnerActivity;

class UserObserver
{
    public function updated(User $user): void
    {
        try {
            $ip = null;
            $ua = null;
            if (function_exists('request')) {
                try {
                    $req = request();
                    $ip = $req->ip();
                    $ua = $req->userAgent();
                } catch (\Throwable $e) {
                    // ignore - no request available in some contexts
                }
            }

            // Password changed
            if ($user->wasChanged('password')) {
                PartnerActivity::create([
                    'user_id' => $user->id,
                    'activity_type' => 'password_changed',
                    'description' => 'Pengguna mengubah password',
                    'ip_address' => $ip,
                    'user_agent' => $ua,
                ]);
                // don't duplicate with profile update even if other fields changed
                return;
            }

            // Profile fields changed
            $fields = ['name', 'phone', 'city_id', 'bio', 'email'];
            foreach ($fields as $f) {
                if ($user->wasChanged($f)) {
                    PartnerActivity::create([
                        'user_id' => $user->id,
                        'activity_type' => 'profile_updated',
                        'description' => 'Pengguna memperbarui data diri',
                        'ip_address' => $ip,
                        'user_agent' => $ua,
                    ]);
                    return;
                }
            }
        } catch (\Throwable $e) {
            // Avoid breaking the app if logging fails
        }
    }
}
