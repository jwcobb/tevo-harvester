<?php

namespace App\Models\Tevo;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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


    /**
     * Brokerages can have more than 1 Office.
     */
    public function offices(): HasMany
    {
        return $this->hasMany(Office::class);
    }


    /**
     * Mutator to fix TEvo’s stupid default value by switching
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
