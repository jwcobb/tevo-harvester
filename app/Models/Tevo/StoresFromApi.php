<?php

namespace App\Models\Tevo;

use App\Events\ItemWasDeleted;
use App\Events\ItemWasStored;
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
        if (! empty($result['deleted_at'])) {
            $item = static::withTrashed()->findOrNew((int) $result['id']);
            if ($item->exists === false) {
                /**
                 * A matching item doesn't exist in the database so do nothing
                 * because the data received with deleted items is often too
                 * incomplete to properly save and what really matters is ensuring
                 * the item is deleted (or doesn't exist).
                 */
            } elseif ($item->deleted_at === null) {
                /**
                 * A matching item was found and it has not yet been deleted.
                 * Set the tevo_deleted_at and quietly save before deleting.
                 */
                $item->tevo_deleted_at = $result['deleted_at'];
                $item->saveQuietly();
                $item->delete();
                Log::debug(__METHOD__.': Item was deleted', $item->toArray());

                event(new ItemWasDeleted($item));
            }
            /**
             * Just noting that if the item exists and deleted_at is not null
             * then it has already been marked as deleted and there is nothing to do.
             */
            return $item;
        }


        $result = self::mutateApiResult($result);

        $item = static::withTrashed()->findOrNew((int) $result['id']);
        $item->fill($result);

        // Make sure this item was previously deleted it is no longer deleted
        $item->{$item->getDeletedAtColumn()} = null;

        if ($item->save()) {
            Log::debug(__METHOD__.': Item was saved', $item->toArray());
            // Fire an event if an INSERT or UPDATE was actually performed
            event(new ItemWasStored($item));
        }

        self::postSave($item, $result);

        return $item;
    }

}
