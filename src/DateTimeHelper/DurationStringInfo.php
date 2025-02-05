<?php
/**
 * @package Application Utils
 * @subpackage DateTimeHelper
 */

declare(strict_types=1);

namespace AppUtils\DateTimeHelper;

use AppUtils\Interfaces\StringableInterface;
use function AppUtils\parseDurationString;
use function AppUtils\sb;

/**
 * Parses a standardized, human-readable duration string into
 * its individual parts to convert it to a Date Interval and
 * access relevant information on it.
 *
 * ## Expected format
 *
 * `1d 2h 3m 4s`
 *
 * - All parts are optional.
 * - They can be in any order.
 * - They can be repeated (`5m 10m 40m`).
 * - They can contain any amount of whitespace.
 *
 * ## Supported time units
 *
 * - d / day / days
 * - h / hour / hours
 * - m / minute / minutes
 * - s / second / seconds
 *
 * ## Value aggregation
 *
 * If multiple values of the same type are specified, they will
 * be aggregated into a single value.
 *
 * ## Usage
 *
 * Use either the global function {@see parseDurationString()}
 * or the static method {@see self::create()} to parse a duration
 * string.
 *
 * @package Application Utils
 * @subpackage DateTimeHelper
 */
class DurationStringInfo implements StringableInterface
{
    private int $days = 0;
    private int $hours = 0;
    private int $minutes = 0;
    private int $seconds = 0;
    private string $leftovers = '';

    protected function __construct(string $duration)
    {
        $this->parseDuration($duration);
    }

    /**
     * @param string|null $duration Allowing `NULL` for convenience.
     * @return DurationStringInfo
     */
    public static function create(?string $duration) : DurationStringInfo
    {
        return new DurationStringInfo((string)$duration);
    }

    private function parseDuration(string $duration) : void
    {
        preg_match_all('/([0-9]+)\s*(d|m|h|s|day|days|hour|hours|minute|minutes|second|seconds)/i', $duration, $matches);

        $leftover = $duration;
        foreach($matches[0] as $index => $match)
        {
            $leftover = str_replace($match, '', $leftover);
            $value = (int)$matches[1][$index];
            $unit = strtolower($matches[2][$index]);

            switch($unit)
            {
                case 'd':
                case 'day':
                case 'days':
                    $this->days += $value;
                    break;

                case 'h':
                case 'hour':
                case 'hours':
                    $this->hours += $value;
                    break;

                case 'm':
                case 'minute':
                case 'minutes':
                    $this->minutes += $value;
                    break;

                case 's':
                case 'second':
                case 'seconds':
                    $this->seconds += $value;
                    break;
            }
        }

        $this->leftovers = trim($leftover);
    }

    /**
     * Whether the duration string could be successfully parsed.
     *
     * Will return false if any non-whitespace part of the string
     * could not be recognized as valid duration information.
     *
     * > NOTE: A `0` total second duration is considered valid.
     *
     * @return bool
     */
    public function isValid() : bool
    {
        return empty($this->leftovers);
    }

    /**
     * Gets the part of the duration string that could not be recognized
     * as valid duration information.
     *
     * @return string
     */
    public function getInvalidText() : string
    {
        return $this->leftovers;
    }

    public function getInterval() : DateIntervalExtended
    {
        return DateIntervalExtended::fromSeconds($this->getTotalSeconds());
    }

    /**
     * Returns a normalized version of the duration
     * string.
     *
     * This will contain aggregated values in case
     * multiple values of the same type were specified.
     * The order will be `days`, `hours`, `minutes`, `seconds`.
     *
     * @return string
     */
    public function getNormalized() : string
    {
        $result = sb();

        $interval = $this->getInterval();

        $days = $interval->getDays();
        if($days > 0) {
            $result->add($days.'d');
        }

        $hours = $interval->getHours();
        if($hours > 0) {
            $result->add($hours.'h');
        }

        $minutes = $interval->getMinutes();
        if($minutes > 0) {
            $result->add($minutes.'m');
        }

        $seconds = $interval->getSeconds();
        if($seconds > 0) {
            $result->add($seconds.'s');
        }

        return (string)$result;
    }

    public function __toString() : string
    {
        return $this->getNormalized();
    }

    public function getDays() : int
    {
        return $this->days;
    }

    public function getHours() : int
    {
        return $this->hours;
    }

    public function getMinutes() : int
    {
        return $this->minutes;
    }

    public function getSeconds() : int
    {
        return $this->seconds;
    }

    public function getTotalSeconds() : int
    {
        return
            $this->seconds +
            ($this->minutes * 60) +
            ($this->hours * 3600) +
            ($this->days * 86400);
    }
}