<?php namespace App\Models\Tevo;


trait HasStreetAddress
{
    protected static function setStreetAddress($result):array {
        $result['street_address'] = (trim($result['address']['street_address']) === '') ? null : trim($result['address']['street_address']);
        $result['extended_address'] = (trim($result['address']['extended_address']) === '') ? null : trim($result['address']['extended_address']);
        $result['locality'] = (trim($result['address']['locality']) === '') ? null : trim($result['address']['locality']);
        $result['region'] = (trim($result['address']['region']) === '') ? null : trim($result['address']['region']);
        $result['postal_code'] = (trim($result['address']['postal_code']) === '') ? null : trim($result['address']['postal_code']);
        $result['country_code'] = (trim($result['address']['country_code']) === '') ? null : trim($result['address']['country_code']);
        $result['latitude'] = (trim($result['address']['latitude']) === '') ? null : trim($result['address']['latitude']);
        $result['longitude'] = (trim($result['address']['longitude']) === '') ? null : trim($result['address']['longitude']);
        $result['po_box'] = $result['address']['po_box'] ?? 0;

        unset($result['address']);

        return $result;
    }
}
