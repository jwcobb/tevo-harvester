<?php namespace App\Tevo;

use App\Events\ItemWasDeleted;
use App\Events\ItemWasStored;
use Carbon\Carbon;

trait StoresFromApi
{
    /**
     * Take a result item from a Ticket Evolution API request,
     * massage it into form and save() it, thus INSERTing or UPDATEing
     * it as necessary.
     *
     * @param $result
     *
     * @return static
     * @throws \Exception
     */
    public static function storeFromApi($result)
    {
        if (!empty($result['deleted_at'])) {
            $item = self::deleteFromApi($result);
        } else {
            $result = self::mutateApiResult($result);

            $item = self::findOrNewWithTrashed((int)$result['id']);

            $item->fill($result);

            if ($item->save()) {
                // Fire an event if an INSERT or UPDATE was actually performed
                event(new ItemWasStored($item));
            }

            $item = self::postSave($item, $result);
        }

        return $item;
    }


    /**
     * If an item is is already stored locally delete() that item.
     * Otherwise, just return null.
     *
     * @param array $result
     *
     * @return static|null
     * @throws \Exception
     */
    protected static function deleteFromApi(array $result)
    {
        $item = static::find((int)$result['id']);

        /**
         * If there is no a matching item there is nothing to do.
         * Previously, an item was INSERTed then deleted, but the
         * data received with deleted items is often too incomplete
         * to properly save.
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
