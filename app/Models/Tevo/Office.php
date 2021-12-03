<?php

namespace App\Models\Tevo;

use App\Events\ItemWasDeleted;
use App\Events\ItemWasStored;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @mixin IdeHelperOffice
 */
class Office extends Model
{
    use StoresFromApi, HasStreetAddress;

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
        'phone',
        'fax',
        'po_box',
        'timezone',
        'pos',
        'evopay',
        'evopay_discount',
        'url',
        'fedex_pickup_dropoff_time',
        'tevo_created_at',
        'tevo_updated_at',
        'tevo_deleted_at',
    ];

    protected $casts = [
        'id'                        => 'integer',
        'brokerage_id'              => 'integer',
        'name'                      => 'string',
        'main'                      => 'boolean',
        'street_address'            => 'string',
        'extended_address'          => 'string',
        'locality'                  => 'string',
        'region'                    => 'string',
        'postal_code'               => 'string',
        'country_code'              => 'string',
        'latitude'                  => 'decimal:17',
        'longitude'                 => 'decimal:17',
        'phone'                     => 'string',
        'fax'                       => 'string',
        'po_box'                    => 'boolean',
        'timezone'                  => 'string',
        'pos'                       => 'boolean',
        'evopay'                    => 'boolean',
        'evopay_discount'           => 'decimal:4',
        'url'                       => 'string',
        'fedex_pickup_dropoff_time' => 'datetime',
        'tevo_created_at'           => 'datetime',
        'tevo_updated_at'           => 'datetime',
        'tevo_deleted_at'           => 'datetime',
        'created_at'                => 'datetime',
        'updated_at'                => 'datetime',
        'deleted_at'                => 'datetime',
    ];


    /**
     * When deleting an Office deleted the related
     * OfficeHours and OfficeEmailAddresses
     */
    public static function boot()
    {
        parent::boot();

        self::deleting(function ($office) {
            $office->emailAddresses()->each(function ($emailAddress) {
                $emailAddress->delete();
            });

            $office->hours()->each(function ($hours) {
                $hours->delete();
            });
        });
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
            $result = self::setStreetAddress($result);
        }

        return $result;
    }


    /**
     * Any operations that need to be run after save()
     * such as saving related Models can go here.
     */
    protected static function postSave(Model $office, array $result): Model
    {
        /**
         * Insert/Delete/Update OfficeEmailAddresses as necessary
         */
        if (! empty($result['email'])) {
            $validOfficeEmailAddresses = [];

            foreach ($result['email'] as $resultEmailAddress) {
                $resultEmailAddress = strtolower($resultEmailAddress);
                $validOfficeEmailAddresses[] = $resultEmailAddress;

                OfficeEmailAddress::withTrashed()->updateOrCreate(
                    [
                        'office_id'     => $office->id,
                        'email_address' => $resultEmailAddress,
                    ],
                    [
                        'deleted_at' => null,
                    ]);
            }

            // Delete any OfficeEmailAddresses for this Office
            // that were not in $result['email']
            OfficeEmailAddress::where('office_id', '=', $office->id)
                ->whereNotIn('email_address', $validOfficeEmailAddresses)
                ->delete();

            unset($validOfficeEmailAddresses);
        } else {
            OfficeEmailAddress::where('office_id', '=', $office->id)
                ->delete();
        }


        /**
         * Insert/Delete/Update OfficeHour as necessary
         */
        if (! empty($result['hours'])) {
            $validOfficeHourIds = [];

            foreach ($result['hours'] as $operatingHours) {
                foreach ($operatingHours as $hour) {
                    $validOfficeHourIds[] = $hour['id'];

                    OfficeHour::withTrashed()->updateOrCreate(
                        [
                            'id' => $hour['id'],
                        ],
                        [
                            'office_id'   => $office->id,
                            'day_of_week' => $hour['day_of_week'],
                            'closed'      => $hour['closed'],
                            'open'        => Carbon::parse($hour['open'])->toTimeString(),
                            'close'       => Carbon::parse($hour['close'])->toTimeString(),
                            'deleted_at'  => null,
                        ]);
                }
            }

            // Delete any OfficeHours for this Office
            // that were not in $result['hours']
            OfficeHour::whereNotIn('id', $validOfficeHourIds)
                ->delete();

            unset($validOfficeHourIds);
        } else {
            OfficeHour::where('office_id', '=', $office->id)
                ->delete();
        }

        return $office;
    }
}
