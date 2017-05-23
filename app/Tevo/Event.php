<?php namespace TevoHarvester\Tevo;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event as EventFacade;
use TevoHarvester\Events\ItemWasDeleted;
use TevoHarvester\Events\ItemWasStored;

class Event extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'events';

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
        'occurs_at',
        'venue_id',
        'configuration_id',
        'category_id',
        'popularity_score',
        'short_term_popularity_score',
        'long_term_popularity_score',
        'products_count',
        'products_eticket_count',
        'available_count',
        'state',
        'notes',
        'stubhub_id',
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
        'occurs_at',
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
        $event = static::findOrNewWithTrashed($result['id']);
        $event->id = $result['id'];

        if (array_key_exists('name', $result)) {
            $event->name = $result['name'];
        }

        if (array_key_exists('available_count', $result)) {
            $event->available_count = (int)$result['available_count'];
        }

        if (array_key_exists('products_count', $result)) {
            $event->products_count = (int)$result['products_count'];
        }

        if (array_key_exists('products_eticket_count', $result)) {
            $event->products_eticket_count = (int)$result['products_eticket_count'];
        }

        if (isset($result['category']['id'])) {
            $event->category_id = $result['category']['id'];
        }

        if (isset($result['configuration']['id'])) {
            $event->configuration_id = $result['configuration']['id'];
        }

        if (isset($result['venue']['id'])) {
            $event->venue_id = $result['venue']['id'];
        }

        if (array_key_exists('long_term_popularity_score', $result)) {
            $event->long_term_popularity_score = (float)$result['long_term_popularity_score'];
        }

        if (array_key_exists('short_term_popularity_score', $result)) {
            $event->short_term_popularity_score = (float)$result['short_term_popularity_score'];
        }

        if (array_key_exists('popularity_score', $result)) {
            $event->popularity_score = (float)$result['popularity_score'];
        }

        if (array_key_exists('notes', $result)) {
            $event->notes = $result['notes'];
        }

        if (array_key_exists('occurs_at', $result)) {
            $event->occurs_at = new Carbon($result['occurs_at']);
        }

        if (array_key_exists('state', $result)) {
            $event->state = $result['state'];
        }

        if (array_key_exists('stubhub_id', $result)) {
            $event->stubhub_id = $result['stubhub_id'];
        }


        if (array_key_exists('url', $result)) {
            $event->url = $result['url'];
        }

        if (array_key_exists('created_at', $result)) {
            $event->tevo_created_at = new Carbon($result['created_at']);
        }
        if (array_key_exists('updated_at', $result)) {
            $event->tevo_updated_at = new Carbon($result['updated_at']);
        }
        if (array_key_exists('deleted_at', $result)) {
            $event->tevo_deleted_at = new Carbon($result['deleted_at']);
        }

        /**
         * If we have a deleted_at value then we are deleting the item
         * but we need to ensure that we save() it first to record some
         * data and to ensure it actually even exists. We do this via
         * the saveThenDelete() method which does not trigger any of the
         * saving events (but it does trigger the deleting events).
         */
        if (!empty($result['deleted_at'])) {
            $event->saveThenDelete();

            // Fire an event that it was deleted
            EventFacade::fire(new ItemWasDeleted($event));
        } else {
            if ($event->save()) {
                // Fire an event if an INSERT or UPDATE was actually performed
                // But NOT if we are deleting.
                EventFacade::fire(new ItemWasStored($event));
            }


            // Delete all existing performances for this event and we will
            // restore/add performances as necessary below.
            $event->performances()->delete();

            foreach ($result['performances'] as $resultPerformance) {
                $performance = Performance::withTrashed()
                    ->where('event_id', $result['id'])
                    ->where('performer_id', $resultPerformance['performer']['id'])
                    ->first();

                if (empty($performance)) {
                    $performance = new Performance();
                    $performance->event_id = $result['id'];
                    $performance->performer_id = $resultPerformance['performer']['id'];
                }

                $performance->primary = $resultPerformance['primary'];

                if (array_key_exists('name', $result)) {
                    $performance->event_name = $result['name'];
                }

                if (array_key_exists('occurs_at', $result)) {
                    $performance->occurs_at = new Carbon($result['occurs_at']);
                }

                if (isset($result['venue']['id'])) {
                    $performance->venue_id = $result['venue']['id'];
                }

                $performance->{$performance->getDeletedAtColumn()} = null;
                if ($performance->save()) {
                    EventFacade::fire(new ItemWasStored($performance));
                }
            }
            unset($performance);
        }

        return $event;
    }


    /**
     * Events can have more than 1 Performance.
     *
     * @return array
     */
    public function performances()
    {
        return $this->hasMany(Performance::class);
    }


    /**
     * Events can have more than 1 Performer via 1 or more Performances.
     *
     * @return array
     */
    public function performers()
    {
        return $this->hasManyThrough(Performance::class, Performer::class);
    }


    /**
     * Events can have only 1 Venue.
     *
     * @return array
     */
    public function venue()
    {
        return $this->hasOne(Venue::class);
    }


    /**
     * Events can have only 1 Configuration.
     *
     * @return array
     */
    public function configuration()
    {
        return $this->hasOne(Configuration::class);
    }


    /**
     * Events can have only 1 Category.
     *
     * @return array
     */
    public function category()
    {
        return $this->hasOne(Category::class);
    }


    /**
     * Mutator to nullify empty value.
     *
     * @return array
     */
    public function setNotesAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['notes'] = null;
        } else {
            $this->attributes['notes'] = $value;
        }
    }
}
