<?php namespace TevoHarvester\Tevo;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event as EventFacade;
use TevoHarvester\Events\ItemWasDeleted;
use TevoHarvester\Events\ItemWasStored;

class Venue extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'venues';

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
        'popularity_score',
        'street_address',
        'extended_address',
        'locality',
        'region',
        'postal_code',
        'country_code',
        'latitude',
        'longitude',
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
        $venue = static::findOrNewWithTrashed($result['id']);
        $venue->id = $result['id'];

        if (array_key_exists('name', $result)) {
            $venue->name = $result['name'];
        }

        if (array_key_exists('keywords', $result)) {
            $venue->keywords = $result['keywords'];
        }

        if (array_key_exists('popularity_score', $result)) {
            $venue->popularity_score = (float)$result['popularity_score'];
        }

        if (array_key_exists('slug', $result)) {
            $venue->slug = $result['slug'];
        }

        if (array_key_exists('slug_url', $result)) {
            $venue->slug_url = $result['slug_url'];
        }

        $venue->upcoming_event_first = null;
        if (!empty($result['upcoming_events']['first'])) {
            $venue->upcoming_event_first = new Carbon($result['upcoming_events']['first']);
        }

        $venue->upcoming_event_last = null;
        if (!empty($result['upcoming_events']['last'])) {
            $venue->upcoming_event_last = new Carbon($result['upcoming_events']['last']);
        }

        if (isset($result['address'])) {
            $venue->street_address = $result['address']['street_address'];
            $venue->extended_address = $result['address']['extended_address'];
            $venue->locality = $result['address']['locality'];
            $venue->region = $result['address']['region'];
            $venue->postal_code = $result['address']['postal_code'];
            $venue->country_code = $result['address']['country_code'];
            $venue->latitude = $result['address']['latitude'];
            $venue->longitude = $result['address']['longitude'];
        }


        if (array_key_exists('url', $result)) {
            $venue->url = $result['url'];
        }

        if (array_key_exists('created_at', $result)) {
            $venue->tevo_created_at = new Carbon($result['created_at']);
        }
        if (array_key_exists('updated_at', $result)) {
            $venue->tevo_updated_at = new Carbon($result['updated_at']);
        }
        if (array_key_exists('deleted_at', $result)) {
            $venue->tevo_deleted_at = new Carbon($result['deleted_at']);
        }

        /**
         * If we have a deleted_at value then we are deleting the item
         * but we need to ensure that we save() it first to record some
         * data and to ensure it actually even exists. We do this via
         * the saveThenDelete() method which does not trigger any of the
         * saving events (but it does trigger the deleting events).
         */
        if (!empty($result['deleted_at'])) {
            $venue->saveThenDelete();

            // Fire an event that it was deleted
            EventFacade::fire(new ItemWasDeleted($venue));
        } else {
            if ($venue->save()) {
                // Fire an event if an INSERT or UPDATE was actually performed
                // But NOT if we are deleting.
                EventFacade::fire(new ItemWasStored($venue));
            }
        }

        return $venue;
    }


    /**
     * Venues can have more than 1 Configuration.
     *
     * @return array
     */
    public function configurations()
    {
        return $this->hasMany(Configuration::class);
    }


    /**
     * Venues can have more than 1 Event.
     *
     * @return array
     */
    public function events()
    {
        return $this->hasMany(Event::class);
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
