<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateNifty50 implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('update-nifty50'),
        ];
    }

    public function broadcastAs()
    {
        return 'update-nifty50';
    }

    public function broadcastWith()
    {
        $rawTimestamp = $this->data['lastupd'] ?? now();

        $carbon = $rawTimestamp instanceof Carbon ? $rawTimestamp : Carbon::parse($rawTimestamp);

        return [
            'price' => $this->data['pricecurrent'], 
            'change' => $this->data['CHANGE'], 
            'percent' => $this->data['pricepercentchange'], 
            'lastupd' => $carbon->format('d M Y, h:i:s A'),
        ];
    }
}
