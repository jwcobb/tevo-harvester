<?php

namespace App\Models\Tevo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Event as EventFacade;
use App\Events\ItemWasDeleted;
use App\Events\ItemWasStored;

/**
 * @mixin IdeHelperConfiguration
 */
class Configuration extends Model
{
    use StoresFromApi;

    protected $table = 'configurations';

    protected $fillable = [
        'id',
        'venue_id',
        'name',
        'primary',
        'general_admission',
        'capacity',
        'url',
        'seating_chart_url_medium',
        'seating_chart_url_large',
        'configuration_url',
        'tevo_created_at',
        'tevo_updated_at',
        'tevo_deleted_at',
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
        $result['venue_id'] = $result['venue']['id'];
        unset($result['venue']);

        $result['seating_chart_url_medium'] = $result['seating_chart']['medium'] ?? null;
        $result['seating_chart_url_large'] = $result['seating_chart']['large'] ?? null;
        unset($result['seating_chart']);

        return $result;
    }


    /**
     * Configurations can have more than 1 Event.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }


    /**
     * Configurations belong to a Venue.
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
