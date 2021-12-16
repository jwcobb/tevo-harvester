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
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'id'           => 'integer',
        'event_id'     => 'integer',
        'performer_id' => 'integer',
        'primary'      => 'boolean',
        'event_name'   => 'string',
        'occurs_at'    => 'datetime',
        'venue_id'     => 'integer',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
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
