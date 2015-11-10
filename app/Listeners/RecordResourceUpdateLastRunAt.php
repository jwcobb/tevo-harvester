<?php

namespace TevoHarvester\Listeners;

use TevoHarvester\Events\ResourceUpdateWasCompleted;
use TevoHarvester\Tevo\Harvest;

class RecordResourceUpdateLastRunAt
{
    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        //
    }


    /**
     * Handle the event.
     *
     * @param  ResourceUpdateWasCompleted $event
     *
     * @return void
     */
    public function handle(ResourceUpdateWasCompleted $event)
    {
        $status = Harvest::where('resource', '=', $event->harvest->resource)->where('action', '=', $event->harvest->action)->firstOrFail();
        $status->last_run_at = $event->startTime;
        $status->save();
    }
}
