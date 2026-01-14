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
    public $sourceType;
    public $sourceId;

    public function __construct($statistics, $sourceType = 'live', $sourceId = null)
    {
        $this->statistics = $statistics;
        $this->sourceType = $sourceType;
        $this->sourceId = $sourceId;
    }

    public function broadcastOn()
    {
        // --- THE FIX IS HERE ---
        // Do NOT use 'traffic.' . $this->sourceType
        // Use this exact string:
        return new Channel('traffic-channel');
    }

    public function broadcastAs()
    {
        return 'TrafficDataUpdated';
    }

    public function broadcastWith()
    {
        return [
            'statistics' => $this->statistics,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
        ];
    }
}
