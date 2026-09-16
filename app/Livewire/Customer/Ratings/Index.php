<?php

namespace App\Livewire\Customer\Ratings;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Rating;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $perPage = 10;

    public function render()
    {
        $userId = auth()->id();

        // Customer's given ratings to mitras
        $baseQuery = Rating::with(['ratee', 'help'])
            ->where('rater_id', $userId)
            ->where(function ($q) {
                $q->where('type', 'customer_to_mitra')
                    ->orWhereNull('type');
            });

        $ratings = $baseQuery->latest()->paginate($this->perPage);
        $totalRatings = $ratings->total();
        $averageRating = $totalRatings > 0 
            ? round((float) (clone $baseQuery)->avg('rating'), 1) 
            : 0.0;

        return view('livewire.customer.ratings.index', [
            'ratings' => $ratings,
            'totalRatings' => $totalRatings,
            'averageRating' => $averageRating,
        ]);
    }
}
