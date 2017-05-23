<?php namespace TevoHarvester\Tevo;

class Performance extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'performances';

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
     * Performances can have only 1 Performer.
     *
     * @return array
     */
    public function performer()
    {
        return $this->hasOne(Performer::class);
    }


    /**
     * Performances can have only 1 Venue.
     *
     * @return array
     */
    public function venue()
    {
        return $this->hasOne(Venue::class);
    }


    /**
     * Performances belong to an Event.
     *
     * @return array
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
