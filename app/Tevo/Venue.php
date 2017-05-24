<?php namespace App\Tevo;

use Carbon\Carbon;

class Venue extends Model
{
    use StoresFromApi;

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
     * The attributes that may be NULL.
     *
     * @var array
     */
    protected $nullable = [
        'keywords',
        'category_id',
        'street_address',
        'extended_address',
        'locality',
        'region',
        'postal_code',
        'country_code',
        'latitude',
        'longitude',
        'upcoming_event_first',
        'upcoming_event_last',
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
        if (isset($result['address'])) {
            $result['street_address'] = $result['address']['street_address'];
            $result['extended_address'] = $result['address']['extended_address'];
            $result['locality'] = $result['address']['locality'];
            $result['region'] = $result['address']['region'];
            $result['postal_code'] = $result['address']['postal_code'];
            $result['country_code'] = $result['address']['country_code'];
            $result['latitude'] = $result['address']['latitude'];
            $result['longitude'] = $result['address']['longitude'];
        }
        unset($result['address']);

        $result['upcoming_event_first'] = Carbon::parse($result['upcoming_events']['first']) ?? null;
        $result['upcoming_event_last'] = Carbon::parse($result['upcoming_events']['last']) ?? null;
        unset($result['upcoming_events']);

        // Because sometimes popularity_score is NULL lets coerce those to zero
        $result['popularity_score'] = (float)$result['popularity_score'];
        return $result;
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
