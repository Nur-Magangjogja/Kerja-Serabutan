<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PartnerLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queue = 'broadcast';

    public int $helpId;
    public int $mitraId;
    public float $partnerLat;
    public float $partnerLng;
    public ?float $customerLat;
    public ?float $customerLng;
    public string $partnerName;
    public string $updatedAt;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $helpId,
        int $mitraId,
        float $partnerLat,
        float $partnerLng,
        ?float $customerLat = null,
        ?float $customerLng = null,
        string $partnerName = 'Mitra'
    ) {
        $this->helpId      = $helpId;
        $this->mitraId     = $mitraId;
        $this->partnerLat  = $partnerLat;
        $this->partnerLng  = $partnerLng;
        $this->customerLat = $customerLat;
        $this->customerLng = $customerLng;
        $this->partnerName = $partnerName;
        $this->updatedAt   = now()->toISOString();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.help.' . $this->helpId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'PartnerLocationUpdated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'helpId'      => $this->helpId,
            'mitraId'     => $this->mitraId,
            'partnerLat'  => $this->partnerLat,
            'partnerLng'  => $this->partnerLng,
            'customerLat' => $this->customerLat,
            'customerLng' => $this->customerLng,
            'partnerName' => $this->partnerName,
            'updatedAt'   => $this->updatedAt,
        ];
    }
}
