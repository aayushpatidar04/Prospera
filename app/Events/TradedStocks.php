<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TradedStocks implements ShouldBroadcastNow
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
            new Channel('traded-stocks'),
        ];
    }

    public function broadcastAs()
    {
        return 'traded-stocks.updated';
    }



    public function broadcastWith()
    {
        $rawTimestamp = $this->data['timestamp'] ?? now();
        $carbon = $rawTimestamp instanceof Carbon ? $rawTimestamp : Carbon::parse($rawTimestamp);

        return [
            'data' => $this->data,
            'timestamp' => $carbon->format('d M Y, h:i:s A'),
        ];
    }
}
