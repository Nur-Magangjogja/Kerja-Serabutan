<?php

namespace App\Notifications;

use App\Models\Help;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RatingReceivedNotification extends Notification
{
    use Queueable;

    public $help;
    public $rating;
    public $customer;

    public function __construct(Help $help, Rating $rating, ?User $customer = null)
    {
        $this->help     = $help;
        $this->rating   = $rating;
        $this->customer = $customer ?? $help->user ?? User::find($help->user_id);
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $customerName = $this->customer?->name ?? 'Customer';
        $starCount    = (int) $this->rating->rating;
        $stars        = str_repeat('⭐', $starCount);
        $reviewSnippet = !empty($this->rating->review) ? "Ulasan: \"{$this->rating->review}\"" : "Tanpa ulasan tertulis.";

        $message = "Customer {$customerName} memberikan Anda rating {$starCount} bintang ({$stars}) untuk pesanan '{$this->help->title}'. {$reviewSnippet}";

        return [
            'type'          => 'rating_received',
            'title'         => "⭐ Rating {$starCount}/5 Diterima dari Customer!",
            'help_id'       => $this->help->id,
            'help_title'    => $this->help->title,
            'rating'        => $starCount,
            'review'        => $this->rating->review,
            'rater_id'      => $this->customer?->id,
            'customer_name' => $customerName,
            'from_name'     => $customerName,
            'message'       => $message,
            'body'          => $message,
        ];
    }
}
