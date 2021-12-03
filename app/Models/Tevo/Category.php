<?php

namespace App\Models\Tevo;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperCategory
 */
class Category extends Model
{
    use StoresFromApi;

    protected $table = 'categories';

    protected $fillable = [
        'id',
        'parent_id',
        'name',
        'url',
        'slug',
        'slug_url',
        'tevo_created_at',
        'tevo_updated_at',
        'tevo_deleted_at',
    ];

    protected $casts = [
        'id'              => 'integer',
        'parent_id'       => 'integer',
        'name'            => 'string',
        'url'             => 'string',
        'slug'            => 'string',
        'slug_url'        => 'string',
        'tevo_created_at' => 'datetime',
        'tevo_updated_at' => 'datetime',
        'tevo_deleted_at' => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];


    /**
     * Mutate the $result as necessary.
     * Be sure to run the parent::mutateApiResult() to get the common mutations.
     */
    protected static function mutateApiResult(array $result): array
    {
        // Be sure to call the parent version for common mutations
        $result = parent::mutateApiResult($result);


        /**
         * Add custom mutations for this item type here
         */
        $result['parent_id'] = $result['parent']['id'] ?? null;
        unset($result['parent']);

        return $result;
    }


    /**
     * Categories may have 1 or 0 ParentCategories.
     */
    public function parent(): HasOne
    {
        return $this->hasOne(__CLASS__);
    }


    /**
     * Categories can have more than 1 Events.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }


    /**
     * Categories can have more than 1 Performer.
     */
    public function performers(): HasMany
    {
        return $this->hasMany(Performer::class);
    }
}
