<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     * Execute the console command.
     */
    public function handle(): void
    {
        $now = new Carbon();
        $endOfYesterday = Carbon::parse('midnight today')->subSecond();

        $eventCounts = DB::table('events')->where('occurs_at', '<=', $endOfYesterday)
            ->whereNull('deleted_at')->update(['deleted_at' => $now]);
        $this->info($eventCounts.' Events were soft-deleted.');
        Log::info($eventCounts.' Events were soft-deleted.');

        $performancesCounts = DB::table('performances')->where('occurs_at', '<=', $endOfYesterday)
            ->whereNull('deleted_at')->update(['deleted_at' => $now]);
        $this->info($performancesCounts.' Performances were soft-deleted.');
        Log::info($performancesCounts.' Performances were soft-deleted.');

    }
}
