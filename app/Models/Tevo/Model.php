<?php

namespace App\Models\Tevo;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperModel
 */
class Model extends BaseModel
{
    use SoftDeletes;

    protected $guarded = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $dates = [
        'tevo_created_at',
        'tevo_updated_at',
        'tevo_deleted_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    /**
     * Mutate the $result as necessary.
     * Shared mutations go here as is generally called by
     * mutateApiResult() method on the Models.
     */
    protected static function mutateApiResult(array $result): array
    {
        return self::mutateDatesToTevoDates($result);
    }


    /**
     * Mutate the TEvo timestamps to ensure they do not overwrite our own.
     */
    protected static function mutateDatesToTevoDates(array $result): array
    {
        if (array_key_exists('created_at', $result)) {
            $result['tevo_created_at'] = $result['created_at'];
            unset($result['created_at']);
        }
        if (array_key_exists('updated_at', $result)) {
            $result['tevo_updated_at'] = $result['updated_at'];
            unset($result['updated_at']);
        }

        return $result;
    }


    /**
     * Any operations that need to be run after save()
     * such as saving related Models can go here.
     */
    protected static function postSave(Model $item, array $result): Model
    {
        return $item;
    }
}
