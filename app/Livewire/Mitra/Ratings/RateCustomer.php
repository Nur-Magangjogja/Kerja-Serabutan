<?php

namespace App\Livewire\Mitra\Ratings;

use Livewire\Component;
use App\Models\Help;
use App\Models\Rating;

class RateCustomer extends Component
{
    public $help;
    public $rating = 0;
    public $review = '';
    public $showModal = false;
    public $alreadyRated = false;
    public $inline = false;

    protected $rules = [
        'rating' => 'required|integer|min:1|max:5',
        'review' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'rating.required' => 'Rating wajib diisi',
        'rating.min' => 'Rating minimal 1 bintang',
        'rating.max' => 'Rating maksimal 5 bintang',
        'review.max' => 'Review maksimal 500 karakter',
    ];

    public function mount($helpId = null, $inline = false)
    {
        $this->inline = (bool) $inline;
        if ($helpId) {
            $this->help = Help::with('customer')->find($helpId);
        }

        if ($this->help) {
            // Check if mitra already rated this customer
            $this->alreadyRated = Rating::hasRated(
                $this->help->id,
                auth()->id(),
                'mitra_to_customer'
            );
        }
    }

    /**
     * Load help dynamically (used when the component is mounted in a shared modal)
     */
    public function loadHelp($helpId)
    {
        $this->help = Help::with('customer')->find($helpId);
        $this->alreadyRated = false;
        if ($this->help) {
            $this->alreadyRated = Rating::hasRated(
                $this->help->id,
                auth()->id(),
                'mitra_to_customer'
            );
        }
    }

    public function setRating($value)
    {
        $this->rating = $value;
    }

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['rating', 'review']);
    }

    public function submitRating()
    {
        // Validate
        $this->validate();

        // Check if help is completed (accept both 'completed' and 'selesai')
        if (!in_array($this->help->status, ['completed', 'selesai'])) {
            session()->flash('error', 'Hanya bisa memberi rating untuk bantuan yang sudah selesai');
            return;
        }

        // Check if already rated
        if ($this->alreadyRated) {
            session()->flash('error', 'Anda sudah memberikan rating untuk customer ini');
            return;
        }

        // Check if this mitra handled this help
        if ($this->help->mitra_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak berhak memberikan rating untuk bantuan ini');
            return;
        }

        // Create rating
        Rating::create([
            'help_id' => $this->help->id,
            'rater_id' => auth()->id(), // mitra
            'ratee_id' => $this->help->user_id, // customer (user_id di tabel helps)
            'type' => 'mitra_to_customer',
            'rating' => $this->rating,
            'review' => $this->review,
            // Keep legacy columns for backward compatibility
            'user_id' => $this->help->user_id,
            'mitra_id' => auth()->id(),
        ]);

        session()->flash('success', 'Rating berhasil diberikan!');
        
        $this->alreadyRated = true;
        $this->closeModal();

        // Notify parent Livewire listeners that a rating was submitted
        // use emit so the parent component with a listener receives it
        $this->emit('ratingSubmitted', $this->help->id);

        // also dispatch a browser event for any frontend listeners with updated ratings count
        try {
            $help = Help::with('ratings')->find($this->help->id);
            $ratingsCount = $help && $help->ratings ? $help->ratings->count() : 0;
            $this->dispatchBrowserEvent('helpRatingUpdated', [
                'helpId' => $this->help->id,
                'ratings_count' => $ratingsCount,
            ]);
        } catch (\Exception $e) {
            // fail silently if dispatching the browser event isn't possible
        }
    }

    public function render()
    {
        return view('livewire.mitra.ratings.rate-customer');
    }
}

