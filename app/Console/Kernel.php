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
                /**
                 * Things will fail silently if you send a null value to pingBefore() or thenPing()
                 * so unfortunately we need to check if either or both of the variables to be used
                 * as the URL are null and adjust the command accordingly.
                 *
                 * We are still assuming that if there is a value that it is both valid and GET-able.
                 */
                if ($harvest->ping_before_url === null && $harvest->then_ping_url === null) {
                    $schedule->command('harvest:update ' . $harvest->resource . ' --action=' . $harvest->action)
                        ->{$harvest->scheduler_frequency_method}()
                        ->withoutOverlapping();
                }

                if ($harvest->ping_before_url === null && $harvest->then_ping_url !== null) {
                    $schedule->command('harvest:update ' . $harvest->resource . ' --action=' . $harvest->action)
                        ->{$harvest->scheduler_frequency_method}()
                        ->withoutOverlapping()
                        ->thenPing($harvest->then_ping_url);
                }

                if ($harvest->ping_before_url !== null && $harvest->then_ping_url === null) {
                    $schedule->command('harvest:update ' . $harvest->resource . ' --action=' . $harvest->action)
                        ->{$harvest->scheduler_frequency_method}()
                        ->withoutOverlapping()
                        ->pingBefore($harvest->ping_before_url)
                        ->thenPing($harvest->then_ping_url);
                }

            }
        } catch (\Exception $e) {
            // Hopefully we’re only here because we are migrating,
            // which doesn't like the database query above.
        }

    }
}
