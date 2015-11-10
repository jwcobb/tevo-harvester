<?php namespace TevoHarvester\Tevo;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event as EventFacade;
use TevoHarvester\Events\ItemWasDeleted;
use TevoHarvester\Events\ItemWasStored;


class Category extends Model
{
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
        'tevo_created_at',
        'tevo_updated_at',
        'tevo_deleted_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'tevo_created_at',
        'tevo_updated_at',
        'tevo_deleted_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes excluded from the model’s JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    /**
     * Take a result item from a Ticket Evolution API request,
     * massage it into form and save() it, thus INSERTing or UPDATEing
     * it as necessary.
     */
    public static function storeFromApi($result)
    {
        $category = static::findOrNewWithTrashed((int)$result['id']);
        $category->id = $result['id'];

        $category->parent_id = null;
        if (isset($result['parent']['id'])) {
            $category->parent_id = $result['parent']['id'];
        }

        if (array_key_exists('name', $result)) {
            $category->name = $result['name'];
        }

        if (array_key_exists('slug', $result)) {
            $category->slug = $result['slug'];
        }

        if (array_key_exists('url', $result)) {
            $category->url = $result['url'];
        }

        if (array_key_exists('created_at', $result)) {
            $category->tevo_created_at = new Carbon($result['created_at']);
        }
        if (array_key_exists('updated_at', $result)) {
            $category->tevo_updated_at = new Carbon($result['updated_at']);
        }
        if (array_key_exists('deleted_at', $result)) {
            $category->tevo_deleted_at = new Carbon($result['deleted_at']);
        }

        /**
         * If we have a deleted_at value then we are deleting the item
         * but we need to ensure that we save() it first to record some
         * data and to ensure it actually even exists. We do this via
         * the saveThenDelete() method which does not trigger any of the
         * saving events (but it does trigger the deleting events).
         */
        if (!empty($result['deleted_at'])) {
            $category->saveThenDelete();

            // Fire an event that it was deleted
            EventFacade::fire(new ItemWasDeleted($category));
        } else {
            if ($category->save()) {
                // Fire an event if an INSERT or UPDATE was actually performed
                // But NOT if we are deleting.
                EventFacade::fire(new ItemWasStored($category));
            }
        }

        return $category;
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
