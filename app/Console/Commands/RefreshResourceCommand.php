<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

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
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->call('harvester:update', [
            'resource'    => $this->argument('resource'),
            '--action'    => $this->option('action'),
            '--startPage' => $this->option('startPage'),
            '--perPage'   => $this->option('perPage'),
            '--lastRunAt' => '2001-01-01',
        ]);
    }
}
