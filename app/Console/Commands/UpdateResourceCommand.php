<?php

namespace App\Console\Commands;

use App\Events\ResourceUpdateWasCompleted;
use App\Jobs\UpdatePerformerPopularityJob;
use App\Jobs\UpdateResourceJob;
use App\Models\Tevo\Category;
use App\Models\Tevo\Harvest;
use GuzzleHttp\Command\Result;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use TicketEvolution\Laravel\TEvoFacade as TEvoClient;

class UpdateResourceCommand extends Command
{
    use DispatchesJobs;

    /**
     * Array of resources for which we should use cursor pagination vs regular pagination.
     * Not all resources support using updated_at.gte in API requests.
     */
    private const USE_CURSOR_PAGINATION = [
//        'brokerages',
//        'categories',
//        'configurations',
        'events',
//        'offices',
        'performers',
        'venues',
    ];
    private TEvoClient $apiClient;
    private Carbon $startTime;
    private Harvest|Builder|Model $harvest;
    private string|array|null $resource;
    private string|array|bool|null $action;
    private int $startPage;
    private int $perPage;
    private Carbon $lastRunAt;
//    private array $options;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'harvester:update
                            {resource : The resource to update}
                            {--action=active : “active” or “deleted” (default: active). “popularity” is supported for Performers only}
                            {--startPage=1 : The page with which to start (default: 1)}
                            {--perPage=100 : The number of items to retrieve per page (default: 100)}
                            {--lastRunAt= : The timestamp to use with “updated_at”}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Performs an update of the specified resource.';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        /**
         * Set a $startTime variable to record when we started this script. This time
         * will be stored in the appropriate row of `harvests` so we know what
         * time to use the next time this script runs.
         */
        $this->startTime = Carbon::now();

