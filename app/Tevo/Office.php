<?php namespace TevoHarvester\Tevo;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event as EventFacade;
use TevoHarvester\Events\ItemWasDeleted;
use TevoHarvester\Events\ItemWasStored;


class Office extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'offices';

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
        'brokerage_id',
        'name',
        'main',
        'street_address',
        'extended_address',
        'locality',
        'region',
        'postal_code',
        'country_code',
        'latitude',
        'longitude',
        'po_box',
        'phone',
        'fax',
        'timezone',
        'pos',
        'evopay_discount',
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
        $office = static::findOrNewWithTrashed($result['id']);
        $office->id = $result['id'];

        if (isset($result['brokerage']['id'])) {
            $office->brokerage_id = $result['brokerage']['id'];
        }

        if (array_key_exists('name', $result)) {
            $office->name = $result['name'];
        }

        if (array_key_exists('main', $result)) {
            $office->main = $result['main'];
        }


        if (isset($result['address'])) {
            $office->street_address = $result['address']['street_address'];
            $office->extended_address = $result['address']['extended_address'];
            $office->locality = $result['address']['locality'];
            $office->region = $result['address']['region'];
            $office->postal_code = $result['address']['postal_code'];
            $office->country_code = $result['address']['country_code'];
            $office->po_box = $result['address']['po_box'];
            $office->latitude = $result['address']['latitude'];
            $office->longitude = $result['address']['longitude'];
        }


        if (array_key_exists('time_zone', $result)) {
            $office->time_zone = $result['time_zone'];
        }

        if (array_key_exists('phone', $result)) {
            $office->phone = $result['phone'];
        }

        if (array_key_exists('fax', $result)) {
            $office->fax = $result['fax'];
        }

        if (array_key_exists('pos', $result)) {
            $office->pos = $result['pos'];
        }

        if (array_key_exists('evopay', $result)) {
            $office->evopay = $result['evopay'];
        }

        if (array_key_exists('evopay_discount', $result)) {
            $office->evopay_discount = $result['evopay_discount'];
        }

        if (array_key_exists('url', $result)) {
            $office->url = $result['url'];
        }

        if (array_key_exists('created_at', $result)) {
            $office->tevo_created_at = new Carbon($result['created_at']);
        }
        if (array_key_exists('updated_at', $result)) {
            $office->tevo_updated_at = new Carbon($result['updated_at']);
        }
        if (array_key_exists('deleted_at', $result)) {
            $office->tevo_deleted_at = new Carbon($result['deleted_at']);
        }

        /**
         * If we have a deleted_at value then we are deleting the item
         * but we need to ensure that we save() it first to record some
         * data and to ensure it actually even exists. We do this via
         * the saveThenDelete() method which does not trigger any of the
         * saving events (but it does trigger the deleting events).
         */
        if (!empty($result['deleted_at'])) {
            $office->saveThenDelete();

            // Fire an event that it was deleted
            EventFacade::fire(new ItemWasDeleted($office));
        } else {
            if ($office->save()) {
                // Fire an event if an INSERT or UPDATE was actually performed
                // But NOT if we are deleting.
                EventFacade::fire(new ItemWasStored($office));
            }


            /**
             * Get all of the already associated email addresses and
             * delete() any that are no longer active.
             */
            $activeEmailAddresses = $result['email'];

            foreach ($office->emailAddresses() as $emailAddress) {
                if (!in_array($emailAddress->email_address, $activeEmailAddresses)) {
                    // If it isn't in the array then it is no longer active, kill it.
                    if ($emailAddress->delete()) {
                        EventFacade::fire(new ItemWasDeleted($emailAddress));
                    }
                } else {
                    // Otherwise unset() it so that we're left with any
                    // $activeEmailAddresses that need to be created
                    unset($activeEmailAddresses['$emailAddress->email_address']);
                }
            }
            unset($emailAddress);

            // Any addresses left in $activeEmailAddresses do not yet exist
            // and need to be created.
            foreach ($activeEmailAddresses as $email_address) {
                if ($newEmailAddress = OfficeEmailAddress::firstOrCreate([
                    'office_id'     => $result['id'],
                    'email_address' => strtolower($email_address),
                ])
                ) {
                    EventFacade::fire(new ItemWasStored($newEmailAddress));
                }
            }
            unset($email_address);


            /**
             * Get all of the already associated hours and
             * delete() any that are no longer active.
             */
            $activeHours = $result['hours'];

            foreach ($office->hours() as $hour) {
                if (!in_array($hour->id, array_column($activeHours, 'id'))) {
                    // If it isn't in the array then it is no longer active, kill it.
                    if ($hour->delete()) {
                        EventFacade::fire(new ItemWasDeleted($hour));
                    }
                } else {
                    // Otherwise unset() it so that we're left with any
                    // $activeEmailAddresses that need to be created
                    unset($activeHours[array_search($hour->id, $activeHours)]);
                }
            }
            unset($hour);

            // Any addresses left in $activeEmailAddresses do not yet exist
            // and need to be created.
            foreach ($activeHours as $operatingHours) {
                foreach ($operatingHours as $hour) {
                    $officeHour = OfficeHour::findOrNewWithTrashed($hour['id']);
                    $officeHour->id = $hour['id'];
                    $officeHour->office_id = $result['id'];
                    $officeHour->day_of_week = $hour['day_of_week'];
                    $officeHour->closed = $hour['closed'];
                    $officeHour->open = Carbon::parse($hour['open'])->toTimeString();
                    $officeHour->close = Carbon::parse($hour['close'])->toTimeString();

                    if ($officeHour->save()) {
                        EventFacade::fire(new ItemWasStored($officeHour));
                    }
                }
            }
            unset($hour);


        }

        return $office;
    }


    /**
     * Offices can have more than 1 OfficeEmailAddress.
     *
     * @return array
     */
    public function emailAddresses()
    {
        return $this->hasMany(OfficeEmailAddress::class);
    }


    /**
     * Offices can have more than 1 OfficeHour.
     *
     * @return array
     */
    public function hours()
    {
        return $this->hasMany(OfficeHour::class);
    }


    /**
     * Offices belong to a Brokerage.
     *
     * @return array
     */
    public function brokerage()
    {
        return $this->belongsTo(Brokerage::class);
    }


}
