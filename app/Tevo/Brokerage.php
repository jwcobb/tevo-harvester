<?php namespace TevoHarvester\Tevo;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event as EventFacade;
use TevoHarvester\Events\ItemWasDeleted;
use TevoHarvester\Events\ItemWasStored;

class Brokerage extends Model
{

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
     * Take a result item from a Ticket Evolution API request,
     * massage it into form and save() it, thus INSERTing or UPDATEing
     * it as necessary.
     */
    public static function storeFromApi($result)
    {
        $brokerage = static::findOrNewWithTrashed($result['id']);
        $brokerage->id = $result['id'];

        if (array_key_exists('name', $result)) {
            $brokerage->name = $result['name'];
        }

        if (array_key_exists('abbreviation', $result)) {
            $brokerage->abbreviation = $result['abbreviation'];
        }

        if (array_key_exists('logo', $result)) {
            $brokerage->logo = $result['logo'];
        }

        if (array_key_exists('natb_member', $result)) {
            $brokerage->natb_member = $result['natb_member'];
        }

        if (array_key_exists('url', $result)) {
            $brokerage->url = $result['url'];
        }

        if (array_key_exists('created_at', $result)) {
            $brokerage->tevo_created_at = new Carbon($result['created_at']);
        }
        if (array_key_exists('updated_at', $result)) {
            $brokerage->tevo_updated_at = new Carbon($result['updated_at']);
        }
        if (array_key_exists('deleted_at', $result)) {
            $brokerage->tevo_deleted_at = new Carbon($result['deleted_at']);
        }

        /**
         * If we have a deleted_at value then we are deleting the item
         * but we need to ensure that we save() it first to record some
         * data and to ensure it actually even exists. We do this via
         * the saveThenDelete() method which does not trigger any of the
         * saving events (but it does trigger the deleting events).
         */
        if (!empty($result['deleted_at'])) {
            $brokerage->saveThenDelete();

            // Fire an event that it was deleted
            EventFacade::fire(new ItemWasDeleted($brokerage));
        } else {
            if ($brokerage->save()) {
                // Fire an event if an INSERT or UPDATE was actually performed
                // But NOT if we are deleting.
                EventFacade::fire(new ItemWasStored($brokerage));
            }
        }

        return $brokerage;
    }


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
