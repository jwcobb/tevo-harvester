<?php

namespace App\Models\Tevo;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperOfficeEmailAddress
 */
class OfficeEmailAddress extends Model
{
    protected $table = 'office_email_addresses';

    protected $fillable = [
        'id',
        'office_id',
        'email_address',
    ];

    protected $casts = [
        'id'            => 'integer',
        'office_id'     => 'integer',
        'email_address' => 'string',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];


    /**
     * OfficeEmailAddresses belong to an Office.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }


    /**
     * Mutator to lowercase email address.
     */
    public function setEmailAddressAttribute($value): void
    {
        $this->attributes['email_address'] = strtolower($value);
    }
}
