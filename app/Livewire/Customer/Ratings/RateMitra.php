<?php

namespace App\Livewire\Customer\Ratings;

use Livewire\Component;
use App\Models\Help;
use App\Models\Rating;

class RateMitra extends Component
{
    public $help;
    public $rating = 0;
    public $review = '';
    public $alreadyRated = false;
    public $userRating = null;

    protected $rules = [
        'rating' => 'required|integer|min:1|max:5',
        'review' => 'nullable|string|max:500',
    ];

    public function mount($helpId)
    {
        $this->help = Help::with(['mitra', 'rating'])->findOrFail($helpId);

        if (!auth()->check() || !auth()->user()->isCustomer()) {
            return;
        }

        $this->userRating = Rating::where('help_id', $this->help->id)
            ->where('rater_id', auth()->id())
            ->first();

        $this->alreadyRated = (bool) $this->userRating;

        if ($this->userRating) {
            $this->rating = (int) $this->userRating->rating;
            $this->review = $this->userRating->review ?? '';
        }
    }

    public function setRating($value)
    {
        if ($this->alreadyRated) {
            return;
        }
        $this->rating = (int) $value;
    }

    public function submitRating()
    {
        if (!auth()->check() || !auth()->user()->isCustomer()) {
            session()->flash('error', 'Hanya akun Customer yang dapat memberikan rating.');
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Hanya akun Customer yang dapat memberikan rating.']);
            return;
        }

        $this->validate();

        if (!$this->help->canBeRated()) {
            session()->flash('error', 'Rating belum dapat diberikan (pesanan belum selesai, dana escrow belum diselesaikan, atau sedang dalam sengketa).');
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Rating belum dapat diberikan untuk pesanan ini.']);
            return;
        }

        if ($this->alreadyRated) {
            session()->flash('error', 'Anda sudah memberikan rating untuk mitra ini.');
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Anda sudah memberikan rating untuk mitra ini.']);
            return;
        }

        if ($this->help->user_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak berhak memberikan rating untuk bantuan ini.');
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Anda tidak berhak memberikan rating untuk bantuan ini.']);
            return;
        }

        $ratingRecord = \Illuminate\Support\Facades\DB::transaction(function () {
            $record = Rating::create([
                'help_id'  => $this->help->id,
                'rater_id' => auth()->id(),
                'ratee_id' => $this->help->mitra_id,
                'type'     => 'customer_to_mitra',
                'rating'   => $this->rating,
                'review'   => $this->review,
            ]);

            $this->help->update(['rating_status' => Help::RATING_STATUS_RATED]);
            return $record;
        });

        $this->userRating = $ratingRecord;
        $this->alreadyRated = true;

        if ($this->help->mitra) {
            try {
                $this->help->mitra->notify(new \App\Notifications\RatingReceivedNotification($this->help, $ratingRecord, auth()->user()));
            } catch (\Throwable $e) {
                \Log::warning('[RateMitra] Failed to notify mitra of rating: ' . $e->getMessage());
            }
        }

        session()->flash('message', 'Terima kasih! Penilaian Anda untuk mitra berhasil disimpan.');
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Terima kasih, rating berhasil dikirim']);
        $this->dispatch('rating-submitted', ['helpId' => $this->help->id]);
        $this->dispatch('ratingSubmitted');
    }

    public function resetForm()
    {
        if (!$this->alreadyRated) {
            $this->reset(['rating', 'review']);
        }
    }

    public function render()
    {
        return view('livewire.customer.ratings.rate-mitra');
    }
}
