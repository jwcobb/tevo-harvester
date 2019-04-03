<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * Avoid MySQL errors when using utf8mb4 by setting the
         * maximum string length to 191.
         *
         * @link https://laravel-news.com/laravel-5-4-key-too-long-error
         * @link https://mathiasbynens.be/notes/mysql-utf8mb4
         */
        Schema::defaultStringLength(191);
    }

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
            return "<?php echo '<span title=\"' . with($lastRunAt)->format('D, M j, Y g:i:s a') . '\">' . with($lastRunAt)->diffForHumans() . '</span>'; ?>";
        });
    }
}
