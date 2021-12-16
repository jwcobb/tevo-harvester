<?php namespace App\Models\Tevo;


trait HasUpcomingEvents
{
    protected static function setUpcomingEvents($result): array {
        $result['upcoming_event_first'] = $result['upcoming_events']['first'] ?? null;
        $result['upcoming_event_last'] = $result['upcoming_events']['last'] ?? null;

        unset($result['upcoming_events']);

        return $result;
    }
}
