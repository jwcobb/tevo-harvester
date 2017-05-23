<?php namespace App\Tevo;

class Brokerage extends Model
{
    use StoresFromApi;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'brokerages';

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
        'abbreviation',
        'natb_member',
        'url',
        'logo',
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
        'logo',
    ];


    /**
     * Brokerages can have more than 1 Office.
     *
     * @return array
     */
    public function offices()
    {
        return $this->hasMany(Office::class);
    }


    /**
     * Get Users via Offices.
     *
     * @return array
     */
    public function users()
    {
        return $this->hasManyThrough(Office::class, User::class);
    }


    /**
     * Mutator to fix TEvo’s stupid default value by switching
     * /logos/original/missing.png to NULL
     *
     */
    public function setLogoAttribute($value)
    {
        if ($value === '/logos/original/missing.png') {
            $this->attributes['logo'] = null;
        } else {
            $this->attributes['logo'] = $value;
        }
    }
}
