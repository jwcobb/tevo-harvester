<?php

namespace App\Listeners;

use App\Events\ItemWasStored;

class RecordItemUpdate
{
    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        //
    }


    /**
     * Handle the event.
     *
     * @param  ItemWasStored $event
     *
     * @return void
     */
    public function handle(ItemWasStored $event)
    {
        //
    }
}
