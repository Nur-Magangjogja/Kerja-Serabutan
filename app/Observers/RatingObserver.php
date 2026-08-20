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
                'description' => 'Customer memberikan ulasan dan rating',
                'ip_address' => $ip,
                'user_agent' => $ua,
            ]);
        } catch (\Throwable $e) {
            // swallow errors to avoid breaking the app
        }
    }
}
