<?php

namespace App\Jobs;

use App\Events\ResourceUpdateWasCompleted;
use App\Models\Tevo\Harvest;
use Illuminate\Support\Carbon;
use TicketEvolution\Laravel\TEvoFacade as Tevo;

class UpdateResourceJob extends Job
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


        event(new ResourceUpdateWasCompleted($this->harvest, $this->startTime));
    }


    /**
     * Generator to return individual items from the API while paging through results.
     */
    private function getItemsFromApi(Tevo $apiClient): ?\Generator
    {
        $thisPage = $this->settings['startPage'];

        while ($thisPage !== false) {
            $options = [
                'page'           => $thisPage,
                'per_page'       => $this->settings['perPage'],
                'updated_at.gte' => $this->settings['lastRun']->format('c'),
            ];

            // "deleted" actions use "deleted_at" instead of "updated_at"
            if ($this->harvest->action === 'deleted') {
                $options['deleted_at.gte'] = $options['updated_at.gte'];
                unset($options['updated_at.gte']);
            }

            echo 'Fetching page ' . $thisPage . PHP_EOL;
            $results = $apiClient::{$this->harvest->library_method}($options);
//            dd($results);

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
