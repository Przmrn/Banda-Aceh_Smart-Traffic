<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrafficDataUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $statistics;
    public $sourceType; // 'live' or 'static'
    public $sourceId;   // 'cam-01' or 'video_123.mp4'

    /**
     * Create a new event instance.
     */
    public function __construct($statistics, $sourceType = 'live', $sourceId = null)
    {
        $this->statistics = $statistics;
        $this->sourceType = $sourceType;
        $this->sourceId = $sourceId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        // Using a dynamic channel allows the frontend to listen only to the specific source it needs.
        return new Channel('traffic.' . $this->sourceType . '.' . $this->sourceId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'TrafficDataUpdated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'statistics' => $this->statistics,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
        ];
    }
}
