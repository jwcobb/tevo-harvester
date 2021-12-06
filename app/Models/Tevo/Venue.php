<?php

namespace App\Models\Tevo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperVenue
 */
class Venue extends Model
{
    use StoresFromApi, HasKeywords, HasStreetAddress, HasUpcomingEvents;

    protected $table = 'venues';

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

    protected $casts = [
        'id'                   => 'integer',
        'name'                 => 'string',
        'slug'                 => 'string',
        'popularity_score'     => 'decimal:7',
        'street_address'       => 'string',
        'extended_address'     => 'string',
        'locality'             => 'string',
        'region'               => 'string',
        'postal_code'          => 'string',
        'country_code'         => 'string',
        'latitude'             => 'decimal:17',
        'longitude'            => 'decimal:17',
        'keywords'             => 'string',
        'upcoming_event_first' => 'datetime',
        'upcoming_event_last'  => 'datetime',
        'url'                  => 'string',
        'slug_url'             => 'string',
        'tevo_created_at'      => 'datetime',
        'tevo_updated_at'      => 'datetime',
        'tevo_deleted_at'      => 'datetime',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
        'deleted_at'           => 'datetime',
    ];


    /**
     * When deleting an Office deleted the related
     * OfficeHours and OfficeEmailAddresses
     */
    public static function boot() {
        parent::boot();

        self::deleting(function($venue) {
            $venue->configurations()->each(function($configuration) {
                $configuration->delete();
            });
        });
    }


    /**
     * Venues can have more than 1 Configuration.
     */
    public function configurations(): HasMany
    {
        return $this->hasMany(Configuration::class);
    }


    /**
     * Venues can have more than 1 Event.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get the real upcoming_first_event
     */
    public function upcomingFirstEvent(): HasOne
    {
        return $this->hasOne(Event::class)->ofMany(['occurs_at' => 'min'], function ($query) {
            $query->where('occurs_at', '>', now())->where('state', '=', 'shown');
        });
    }


    /**
     * Get the real upcoming_last_event
     */
    public function upcomingLastEvent(): HasOne
    {
        return $this->hasOne(Event::class)->ofMany(['occurs_at' => 'max'], function ($query) {
            $query->where('occurs_at', '>', now())->where('state', '=', 'shown');
        });
    }


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
        if (isset($result['address'])) {
            $result = self::setStreetAddress($result);
        }

        $result = self::setUpcomingEvents($result);


        // Sometimes popularity_score is NULL. Coerce those to zero
        $result['popularity_score'] = $result['popularity_score'] ?? 0;

        return $result;
    }
}
