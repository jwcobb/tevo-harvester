<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\ItemWasStored::class => [
            \App\Listeners\RecordItemUpdate::class,
        ],
        \App\Events\ItemWasDeleted::class => [
            \App\Listeners\RecordItemDelete::class,
        ],
        \App\Events\ResourceUpdateWasCompleted::class => [
            \App\Listeners\RecordResourceUpdateLastRunAt::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
