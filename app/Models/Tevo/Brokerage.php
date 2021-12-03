<?php

namespace App\Models\Tevo;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperBrokerage
 */
class Brokerage extends Model
{
    use StoresFromApi;

    protected $table = 'brokerages';

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

    protected $casts = [
        'id'              => 'integer',
        'name'            => 'string',
        'abbreviation'    => 'string',
        'natb_member'     => 'boolean',
        'url'             => 'string',
        'logo'            => 'string',
        'tevo_created_at' => 'datetime',
        'tevo_updated_at' => 'datetime',
        'tevo_deleted_at' => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];


    /**
     * Brokerages can have more than 1 Office.
     */
    public function offices(): HasMany
    {
        return $this->hasMany(Office::class);
    }


    /**
     * Mutator to change from TEvo’s default value by switching
     * /logos/original/missing.png to NULL
     */
    public function setLogoAttribute($value): void
    {
        if ($value === '/logos/original/missing.png') {
            $this->attributes['logo'] = null;
        } else {
            $this->attributes['logo'] = $value;
        }
    }
}
