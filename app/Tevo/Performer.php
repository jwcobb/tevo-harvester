<?php namespace TevoHarvester\Tevo;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event as EventFacade;
use TevoHarvester\Events\ItemWasDeleted;
use TevoHarvester\Events\ItemWasStored;

class Performer extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'performers';

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
    protected $perPage = 100;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'slug',
        'category_id',
        'popularity_score',
        'venue_id',
        'keywords',
        'upcoming_event_first',
        'upcoming_event_last',
        'url',
        'slug_url',
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
        'upcoming_event_first',
        'upcoming_event_last',
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
        $performer = static::findOrNewWithTrashed($result['id']);
        $performer->id = $result['id'];

        if (array_key_exists('name', $result)) {
            $performer->name = $result['name'];
        }

        if (array_key_exists('slug_url', $result)) {
            $performer->slug_url = $result['slug_url'];
        }

        if (array_key_exists('slug', $result)) {
            $performer->slug = $result['slug'];
        }

        $performer->category_id = null;
        if (!empty($result['category']['id'])) {
            $performer->category_id = $result['category']['id'];
        }

        $performer->venue_id = null;
        if (!empty($result['venue']['id'])) {
            $performer->venue_id = $result['venue']['id'];
        }

        if (array_key_exists('popularity_score', $result)) {
            $performer->popularity_score = (float)$result['popularity_score'];
        }

        if (array_key_exists('keywords', $result)) {
            $performer->keywords = $result['keywords'];
        }

        $performer->upcoming_event_first = null;
        if (!empty($result['upcoming_events']['first'])) {
            $performer->upcoming_event_first = new Carbon($result['upcoming_events']['first']);
        }

        $performer->upcoming_event_last = null;
        if (!empty($result['upcoming_events']['last'])) {
            $performer->upcoming_event_last = new Carbon($result['upcoming_events']['last']);
        }


        if (array_key_exists('url', $result)) {
            $performer->url = $result['url'];
        }

        if (array_key_exists('created_at', $result)) {
            $performer->tevo_created_at = new Carbon($result['created_at']);
        }
        if (array_key_exists('updated_at', $result)) {
            $performer->tevo_updated_at = new Carbon($result['updated_at']);
        }
        if (array_key_exists('deleted_at', $result)) {
            $performer->tevo_deleted_at = new Carbon($result['deleted_at']);
        }

        /**
         * If we have a deleted_at value then we are deleting the item
         * but we need to ensure that we save() it first to record some
         * data and to ensure it actually even exists. We do this via
         * the saveThenDelete() method which does not trigger any of the
         * saving events (but it does trigger the deleting events).
         */
        if (!empty($result['deleted_at'])) {
            $performer->saveThenDelete();

            // Fire an event that it was deleted
            EventFacade::fire(new ItemWasDeleted($performer));
        } else {
            if ($performer->save()) {
                // Fire an event if an INSERT or UPDATE was actually performed
                // But NOT if we are deleting.
                EventFacade::fire(new ItemWasStored($performer));
            }
        }

        return $performer;
    }


    /**
     * Performers can have only 1 Category.
     *
     * @return array
     */
    public function category()
    {
        return $this->hasOne(Category::class);
    }


    /**
     * Performers may have 0 or 1 Venues.
     *
     * @return array
     */
    public function venue()
    {
        return $this->hasOne(Venue::class);
    }


    /**
     * Performers can have more than 1 Event.
     *
     * @return array
     */
    public function events()
    {
        return $this->hasManyThrough(Performance::class, Event::class);
    }


    /**
     * Performers belong to a Performance.
     *
     * @return array
     */
    public function performance()
    {
        return $this->belongsTo(Performance::class);
    }


    /**
     * Mutator to nullify empty value.
     *
     * @return array
     */
    public function setKeywordsAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['keywords'] = null;
        } else {
            $this->attributes['keywords'] = $value;
        }
    }
}
