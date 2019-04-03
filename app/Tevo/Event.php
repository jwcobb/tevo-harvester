<?php namespace App\Tevo;

use App\Events\ItemWasStored;
use Carbon\Carbon;

class Event extends Model
{
    use StoresFromApi;

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
        'occurs_at_local',
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
     * The attributes that may be NULL.
     *
     * @var array
     */
    protected $nullable = [
        'notes',
        'stubhub_id',
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

        // No event should have a real score that is negative,
        // but yet I’ve seen it happen. Add some sanity.
        if ($result['long_term_popularity_score'] < 0) {
            $result['long_term_popularity_score'] = (float)0;
        }
        if ($result['popularity_score'] < 0) {
            $result['popularity_score'] = (float)0;
        }

        $result['occurs_at'] = rtrim($result['occurs_at'], 'Z');
        $result['occurs_at_local'] = Carbon::parse($result['occurs_at_local']);

        $result['configuration_id'] = $result['configuration']['id'] ?? null;
        unset($result['configuration']);

        $result['category_id'] = $result['category']['id'] ?? null;
        unset($result['category']);

        $result['venue_id'] = $result['venue']['id'];
        unset($result['venue']);

        return $result;
    }


    /**
     * Any operations that need to be run after save()
     * such as saving related Models can go here.
     *
     * @param Model $event
     * @param array $result
     *
     * @return Model
     */
    protected static function postSave(Model $event, array $result): Model
    {
        // Delete all existing performances for this event and we will
        // restore/add performances as necessary below.
        $event->performances()->delete();

        foreach ($result['performances'] as $resultPerformance) {
            $performance = Performance::withTrashed()
                ->where('event_id', $result['id'])
                ->where('performer_id', $resultPerformance['performer']['id'])
                ->first();

            if (empty($performance)) {
                $performance = new Performance([
                    'event_id'     => $result['id'],
                    'performer_id' => $resultPerformance['performer']['id'],
                ]);
            }

            $performance->fill([
                'primary'    => $resultPerformance['primary'],
                'event_name' => $event->name,
                'occurs_at'  => Carbon::parse($event->occurs_at)->toDateTimeString(),
                'venue_id'   => $event->venue_id,
            ]);

            $performance->{$performance->getDeletedAtColumn()} = null;

            if ($performance->save()) {
                event(new ItemWasStored($performance));
            }
        }
        unset($performance);

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
