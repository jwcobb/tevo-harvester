<?php

namespace App\Models\Tevo;

use App\Events\ItemWasStored;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperEvent
 */
class Event extends Model
{
    use StoresFromApi;

    protected $table = 'events';

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

    protected $casts = [
        'id'                          => 'integer',
        'name'                        => 'string',
        'occurs_at'                   => 'datetime',
        'occurs_at_local'             => 'datetime',
        'venue_id'                    => 'integer',
        'configuration_id'            => 'integer',
        'category_id'                 => 'integer',
        'popularity_score'            => 'decimal:12',
        'short_term_popularity_score' => 'decimal:7',
        'long_term_popularity_score'  => 'decimal:7',
        'products_count'              => 'integer',
        'products_eticket_count'      => 'integer',
        'available_count'             => 'integer',
        'state'                       => 'integer',
        'notes'                       => 'string',
        'stubhub_id'                  => 'integer',
        'url'                         => 'string',
        'seating_chart_url_medium'    => 'string',
        'seating_chart_url_large'     => 'string',
        'configuration_url'           => 'string',
        'tevo_created_at'             => 'datetime',
        'tevo_updated_at'             => 'datetime',
        'tevo_deleted_at'             => 'datetime',
        'created_at'                  => 'datetime',
        'updated_at'                  => 'datetime',
        'deleted_at'                  => 'datetime',
    ];


    /**
     * When deleting an Event deleted the related Performances
     */
    public static function boot() {
        parent::boot();

        self::deleting(function($event) {
            $event->performances()->each(function($performance) {
                $performance->delete();
            });
        });
    }



    /**
     * Events can have more than 1 Performance.
     */
    public function performances(): HasMany
    {
        return $this->hasMany(Performance::class);
    }


    /**
     * Events can have more than 1 Performer via 1 or more Performances.
     */
    public function performers(): HasManyThrough
    {
        return $this->hasManyThrough(Performance::class, Performer::class);
    }


    /**
     * Events can have only 1 Venue.
     */
    public function venue(): HasOne
    {
        return $this->hasOne(Venue::class);
    }


    /**
     * Events can have only 1 Configuration.
     */
    public function configuration(): HasOne
    {
        return $this->hasOne(Configuration::class);
    }


    /**
     * Events can have only 1 Category.
     */
    public function category(): HasOne
    {
        return $this->hasOne(Category::class);
    }


    /**
     * Mutator to nullify empty value.
     */
    public function setNotesAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['notes'] = null;
        } else {
            $this->attributes['notes'] = $value;
        }
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

        // No event should have a real score that is negative,
        // but yet I’ve seen it happen. Add some sanity.
        if ($result['long_term_popularity_score'] < 0) {
            $result['long_term_popularity_score'] = (float) 0;
        }
        if ($result['popularity_score'] < 0) {
            $result['popularity_score'] = (float) 0;
        }

        $result['occurs_at'] = rtrim($result['occurs_at'], 'Z');

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
     */
    protected static function postSave(Model $event, array $result): Model
    {
        /**
         * Insert/Delete/Update Performances as necessary
         */
        if (! empty($result['performances'])) {
            $validEventPerformerIds = [];

            foreach ($result['performances'] as $resultPerformance) {
                $validEventPerformerIds[] = $resultPerformance['performer']['id'];

                Performance::withTrashed()->updateOrCreate(
                    [
                        'event_id'     => $event->id,
                        'performer_id' => $resultPerformance['performer']['id'],
                    ],
                    [
                        'primary'    => $resultPerformance['primary'],
                        'event_name' => $event->name,
                        'occurs_at'  => $event->occurs_at,
                        'venue_id'   => $event->venue_id,
                        'deleted_at' => null,
                    ]);
            }

            // Delete any Performances for this Event that have Performers
            // that were not in $result['performances']
            Performance::where('event_id', '=', $event->id)
                ->whereNotIn('performer_id', $validEventPerformerIds)
                ->delete();

            unset($validEventPerformerIds);
        } else {
            Performance::where('event_id', '=', $event->id)
                ->delete();
        }
        return $event;
    }
}
