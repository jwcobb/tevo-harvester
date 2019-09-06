<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MarkPastEventsDeleted extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'harvester:mark-past-events-deleted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds any events and performances that are in the past and soft-deletes them. This is intended to be run at 12:01AM.';

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
     * @throws Exception
     */
    public function handle()
    {
        $now = new Carbon();
        $endOfYesterday = new Carbon('midnight today');
        $endOfYesterday->subSecond();

        $eventCounts = DB::table('events')->where('occurs_at', '<=', $endOfYesterday)->whereNull('deleted_at')->update(['deleted_at' => $now]);
        $this->info($eventCounts . ' Events were soft-deleted.');

        $performancesCounts = DB::table('performances')->where('occurs_at', '<=', $endOfYesterday)->whereNull('deleted_at')->update(['deleted_at' => $now]);
        $this->info($performancesCounts . ' Performances were soft-deleted.');

    }
}
