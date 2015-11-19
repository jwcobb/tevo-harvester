<?php

namespace TevoHarvester\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use TevoHarvester\Tevo\Harvest;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \TevoHarvester\Console\Commands\ShowHarvesterStatusCommand::class,
        \TevoHarvester\Console\Commands\UpdateResourceCommand::class,
        \TevoHarvester\Console\Commands\RefreshResourceCommand::class,
    ];


    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /**
         * Create the schedule based upon what is in the `harvests` table
         */
        try {
            $harvests = Harvest::all();

            foreach ($harvests as $harvest) {
                $schedule->command('harvest:update ' . $harvest->resource . ' --action=' . $harvest->action)
                    ->{$harvest->scheduler_frequency_method}()
                    ->withoutOverlapping();

                /**
                 * Both ping_before_url and then_ping_url could be NULL
                 * in the database but things will fail silently if NULL
                 * is passed to pingBefore() or thenPing().
                 *
                 * We are still assuming that if there is a value that it
                 * is both valid and GET-able, which could also cause
                 * a silent failure.
                 */
                if ($harvest->ping_before_url !== null) {
                    $schedule->pingBefore($harvest->ping_before_url);
                }

                if ($harvest->then_ping_url !== null) {
                    $schedule->thenPing($harvest->then_ping_url);
                }
            }
        } catch (\Exception $e) {
            // Hopefully we’re only here because we are migrating,
            // which doesn’t like the database query above.
        }

    }
}
