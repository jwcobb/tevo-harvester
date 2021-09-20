<?php

namespace App\Models\Tevo;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperPerformance
 */
class Performance extends Model
{
    protected $table = 'performances';

    protected $fillable = [
        'event_id',
        'performer_id',
        'primary',
        'event_name',
        'occurs_at',
        'venue_id',
        'tevo_created_at',
        'tevo_updated_at',
        'tevo_deleted_at',
    ];

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
     * Performances can have only 1 Performer.
     */
    public function performer(): HasOne
    {
        return $this->hasOne(Performer::class);
    }


    /**
     * Performances can have only 1 Venue.
     */
    public function venue(): HasOne
    {
        return $this->hasOne(Venue::class);
    }


    /**
     * Performances belong to an Event.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
