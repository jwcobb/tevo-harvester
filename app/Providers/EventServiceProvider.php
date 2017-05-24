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
        'App\Events\ItemWasStored' => [
            'App\Listeners\RecordItemUpdate',
        ],
        'App\Events\ItemWasDeleted' => [
            'App\Listeners\RecordItemDelete',
        ],
        'App\Events\ResourceUpdateWasCompleted' => [
            'App\Listeners\RecordResourceUpdateLastRunAt',
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
