<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TopStocksUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */

    public $sector;
    public $category;
    public $stocks;


    public function __construct($sector, $category, $stocks)
    {
        $this->sector = $sector;
        $this->category = $category;
        $this->stocks = $stocks;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("top-stocks.{$this->sector}.{$this->category}"),
        ];
    }

    public function broadcastAs()
    {
        return 'TopStocksUpdated';
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->stocks, 
        ];
    } 
}
