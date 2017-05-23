<?php

namespace TevoHarvester\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use TevoHarvester\Jobs\UpdateResourceJob;
use TevoHarvester\Tevo\Harvest;

class RefreshResourceCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'harvester:refresh
                            {resource : The resource to update}
                            {--action=active : “active” or “deleted” (default: active)}
                            {--startPage=1 : The page with which to start (default: 1)}
                            {--perPage=100 : The number of items to retrieve per page (default: 100)}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Performs a full refresh of the specified resource.';


    /**
     * Create a new command instance.
     *
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
            'lastRun'   => new Carbon('2001-01-01')
        ];

        $job = new UpdateResourceJob(
            $harvest,
            $options
        );

        $message = 'Fully refreshing ' . $action . ' ' . $resource . ' ' . $perPage . ' at a time';
        if (isset($lastRun)) {
            $message .= ' with entries updated since ' . $lastRun->format('r');
        }
        $this->info($message);
        $this->dispatch($job);
    }
}
