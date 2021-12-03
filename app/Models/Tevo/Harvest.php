<?php

namespace App\Models\Tevo;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Harvest
 *
 * @package App\Models\Tevo
 * @mixin IdeHelperHarvest
 */
class Harvest extends Model
{
    protected $table = 'harvests';

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

    protected $casts = [
        'id'                         => 'integer',
        'resource'                   => 'string',
        'action'                     => 'string',
        'library_method'             => 'string',
        'model_class'                => 'string',
        'scheduler_name'             => 'string',
        'scheduler_frequency_method' => 'string',
        'ping_before_url'            => 'string',
        'then_ping_url'              => 'string',
        'last_run_at'                => 'datetime',
        'created_at'                 => 'datetime',
        'updated_at'                 => 'datetime',
    ];


    /**
     * Mutator to nullify empty value.
     */
    public function setPingBeforeUrlAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['ping_before_url'] = null;
        } else {
            $this->attributes['ping_before_url'] = $value;
        }
    }


    /**
     * Mutator to nullify empty value.
     */
    public function setThenPingUrlAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['then_ping_url'] = null;
        } else {
            $this->attributes['then_ping_url'] = $value;
        }
    }
}
