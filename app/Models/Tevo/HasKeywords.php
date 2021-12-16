<?php

namespace App\Models\Tevo;


trait HasKeywords
{
    /**
     * Mutator to nullify empty value.
     */
    public function setKeywordsAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['keywords'] = null;
        } else {
            $this->attributes['keywords'] = $value;
        }
    }
}
