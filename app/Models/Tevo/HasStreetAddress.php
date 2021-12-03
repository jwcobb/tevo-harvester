<?php namespace App\Models\Tevo;


trait HasStreetAddress
{
    protected static function setStreetAddress($result):array {
        $result['street_address'] = $result['address']['street_address'];
        $result['extended_address'] = $result['address']['extended_address'];
        $result['locality'] = $result['address']['locality'];
        $result['region'] = $result['address']['region'];
        $result['postal_code'] = $result['address']['postal_code'];
        $result['country_code'] = $result['address']['country_code'];
        $result['po_box'] = $result['address']['po_box'] ?? null;
        $result['latitude'] = $result['address']['latitude'];
        $result['longitude'] = $result['address']['longitude'];

        unset($result['address']);

        return $result;
    }
}
