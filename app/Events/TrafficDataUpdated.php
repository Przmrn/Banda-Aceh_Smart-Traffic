<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrafficDataUpdated implements ShouldBroadcastNow // <-- Implement ini
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $statistics; // Data yang ingin kita kirim

    public function __construct($statistics)
    {
        $this->statistics = $statistics;
    }

    /**
     * Tentukan channel (saluran) siaran.
     */
    public function broadcastOn(): Channel | array
    {
        // 'traffic-channel' adalah nama "walkie-talkie" kita
        return new Channel('traffic-channel');
    }
}
