<?php

namespace TevoHarvester\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Custom Blade directive for checking when a Harvest was last run
         * and returning a formatted string.
         */
        Blade::directive('lastrundiff', function ($lastRunAt) {
            return "<?php echo '<span title=\"' . with{$lastRunAt}->format('D, M j, Y g:i:s a') . '\">' . with{$lastRunAt}->diffForHumans() . '</span>'; ?>";
        });
    }


    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
            /*
             * Only use Laravel Debugbar in local environment
             */
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
            $this->app->alias(\Barryvdh\Debugbar\ServiceProvider::class, 'Debugbar');

            /*
             * Only use LaravelIdeHelper in local environment
             */
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
