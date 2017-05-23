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
                 * Both ping_before_url and then_ping_url could be NULL
                 * in the database but things will fail silently if NULL
                 * is passed to pingBefore() or thenPing().
                 *
                 * This still assumes that if there is a value that it
                 * is both valid and GET-able. If either is not a valid
                 * URL or that URL does not return a response a silent
                 * failure could still occur.
                 */
                if ($harvest->ping_before_url !== null && $harvest->then_ping_url !== null) {
                    $schedule->command('harvest:update ' . $harvest->resource . ' --action=' . $harvest->action)
                        ->{$harvest->scheduler_frequency_method}()
                        ->withoutOverlapping()
                        ->pingBefore($harvest->ping_before_url)
                        ->thenPing($harvest->then_ping_url);
                } elseif ($harvest->ping_before_url !== null && $harvest->then_ping_url === null) {
                    $schedule->command('harvest:update ' . $harvest->resource . ' --action=' . $harvest->action)
                        ->{$harvest->scheduler_frequency_method}()
                        ->withoutOverlapping()
                        ->pingBefore($harvest->ping_before_url);
                } elseif ($harvest->ping_before_url === null && $harvest->then_ping_url !== null) {
                    $schedule->command('harvest:update ' . $harvest->resource . ' --action=' . $harvest->action)
                        ->{$harvest->scheduler_frequency_method}()
                        ->withoutOverlapping()
                        ->thenPing($harvest->then_ping_url);
                } else {
                    // Both are null
                    $schedule->command('harvest:update ' . $harvest->resource . ' --action=' . $harvest->action)
                        ->{$harvest->scheduler_frequency_method}()
                        ->withoutOverlapping();
                }
            }
        } catch (\Exception $e) {
            // Hopefully we are only here because we are migrating,
            // which does not like the database query above.
        }
    }
    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
