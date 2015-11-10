<?php

namespace TevoHarvester\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use TevoHarvester\Tevo\Harvest;


class ShowHarvesterStatusCommand extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'harvester:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows the status of all available Harvests.';


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
        $harvests = Harvest::orderBy('resource', 'asc')->orderBy('action', 'asc')->get(['resource', 'action', 'last_run_at']);
        foreach ($harvests as $harvest) {
            $lastRunAt = 'Not yet run';
            if ($harvest->last_run_at != null) {
                $lastRunAt = $harvest->last_run_at . ' (' . $harvest->last_run_at->diffForHumans() . ')';
            }
            $data[] = [
                $harvest->resource,
                $harvest->action,
                $lastRunAt,
            ];
        }

        $headers = ['Resource', 'Action', 'Last Run'];

        $this->table($headers, $data);
    }


}
