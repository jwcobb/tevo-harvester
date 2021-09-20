<?php

namespace App\Console\Commands;

use App\Models\Tevo\Harvest;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

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
        $columns = [
            'Resource'    => 'resource',
            'Action'      => 'action',
            'Last Run'    => 'last_run_at',
            'Scheduled'   => 'scheduler_frequency_method',
            'Ping Before' => 'ping_before_url',
            'Ping After'  => 'then_ping_url',
        ];
        $harvests = Harvest::orderBy('resource', 'asc')
            ->orderBy('action', 'asc')
            ->get(array_values($columns));

        $this->table(array_keys($columns), $harvests);
    }
}
