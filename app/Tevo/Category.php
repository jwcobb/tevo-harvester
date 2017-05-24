<?php namespace App\Tevo;

class Category extends Model
{
    use StoresFromApi;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The number of models to return for pagination.
     *
     * @var int
     */
    protected $perPage = 200;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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


    /**
     * The attributes that may be NULL.
     *
     * @var array
     */
    protected $nullable = [
        'parent_id',
    ];


    /**
     * Mutate the $result as necessary.
     * Be sure to run the parent::mutateApiResult() to get the common mutations.
     *
     * @param array $result
     *
     * @return array
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
     *
     * @return array
     */
    public function parent()
    {
        return $this->hasOne(Category::class);
    }


    /**
     * Categories can have more than 1 Events.
     *
     * @return array
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }


    /**
     * Categories can have more than 1 Performer.
     *
     * @return array
     */
    public function performers()
    {
        return $this->hasMany(Performer::class);
    }
}
