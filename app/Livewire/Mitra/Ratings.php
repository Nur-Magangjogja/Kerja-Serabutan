<?php

namespace App\Livewire\Mitra;

use App\Models\Rating;
use Livewire\WithPagination;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.mitra')]
class Ratings extends Component
{
    use WithPagination;
    // Do not store paginator or complex objects as public properties in Livewire.
    // We'll fetch paginated ratings in render() and pass them to the view.

    public function render()
    {
        $ratings = Rating::where('mitra_id', auth()->id())
            ->with('help.user')
            ->latest()
            ->paginate(10);

        $allRatings = Rating::where('mitra_id', auth()->id())->get();
        $totalRatings = $allRatings->count();
        $averageRating = $totalRatings > 0 ? round($allRatings->avg('rating'), 1) : 0;

        return view('livewire.mitra.ratings.index', compact('ratings', 'totalRatings', 'averageRating'));
    }
}
