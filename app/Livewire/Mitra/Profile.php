<?php

namespace App\Livewire\Mitra;

use App\Models\Help;
use App\Models\Rating;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.mitra')]
class Profile extends Component
{
    public function render()
    {
        $user = auth()->user();

        // Get mitra statistics
        $totalHelped = Help::where('mitra_id', $user->id)->count();
        $completedHelps = Help::where('mitra_id', $user->id)->where('status', 'selesai')->count();
        $averageRating = Rating::where('mitra_id', $user->id)->avg('rating') ?? 0;
        $totalRatings = Rating::where('mitra_id', $user->id)->count();

        return view('livewire.mitra.profile.index', [
            'user' => $user,
            'totalHelped' => $totalHelped,
            'completedHelps' => $completedHelps,
            'averageRating' => round($averageRating, 1),
            'totalRatings' => $totalRatings,
        ]);
    }
}
