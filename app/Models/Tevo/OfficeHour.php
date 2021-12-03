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
    ];

    protected $casts = [
        'id'          => 'integer',
        'office_id'   => 'integer',
        'day_of_week' => 'integer',
        'closed'      => 'boolean',
        'open'        => 'datetime:H:i',
        'close'       => 'datetime:H:i',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'deleted_at'  => 'datetime',
    ];


    /**
     * OfficeHours belong to an Office.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
