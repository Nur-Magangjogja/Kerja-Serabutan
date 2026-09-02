<?php

namespace App\Events;

use App\Models\Help;
use App\Models\HelpDispatch;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MitraOfferDispatched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $mitraId;
    public int $dispatchId;
    public int $helpId;
    public string $title;
    public float $amount;
    public int $timeoutSeconds;

    /**
     * Create a new event instance.
     */
    public function __construct(HelpDispatch $dispatch, Help $help, int $timeoutSeconds = 45)
    {
        $this->mitraId        = (int) $dispatch->mitra_id;
        $this->dispatchId     = (int) $dispatch->id;
        $this->helpId         = (int) $help->id;
        $this->title          = (string) $help->title;
        $this->amount         = (float) $help->amount;
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('mitra.' . $this->mitraId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'MitraOfferDispatched';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'dispatch_id'     => $this->dispatchId,
            'help_id'         => $this->helpId,
            'title'           => $this->title,
            'amount'          => $this->amount,
            'timeout_seconds' => $this->timeoutSeconds,
        ];
    }
}
