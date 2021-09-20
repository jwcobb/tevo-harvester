<?php

namespace App\Models\Tevo;

use App\Events\ItemWasDeleted;
use App\Events\ItemWasStored;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait StoresFromApi
{
    /**
     * Take a result item from a Ticket Evolution API request,
     * massage it into form and save() it, thus INSERTing or UPDATEing
     * it as necessary.
     */
    public static function storeFromApi($result): ?static
    {
        if (!empty($result['deleted_at'])) {
            $item = self::deleteFromApi($result);
        } else {
            $result = self::mutateApiResult($result);

            $item = self::findOrNewWithTrashed((int) $result['id']);

            $item->fill($result);

            if ($item->save()) {
                Log::info('Item was saved', $item->toArray());
                // Fire an event if an INSERT or UPDATE was actually performed
                event(new ItemWasStored($item));
            }

            $item = self::postSave($item, $result);
        }

        return $item;
    }


    /**
     * If an item is already stored locally delete() that item.
     * Otherwise, just return null.
     */
    protected static function deleteFromApi(array $result)
    {
        $item = static::find((int) $result['id']);
        /**
         * If there is no a matching item there is nothing to do.
         * Either this item was already deleted or it was never INSERTed.
         * If item was never INSERTed do not INSERT now because the data
         * received with deleted items is often too incomplete to properly save.
         */
        if ($item) {
            $item->tevo_deleted_at = new Carbon($result['deleted_at']);

            /**
             * If we have a deleted_at value then we are deleting the item
             * but we need to ensure that we save() it first to record some
             * data and to ensure it actually even exists. We do this via
             * the saveThenDelete() method which does not trigger any of the
             * saving events (but it does trigger the deleting events).
             */
            $item->saveThenDelete();

            // Fire an event that it was deleted
            event(new ItemWasDeleted($item));
        }

        return $item;
    }
}
