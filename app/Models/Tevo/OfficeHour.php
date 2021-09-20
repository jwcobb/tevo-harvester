<?php

namespace App\Models\Tevo;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperOfficeHour
 */
class OfficeHour extends Model
{
    protected $table = 'office_hours';

    protected $fillable = [
        'id',
        'office_id',
        'day_of_week',
        'closed',
        'open',
        'close',
        'office_url',
        'tevo_created_at',
        'tevo_updated_at',
        'tevo_deleted_at',
    ];


    /**
     * OfficeHours belong to an Office.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
