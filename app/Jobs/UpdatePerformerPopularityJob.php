<?php

namespace TevoHarvester\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Facades\Event;
use TevoHarvester\Events\ResourceUpdateWasCompleted;
use TevoHarvester\Tevo\Harvest;
use TicketEvolution\Laravel\TEvoFacade as Tevo;

class UpdatePerformerPopularityJob extends Job implements SelfHandling
{

    /**
     * @var
     */
    public $startTime;

    /**
     * @var
     */
    public $harvest;

    /**
     * @var
     */
    public $settings;

    /**
     * @var
     */
    public $last_category_id;


    /**
     * Create a new job instance.
     *
     * @param Harvest    $harvest
     * @param            $category_id
     * @param            $last_category_id
     */
    public function __construct(Harvest $harvest, $category_id, $last_category_id)
    {
        $this->harvest = $harvest;
        $this->last_category_id = $last_category_id;
        $this->settings = [
            'startPage'                 => (int)1,
            'perPage'                   => (int)100,
            'order_by'                  => 'performers.popularity_score DESC',
            'only_with_upcoming_events' => (int)1,
            'category_tree'             => (int)1,
            'category_id'               => (int)$category_id,
        ];
    }


    /**
     * Execute the job.
     *
     * @param Tevo $apiClient
     */
    public function handle(Tevo $apiClient)
    {
        /**
         * Set a $startTime variable to record when we started this script. This time
         * will be stored in the appropriate row of `harvests` so we know what
         * time to use the next time this script runs.
         */
        $this->startTime = Carbon::now();

        foreach ($this->getItemsFromApi($apiClient) as $result) {
            $item = call_user_func($this->harvest->model_class . '::storeFromApi', $result);
        }

        // Only if this is the last Category to update fire the Event
        if ($this->settings['category_id'] === $this->last_category_id) {
            Event::fire(new ResourceUpdateWasCompleted($this->harvest, $this->startTime));
        }
    }


    /**
     * @param Tevo $apiClient
     *
     * @return \Generator
     */
    private function getItemsFromApi(Tevo $apiClient)
    {
        $options = $this->settings;

        $results = $apiClient::{$this->harvest->library_method}($options);

        foreach ($results[$this->harvest->resource] as $result) {
            yield $result;
        }

    }


}
