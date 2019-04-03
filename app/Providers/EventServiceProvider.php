<?php

namespace App\Providers;

use App\Events\ItemWasDeleted;
use App\Events\ItemWasStored;
use App\Events\ResourceUpdateWasCompleted;
use App\Listeners\RecordItemDelete;
use App\Listeners\RecordItemUpdate;
use App\Listeners\RecordResourceUpdateLastRunAt;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class                 => [
            SendEmailVerificationNotification::class,
        ],
        ItemWasStored::class              => [
            RecordItemUpdate::class,
        ],
        ItemWasDeleted::class             => [
            RecordItemDelete::class,
        ],
        ResourceUpdateWasCompleted::class => [
            RecordResourceUpdateLastRunAt::class,
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
