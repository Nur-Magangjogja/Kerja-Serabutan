<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Help;
use App\Models\Rating;

class RateMitra extends Component
{
    public $help;
    public $rating = 0;
    public $review = '';
    public $alreadyRated = false;

    protected $rules = [
        'rating' => 'required|integer|min:1|max:5',
        'review' => 'nullable|string|max:500',
    ];

    public function mount($helpId)
    {
        $this->help = Help::with('mitra')->findOrFail($helpId);

        if (!auth()->check() || !auth()->user()->isCustomer()) {
            $this->alreadyRated = true;
            return;
        }

        $this->alreadyRated = Rating::hasRated(
            $this->help->id,
            auth()->id(),
            'customer_to_mitra'
        );
    }

    public function setRating($value)
    {
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

        if (!in_array($this->help->status, ['completed', 'selesai'])) {
            session()->flash('error', 'Hanya bisa memberi rating untuk bantuan yang sudah selesai.');
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Hanya bisa memberi rating untuk bantuan yang sudah selesai.']);
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

        $ratingRecord = Rating::create([
            'help_id' => $this->help->id,
            'rater_id' => auth()->id(),
            'ratee_id' => $this->help->mitra_id,
            'type' => 'customer_to_mitra',
            'rating' => $this->rating,
            'review' => $this->review,
            // Legacy fields
            'user_id' => auth()->id(),
            'mitra_id' => $this->help->mitra_id,
        ]);

        if ($this->help->mitra) {
            try {
                $this->help->mitra->notify(new \App\Notifications\RatingReceivedNotification($this->help, $ratingRecord, auth()->user()));
            } catch (\Throwable $e) {
                \Log::warning('[RateMitra] Failed to notify mitra of rating: ' . $e->getMessage());
            }
        }

        $this->alreadyRated = true;
        session()->flash('message', 'Terima kasih! Rating & ulasan Anda berhasil dikirim.');
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Terima kasih, rating berhasil dikirim']);
        $this->dispatch('rating-submitted', ['helpId' => $this->help->id]);
        $this->dispatch('ratingSubmitted');
    }

    public function resetForm()
    {
        $this->reset(['rating', 'review']);
    }

    public function render()
    {
        return view('livewire.customer.rate-mitra');
    }
}
