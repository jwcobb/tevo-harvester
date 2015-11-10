<?php

namespace TevoHarvester\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'TevoHarvester\Events\ItemWasStored' => [
            'TevoHarvester\Listeners\RecordItemUpdate',
        ],
        'TevoHarvester\Events\ItemWasDeleted' => [
            'TevoHarvester\Listeners\RecordItemDelete',
        ],
        'TevoHarvester\Events\ResourceUpdateWasCompleted' => [
            'TevoHarvester\Listeners\RecordResourceUpdateLastRunAt',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}
