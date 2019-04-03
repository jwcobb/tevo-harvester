<?php

namespace App\Jobs;

use App\Events\ResourceUpdateWasCompleted;
use App\Tevo\Harvest;
use Carbon\Carbon;
use TicketEvolution\Laravel\TEvoFacade as Tevo;

class UpdateResourceJob extends Job
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
     * Create a new job instance.
     *
     * @param Harvest $harvest
     * @param         $settings
     */
    public function __construct(Harvest $harvest, $settings)
    {
        $this->harvest = $harvest;
        $this->settings = $settings;
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

        event(new ResourceUpdateWasCompleted($this->harvest, $this->startTime));
    }


    /**
     * @param Tevo $apiClient
     *
     * @return \Generator
     */
    private function getItemsFromApi(Tevo $apiClient)
    {
        $thisPage = $this->settings['startPage'];

        while ($thisPage !== false) {
            $options = [
                'page'           => $thisPage,
                'per_page'       => $this->settings['perPage'],
                'updated_at.gte' => $this->settings['lastRun']->format('c'),
            ];

            // "deleted" actions use "deleted_at" instead of "updated_at"
            if ($this->harvest->action == 'deleted') {
                $options['deleted_at.gte'] = $options['updated_at.gte'];
                unset($options['updated_at.gte']);
            }

            echo 'Fetching page ' . $thisPage . PHP_EOL;
            $results = $apiClient::{$this->harvest->library_method}($options);

            $thisPage = (!empty($results[$this->harvest->resource])) ? ++$thisPage : false;

            foreach ($results[$this->harvest->resource] as $result) {
                yield $result;
            }

            // The categories resource is not paginated and therefore will never
            // loop endlessly if we don’t forcibly exit.
            if ($this->harvest->resource === 'categories') {
                $thisPage = false;
            }
        }
    }
}
