<?php

namespace App\Jobs;

use App\Events\ResourceUpdateWasCompleted;
use App\Models\Tevo\Harvest;
use Illuminate\Support\Carbon;
use TicketEvolution\Laravel\TEvoFacade as Tevo;

class RefreshResourceJob extends Job
{

    public Carbon $startTime;

    public Harvest $harvest;

    public array $settings;


    /**
     * Create a new job instance.
     */
    public function __construct(Harvest $harvest, array $settings)
    {
        $this->harvest = $harvest;
        $this->settings = $settings;
    }


    /**
     * Execute the job.
     */
    public function handle(Tevo $apiClient): void
    {
        /**
         * Set a $startTime variable to record when we started this script. This time
         * will be stored in the appropriate row of `harvests` so we know what
         * time to use the next time this script runs.
         */
        $this->startTime = Carbon::now();

        foreach ($this->getItemsFromApi($apiClient) as $result) {
            $item = call_user_func($this->harvest->model_class.'::storeFromApi', $result);
        }

        event(new ResourceUpdateWasCompleted($this->harvest, $this->startTime));
    }



}
