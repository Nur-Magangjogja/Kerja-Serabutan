<?php

namespace App\Observers;

use App\Models\Rating;
use App\Models\PartnerActivity;

class RatingObserver
{
    public function created(Rating $rating): void
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
                    // ignore
                }
            }

            PartnerActivity::create([
                'user_id' => $rating->user_id,
                'activity_type' => 'help_reviewed',
                'description' => 'Customer menilai bantuan #' . ($rating->help_id ?? ''),
                'ip_address' => $ip,
                'user_agent' => $ua,
            ]);
        } catch (\Throwable $e) {
            // swallow errors to avoid breaking the app
        }
    }
}
