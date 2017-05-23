<?php namespace App\Tevo;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event as EventFacade;
use App\Events\ItemWasDeleted;
use App\Events\ItemWasStored;

class Configuration extends Model
{
    use StoresFromApi;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'configurations';

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
     * The attributes that may be NULL.
     *
     * @var array
     */
    protected $nullable = [
        'capacity',
        'seating_chart_url_medium',
        'seating_chart_url_large',
    ];


    /**
     * Mutate the $result as necessary.
     * Be sure to run the parent::mutateApiResult() to get the common mutations.
     *
     * @param array $result
     *
     * @return array
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
     *
     * @return array
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }


    /**
     * Configurations belong to a Venue.
     *
     * @return array
     */
    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
}
