<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use App\Tevo\Harvest;

class ResourceUpdateWasCompleted extends Event
{
    use SerializesModels;

    /**
     * @var
     */
    public $harvest;
    /**
     * @var Carbon
     */
    public $startTime;


    /**
     * Create a new event instance.
     *
     * @param Harvest $harvest
     * @param Carbon  $startTime
     */
    public function __construct(Harvest $harvest, Carbon $startTime)
    {
        $this->harvest = $harvest;
        $this->startTime = $startTime;
    }


    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
