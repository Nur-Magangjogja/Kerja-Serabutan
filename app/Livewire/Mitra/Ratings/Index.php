<?php

namespace App\Livewire\Mitra\Ratings;

use App\Models\Rating;
use Livewire\WithPagination;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.mitra')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $perPage = 10;

    public function render()
    {
        $mitraId = auth()->id();

        $baseQuery = Rating::with(['rater', 'help'])
            ->where('ratee_id', $mitraId)
            ->where(function ($q) {
                $q->where('type', 'customer_to_mitra')
                  ->orWhereNull('type');
            });

        $ratings = (clone $baseQuery)->latest()->paginate($this->perPage);
        $totalRatings = (clone $baseQuery)->count();
        $averageRating = (clone $baseQuery)->avg('rating') ?: 0;
        $averageRating = round($averageRating, 1);

        return view('livewire.mitra.ratings.index', compact('ratings', 'totalRatings', 'averageRating'));
    }
}
