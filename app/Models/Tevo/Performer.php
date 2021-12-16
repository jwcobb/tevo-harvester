<?php

namespace App\Models\Tevo;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperPerformer
 */
class Performer extends Model
{
    use StoresFromApi, HasKeywords, HasUpcomingEvents;

    protected $table = 'performers';

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

    protected $casts = [
        'id'                   => 'integer',
        'name'                 => 'string',
        'slug'                 => 'string',
        'category_id'          => 'integer',
        'popularity_score'     => 'decimal:7',
        'venue_id'             => 'integer',
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
        $result['category_id'] = $result['category']['id'] ?? null;
        unset($result['category']);

        $result['venue_id'] = $result['venue']['id'] ?? null;
        unset($result['venue']);

        $result = self::setUpcomingEvents($result);

        return $result;
    }


    /**
     * Performers can have only 1 Category.
     */
    public function category(): HasOne
    {
        return $this->hasOne(Category::class);
    }


    /**
     * Performers may have 0 or 1 Venues.
     */
    public function venue(): HasOne
    {
        return $this->hasOne(Venue::class);
    }


    /**
     * Performers can have more than 1 Event.
     */
    public function events(): HasManyThrough
    {
        return $this->hasManyThrough(Performance::class, Event::class);
    }


    /**
     * Performers belong to a Performance.
     */
    public function performance(): BelongsTo
    {
        return $this->belongsTo(Performance::class);
    }
}