        $this->apiClient = new TEvoClient();
    }


    /**
     * Base handler to decide which method to use.
     */
    public function handle(): void
    {
        $this->startPage = (int) $this->option('startPage');
        $this->perPage = (int) $this->option('perPage');

        if ($this->option('action') === 'popularity') {
            $this->resource = 'performers';
            $this->action = 'popularity';

            $this->handlePopularity();
        } else {
            $this->resource = $this->argument('resource');
            $this->action = $this->option('action');

            $this->handleUpdate();
        }
    }


    /**
     * Perform an update of a resource.
     */
    protected function handleUpdate(): void
    {
        $this->setHarvest();
        $this->setLastRunAt();


        $message = 'Updating '.$this->action.' '.$this->resource.' '.$this->perPage.' at a time with entries updated since '.$this->lastRunAt->toIso8601String();
        $this->info($message);
        Log::info($message);

        if (in_array($this->resource, self::USE_CURSOR_PAGINATION, true)) {
            foreach ($this->getItemsFromApiWithCursorPagination() as $result) {
                $item = call_user_func($this->harvest->model_class.'::storeFromApi', $result);
            }
        } else {
            foreach ($this->getItemsFromApiWithStandardPagination() as $result) {
                $item = call_user_func($this->harvest->model_class.'::storeFromApi', $result);
            }
        }


        $this->completed();
    }


    /**
     * Create jobs to update the most popular performers for each category.
     */
    protected function handlePopularity(): void
    {
        $this->setHarvest();
        $this->setLastRunAt();

        /**
         * Get all the categories and then loop through them creating an
         * UpdatePerformerPopularityJob for each one.
         */
        try {
            $categories = Category::active()->orderBy('id')->get();
        } catch (\Exception $e) {
            exit('There are no categories yet. Please ensure you have run the Active Categories job.');
        }

        $message = 'Updating the popularity_score for the 100 most popular Performers in each Category.';
        $this->info($message);
        Log::info($message);


        $progressBar = $this->output->createProgressBar($categories->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->start();
        foreach ($categories as $category) {
            foreach ($this->getPopularPerformersFromApiByCategory($category) as $result) {
                $item = call_user_func($this->harvest->model_class . '::storeFromApi', $result);
            }
            $progressBar->advance();
        }
        $progressBar->finish();


        $this->completed();
    }


    private function setHarvest(): void
    {
        try {
            $this->harvest = Harvest::where('resource', $this->resource)->where('action', $this->action)->firstOrFail();
        } catch (\Exception $e) {
            exit('There is no existing action for updating '.ucwords($this->action).' '.ucwords($this->resource).'.');
        }
    }


    private function setLastRunAt(): void
    {
        // If a lastRun was given use that
        // OR if this has never been run before use 2001-01-01
        if (!empty($this->option('lastRunAt'))) {
            $this->lastRunAt = new Carbon($this->option('lastRunAt'));
        } else {
            $this->lastRunAt = $this->harvest->last_run_at ?? new Carbon('2001-01-01');
        }
    }


    /**
     * Generator to return individual items from the API while paging through results
     * using standard pagination.
     */
    private function getItemsFromApiWithStandardPagination(): ?\Generator
    {
        $thisPage = $this->startPage;

        $progressBar = $this->output->createProgressBar(0);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        while ($thisPage !== false) {
            $options = [
                'page'           => $thisPage,
                'per_page'       => $this->perPage,
                'updated_at.gte' => $this->lastRunAt->toIso8601String(),
            ];

            // "deleted" actions use "deleted_at" instead of "updated_at"
            if ($this->harvest->action === 'deleted') {
                $options['deleted_at.gte'] = $options['updated_at.gte'];
                unset($options['updated_at.gte']);
            }

            $results = $this->fetchApiResults($options);


            if (!isset($lastPage)) {
                if ($results['total_entries'] === 0) {
                    $this->line('There are no '.$this->harvest->action.' '.$this->resource.' that have been updated since '.$this->lastRunAt->toIso8601String().'.');
                    return;
                }

                $lastPage = (int) ceil($results['total_entries'] / $results['per_page']);
                $progressBar->start($lastPage);
            }

            $progressBar->advance();


            foreach ($results[$this->harvest->resource] as $result) {
                yield $result;
            }


            if ($thisPage === $lastPage) {
                /**
                 * If this is the last page, exit the loop.
                 *
                 * Luckily, the response for categories endpoint includes {"per_page":136,"total_entries":136,…}
                 * which will result in $lastPage being 1 and correctly exiting the loop even though categories
                 * is not paginated and would loop endlessly because "page" and "per_page" is ignored
                 * by the categories endpoint.
                 */
                $thisPage = false;
                $progressBar->finish();
            } else {
                ++$thisPage;
            }
        }
    }


    /**
     * Generator to return individual items from the API while paging through results.
     *
     * Looping page by page is taxing on the database especially when going many pages deep
     * so instead of doing that we'll use cursor pagination based on the updated_at value.
     *
     * This should prevent having to go many pages deep, but sometimes if more than 100 events
     * have the same updated_at value we will still need to fetch additional pages until we end
     * on one that has a different value for updated_at. Since that should be rare and likely only
     * a few pages deep the load on the database should be less than if we paged through the entire set.
     *
     * This method does result in getting some results more twice. For example if the page started with
     * a value of 2014-05-08T13:02:42Z and ended with 2014-05-08T13:02:45Z the next page will begin
     * with 2014-05-08T13:02:4Z thus fetching any items from the previous page with 2014-05-08T13:02:42Z
     * a second time. Since there are no controls in the API to add a secondary value like id this will
     * have to do.
     */
    private function getItemsFromApiWithCursorPagination(): ?\Generator
    {
        $updatedAtStart = $this->lastRunAt;
        $thisPage = $this->startPage;

        $progressBar = $this->output->createProgressBar(0);
        $progressBar->setFormat(" %current%/%max%ish [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%\n  %message%");
        $progressBar->setMessage('The total number of pages is only an estimate.');


        while ($thisPage !== false) {
            $options = [
                'page'           => $thisPage,
                'per_page'       => $this->perPage,
                'updated_at.gte' => $updatedAtStart->toIso8601String(),
                'order_by'       => $this->harvest->resource.'.updated_at ASC',
            ];

            // "deleted" actions use "deleted_at" instead of "updated_at"
            if ($this->harvest->action === 'deleted') {
                $options['deleted_at.gte'] = $options['updated_at.gte'];
                unset($options['updated_at.gte']);
                if ($this->resource === 'events') {
                    // order_by is not allowed in deleted events request
                    unset($options['order_by']);
                }
            }

            $results = $this->fetchApiResults($options);


            if (!isset($lastPage)) {
                if ($results['total_entries'] === 0) {
                    $this->line('There are no '.$this->harvest->action.' '.$this->resource.' that have been updated since '.$this->lastRunAt->toIso8601String().'.');
                    return;
                }

                $lastPage = (int) ceil($results['total_entries'] / $results['per_page']);
                $progressBar->start($lastPage);
            }

            $progressBar->advance();


            foreach ($results[$this->harvest->resource] as $result) {
                yield $result;
            }


            /**
             * If there are no results then exit.
             *
             * Additionally, the categories resource is not paginated and
             * therefore will loop endlessly if we don’t forcibly exit.
             */
            if (empty($results[$this->harvest->resource] || $this->harvest->resource === 'categories')) {
                $thisPage = false;
                $progressBar->finish();
            } else {
                /**
                 * If the last result from the current page has the same updated_at
                 * as the first one ($updatedAtStart) then we need to get another page
                 * because it looks like all of the current page of results have the same updated_at.
                 *
                 * If the last result updated_at is different use that as the new $updatedAtStart
                 * and ensure we are fetching page 1.
                 */
                if ($this->harvest->action === 'deleted') {
                    $lastResultCreatedAt = Carbon::parse($result['deleted_at']);
                } else {
                    $lastResultCreatedAt = Carbon::parse($result['updated_at']);
                }
                if ($updatedAtStart->equalTo($lastResultCreatedAt)) {
                    ++$thisPage;
                } else {
                    $updatedAtStart = $lastResultCreatedAt;
                    $thisPage = 1;
                }
            }
        }
    }


    private function getPopularPerformersFromApiByCategory(Category $category): \Generator
    {
        $options = [
            'startPage'                 => (int)1,
            'perPage'                   => (int)100,
            'order_by'                  => 'performers.popularity_score DESC',
            'only_with_upcoming_events' => (int)1,
            'category_tree'             => (int)1,
            'category_id'               => (int)$category->id,
        ];

        $results = $this->fetchApiResults($options);

        foreach ($results[$this->harvest->resource] as $result) {
            yield $result;
        }
    }


    private function completed(): void
    {
        Log::info('Completed updating '.$this->action.' '.$this->resource.' '.$this->perPage.' at a time with entries updated since '.$this->lastRunAt->toIso8601String());

        event(new ResourceUpdateWasCompleted($this->harvest, $this->startTime));
    }


    private function fetchApiResults(array $options): Result
    {
        try {
            $results = $this->apiClient::{$this->harvest->library_method}($options);
            Log::debug(__METHOD__.': API results retrieved.', [
                'clientLibraryMethod' => $this->harvest->library_method,
                'options'             => $options,
                'results'             => $results,
            ]);
            return $results;
        } catch (\Exception $e) {
            Log::error(__METHOD__.': Error retrieving API results.', [
                'clientLibraryMethod' => $this->harvest->library_method,
                'options'             => $options,
                'exception'           => $e,
            ]);
            exit('Error retrieving API results.'.$e->getMessage());
        }
    }
}
