<?php namespace TevoHarvester\Tevo;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event as EventFacade;
use TevoHarvester\Events\ItemWasDeleted;
use TevoHarvester\Events\ItemWasStored;

class Configuration extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'configurations';

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
        'venue_id',
        'name',
        'primary',
        'general_admission',
        'capacity',
        'seating_chart_url_medium',
        'seating_chart_url_large',
        'configuration_url',
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
        $configuration = static::findOrNewWithTrashed($result['id']);
        $configuration->id = $result['id'];

        if (!empty($result['venue']['id'])) {
            $configuration->venue_id = $result['venue']['id'];
        }

        if (array_key_exists('name', $result)) {
            $configuration->name = $result['name'];
        }

        if (array_key_exists('capacity', $result)) {
            $configuration->capacity = $result['capacity'];
        }

        if (array_key_exists('general_admission', $result)) {
            $configuration->general_admission = $result['general_admission'];
        }

        if (array_key_exists('primary', $result)) {
            $configuration->primary = $result['primary'];
        }

        $configuration->seating_chart_url_medium = null;
        if (isset($result['seating_chart']['medium'])) {
            $configuration->seating_chart_url_medium = $result['seating_chart']['medium'];
        }

        $configuration->seating_chart_url_large = null;
        if (isset($result['seating_chart']['large'])) {
            $configuration->seating_chart_url_large = $result['seating_chart']['large'];
        }


        if (array_key_exists('url', $result)) {
            $configuration->url = $result['url'];
        }

        if (array_key_exists('created_at', $result)) {
            $configuration->tevo_created_at = new Carbon($result['created_at']);
        }
        if (array_key_exists('updated_at', $result)) {
            $configuration->tevo_updated_at = new Carbon($result['updated_at']);
        }
        if (array_key_exists('deleted_at', $result)) {
            $configuration->tevo_deleted_at = new Carbon($result['deleted_at']);
        }

        /**
         * If we have a deleted_at value then we are deleting the item
         * but we need to ensure that we save() it first to record some
         * data and to ensure it actually even exists. We do this via
         * the saveThenDelete() method which does not trigger any of the
         * saving events (but it does trigger the deleting events).
         */
        if (!empty($result['deleted_at'])) {
            $configuration->saveThenDelete();

            // Fire an event that it was deleted
            EventFacade::fire(new ItemWasDeleted($configuration));
        } else {
            if ($configuration->save()) {
                // Fire an event if an INSERT or UPDATE was actually performed
                // But NOT if we are deleting.
                EventFacade::fire(new ItemWasStored($configuration));
            }
        }

        return $configuration;
    }


    /**
     * Configurations can have more than 1 Event.
     *
     * @return array
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }


    /**
     * Configurations belong to a Venue.
     *
     * @return array
     */
    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
