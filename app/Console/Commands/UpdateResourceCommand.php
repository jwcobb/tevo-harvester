<?php

namespace App\Console\Commands;

use App\Jobs\UpdatePerformerPopularityJob;
use App\Jobs\UpdateResourceJob;
use App\Tevo\Category;
use App\Tevo\Harvest;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UpdateResourceCommand extends Command
{
    use DispatchesJobs;

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
                            {--lastRun= : The timestamp to use with “updated_at”}
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
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('action') === 'popularity') {
            $this->handlePopularity();
        } else {
            $this->handleUpdate();
        }
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    protected function handleUpdate()
    {
        $resource = $this->argument('resource');
        $action = $this->option('action');
        $startPage = (int)$this->option('startPage');
        $perPage = (int)$this->option('perPage');

        try {
            $harvest = Harvest::where('resource', $resource)->where('action', $action)->firstOrFail();
        } catch (\Exception $e) {
            $this->info('There is no existing action for updating ' . ucwords($action) . ' ' . ucwords($resource) . '.');
            exit('Nothing was updated.');
        }

        $options = [
            'startPage' => $startPage,
            'perPage'   => $perPage,
            'lastRun'   => $harvest->last_run_at
        ];

        // If a lastRun was given use that
        // OR if this has never been run before use 2001-01-01
        if (!empty($this->option('lastRun'))) {
            $options['lastRun'] = new Carbon($this->option('lastRun'));
        } elseif (is_null($options['lastRun'])) {
            $options['lastRun'] = new Carbon('2001-01-01');
        }

        $job = new UpdateResourceJob(
            $harvest,
            $options
        );

        $message = 'Updating ' . $action . ' ' . $resource . ' ' . $perPage . ' at a time';
        if (isset($lastRun)) {
            $message .= ' with entries updated since ' . $lastRun->format('r');
        }
        $this->info($message);
        $this->dispatch($job);
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    protected function handlePopularity()
    {
        $resource = 'performers';
        $action = 'popularity';

        try {
            $harvest = Harvest::where('resource', $resource)->where('action', $action)->firstOrFail();
        } catch (\Exception $e) {
            $this->info('There is no existing action for updating ' . ucwords($action) . ' ' . ucwords($resource) . '.');
            exit('Nothing was updated.');
        }

        /**
         * Get all the categories and then loop through them creating an
         * UpdatePerformerPopularityJob for each one.
         */
        try {
            $categories = Category::active()->orderBy('id')->get();
        } catch (\Exception $e) {
            abort(404, 'There are no categories yet. Please ensure you have run the Active Categories job.');
        }

        $message = 'Updating the popularity_score for the 100 most popular Performers in each Category.';
        $this->info($message);

        // Get the last category_id so we can later detect that the Job for that
        // category_id has completed in order to know to fire the ResourceUpdateWasCompleted Event
        $last_category_id = $categories->last()->id;

        foreach ($categories as $category) {
            $job = new UpdatePerformerPopularityJob($harvest, $category->id, $last_category_id);
            $this->dispatch($job);
        }
    }
}
