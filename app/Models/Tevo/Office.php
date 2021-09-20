<?php

namespace App\Models\Tevo;

use App\Events\ItemWasDeleted;
use App\Events\ItemWasStored;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperOffice
 */
class Office extends Model
{
    use StoresFromApi;

    protected $table = 'offices';

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
        'fedex_pickup_dropoff_time',
        'tevo_created_at',
        'tevo_updated_at',
        'tevo_deleted_at',
    ];


    protected $dates = [
        'fedex_pickup_dropoff_time',
        'tevo_created_at',
        'tevo_updated_at',
        'tevo_deleted_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    /**
     * Mutate the $result as necessary.
     * Be sure to run the parent::mutateApiResult() to get the common mutations.
     */
    protected static function mutateApiResult(array $result): array
    {
        // Be sure to call the parent version for common mutations
        $result = parent::mutateApiResult($result);


        /**
         * Add custom mutations for this item type here
         */
        $result['brokerage_id'] = $result['brokerage']['id'];
        unset($result['brokerage']);

        $result['fedex_pickup_dropoff_time'] = Carbon::parse($result['fedex_pickup_dropoff_time'], 'UTC');


        if (isset($result['address'])) {
            $result['street_address'] = $result['address']['street_address'];
            $result['extended_address'] = $result['address']['extended_address'];
            $result['locality'] = $result['address']['locality'];
            $result['region'] = $result['address']['region'];
            $result['postal_code'] = $result['address']['postal_code'];
            $result['country_code'] = $result['address']['country_code'];
            $result['po_box'] = $result['address']['po_box'];
            $result['latitude'] = $result['address']['latitude'];
            $result['longitude'] = $result['address']['longitude'];
        }
        unset($result['address']);

        return $result;
    }


    /**
     * Any operations that need to be run after save()
     * such as saving related Models can go here.
     */
    protected static function postSave(Model $office, array $result): Model
    {
        /**
         * Get all the already associated email addresses and
         * delete() any that are no longer active.
         */
        $activeEmailAddresses = [];
        if (!empty($result['email'])) {
            $activeEmailAddresses = $result['email'];
        }

        foreach ($office->emailAddresses() as $emailAddress) {
            if (!in_array($emailAddress->email_address, $activeEmailAddresses, true)) {
                // If it isn't in the array then it is no longer active, kill it.
                if ($emailAddress->delete()) {
                    event(new ItemWasDeleted($emailAddress));
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
                event(new ItemWasStored($newEmailAddress));
            }
        }
        unset($email_address);


        /**
         * Get all the already associated hours and
         * delete() any that are no longer active.
         */
        $activeHours = $result['hours'];

        foreach ($office->hours() as $hour) {
            if (!in_array($hour->id, array_column($activeHours, 'id'))) {
                // If it isn't in the array then it is no longer active, kill it.
                if ($hour->delete()) {
                    event(new ItemWasDeleted($hour));
                }
            } else {
                // Otherwise, unset() it so that we're left with any
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
                    event(new ItemWasStored($officeHour));
                }
            }
        }
        unset($hour);

        return $office;
    }


    /**
     * Offices can have more than 1 OfficeEmailAddress.
     */
    public function emailAddresses(): HasMany
    {
        return $this->hasMany(OfficeEmailAddress::class);
    }


    /**
     * Offices can have more than 1 OfficeHour.
     */
    public function hours(): HasMany
    {
        return $this->hasMany(OfficeHour::class);
    }


    /**
     * Offices belong to a Brokerage.
     */
    public function brokerage(): BelongsTo
    {
        return $this->belongsTo(Brokerage::class);
    }
}
