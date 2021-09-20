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
        'url',
        'tevo_created_at',
        'tevo_updated_at',
        'tevo_deleted_at',
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
