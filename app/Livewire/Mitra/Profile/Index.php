<?php

namespace App\Livewire\Mitra\Profile;

use App\Models\Help;
use App\Models\Rating;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.mitra')]
class Index extends Component
{
    public function render()
    {
        $user = auth()->user();

        // 1 query agregasi menggantikan 2 query COUNT terpisah
        $helpsAgg = Help::where('mitra_id', $user->id)
            ->selectRaw("COUNT(*) as total_helped")
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_helps", [Help::STATUS_SELESAI])
            ->first();

        // Rating sudah memoized di User model (loadMitraRatingStats) — max 1 query
        $averageRating = $user->mitra_average_rating;
        $totalRatings = $user->mitra_rating_count;

        return view('livewire.mitra.profile.index', [
            'user' => $user,
            'totalHelped' => (int) ($helpsAgg->total_helped ?? 0),
            'completedHelps' => (int) ($helpsAgg->completed_helps ?? 0),
            'averageRating' => round($averageRating, 1),
            'totalRatings' => $totalRatings,
        ]);
    }
}
