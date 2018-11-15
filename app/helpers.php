<?php

use Cake\Chronos\Chronos;


/**
 * Get a span of years from two given years.
 * Perfect for keeping the copyright date on a site updated using
 *     getYearSpan($firstYear, date('Y'))
 * and getting back '2015–2017' but never '2017–2017'
 *
 * @param $firstYear
 * @param $lastYear
 *
 * @return string
 */
function getYearSpan($firstYear, $lastYear): string
{
    if ($lastYear > $firstYear) {
        return $firstYear . '–' . $lastYear;
    }

    return $firstYear;
}


/**
 * Get a string of stars to output for ratings.
 *
 * @param     $rating
 * @param int $max
 *
 * @return string
 */
function toStars($rating, $max = 5): string
{
    if (!is_numeric($rating)) {
        return $rating;
    }

    $string = str_repeat('★', floor($rating));
    if (($rating - floor($rating)) === .5) {
        $string .= '½';
    }

    return str_pad($string, $max, '☆', STR_PAD_RIGHT);
}


/**
 * Fix Ticket Evolution’s event "occurs_at" values, which are in local time,
 * but have the Z time zone indicator appended to them.
 *
 * @param $datetime
 *
 * @return Chronos
 */
function chronosize($datetime): Chronos
{
    return Chronos::parse(rtrim($datetime, 'Z'));
}


/**
 * Format an event date/time by first checking if the time is TBA
 * and removing any time formatting characters from the supplied format
 * and then append the TBA text instead.
 *
 * This function expects "00:00:00" as the TBA time which Ticket Evolution uses.
 *
 * @param DateTimeImmutable $datetime
 * @param string            $format
 *
 * @return string
 */
function getTbaFormat(DateTimeImmutable $datetime, string $format): string
{
    if ($datetime->format('His') === '000000') {
        return $datetime->format(preg_replace('/(?:[aABgGhHisuveIOPTZcr]| \\\a\\\t )+[ :]?[aABgGhHisuveIOPTZcr]*/', '',
                $format)) . config('app.options.tba_format');
    } else {
        return $datetime->format($format);
    }
}


/**
 * Used to return 'active' for use in Bootstrap links to indicate if the
 * link is the currently active URL/page.
 *
 * @see https://medium.com/@lamaaurizkhal/create-a-helper-for-active-link-in-laravel-5-6-30827a760593
 * @see https://laracasts.com/discuss/channels/general-discussion/whats-the-cleanest-way-to-add-the-active-class-to-bootstrap-link-components/replies/4321
 *
 * @param DateTimeImmutable $datetime
 * @param string            $format
 *
 * @return string
 */
function setActiveRouteClass($path, $active = 'active')
{
    return call_user_func_array('Request::is', (array)$path) ? $active : '';
}


/**
 * Create Form select list options
 */
function getSelectListOptions(array $options, $selected = null)
{
    $html = '';
    $currentOptGroup = null;
    foreach ($options as $value => $display) {
        // If this is not an associative array use $display for $value as well
        if (is_numeric($value)) {
            $value = $display;
        }
        if (is_array($display)) {
            if ($currentOptGroup !== null) {
                $html .= '</optgroup>' . PHP_EOL;
                $currentOptGroup = null;
            }
            $currentOptGroup = $value;
            $html .= '<optgroup label="' . $value . '">' . PHP_EOL;
            $html .= getSelectListOptions($display, $selected);
        } else {
            if ($value === $selected) {
                $html .= '<option value="' . $value . '" selected="selected">' . $display . '</option>' . PHP_EOL;
            } else {
                $html .= '<option value="' . $value . '">' . $display . '</option>' . PHP_EOL;
            }
        }
    }
    if ($currentOptGroup !== null) {
        $html .= '</optgroup>' . PHP_EOL;
    }
    return $html;
}