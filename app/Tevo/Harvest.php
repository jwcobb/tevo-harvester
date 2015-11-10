<?php namespace TevoHarvester\Tevo;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Harvest
 *
 * @package TevoHarvester\Tevo
 */
class Harvest extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'harvests';

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
        'resource',
        'action',
        'library_method',
        'model_class',
        'scheduler_name',
        'scheduler_frequency_method',
        'ping_before_url',
        'then_ping_url',
        'last_run_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_run_at',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes excluded from the model’s JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    /**
     * Mutator to nullify empty value.
     *
     * @return array
     */
    public function setPingBeforeUrlAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['ping_before_url'] = null;
        } else {
            $this->attributes['ping_before_url'] = $value;
        }
    }


    /**
     * Mutator to nullify empty value.
     *
     * @return array
     */
    public function setThenPingUrlAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['then_ping_url'] = null;
        } else {
            $this->attributes['then_ping_url'] = $value;
        }
    }

}
