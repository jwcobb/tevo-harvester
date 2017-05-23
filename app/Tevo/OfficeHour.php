<?php namespace TevoHarvester\Tevo;

class OfficeHour extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'office_hours';

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
        'office_id',
        'day_of_week',
        'closed',
        'open',
        'close',
        'office_url',
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
     * OfficeHours belong to an Office.
     *
     * @return array
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}
