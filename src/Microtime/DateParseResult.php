<?php
/**
 * File containing the class {@see \AppUtils\Microtime\DateParseResult}.
 *
 * @package Application Utils
 * @subpackage Microtime
 * @see \AppUtils\Microtime\DateParseResult
 */

declare(strict_types=1);

namespace AppUtils\Microtime;

use AppUtils\Interfaces\StringableInterface;
use AppUtils\Microtime\TimeZones\NamedTimeZoneInfo;
use AppUtils\Microtime\TimeZones\OffsetParser;
use AppUtils\Microtime\TimeZones\TimeZoneInfo;
use AppUtils\Microtime_Exception;
use DateTimeZone;

/**
 * Date parsing result, containing the date string
 * and time zone to use for the DateTime constructor.
 *
 * This is used to simplify creating a new microtime
 * instance when using the factory methods, to avoid
 * the type checks that are done when using the
 * constructor.
 *
 * @package Application Utils
 * @subpackage Microtime
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DateParseResult implements StringableInterface
{
    private string $dateTime;
    private DateTimeZone $timeZone;
    private ?TimeZoneInfo $timeZoneOffset = null;
    private int $nanoseconds = 0;

    public function __construct(string $datetime, ?DateTimeZone $timeZone=null)
    {
        if($timeZone === null) {
            $timeZone = new DateTimeZone(date_default_timezone_get());
        }

        $this->dateTime = $datetime;
        $this->timeZone = $timeZone;

        $this->detectMicroseconds();

        if(stripos($this->dateTime, 'T') !== false) {
            $this->detectTimeZoneOffset();
        }

        if(strpos($this->dateTime, '/') !== false) {
            $this->detectTimeZoneName();
        }
    }

    public function getNanoseconds(): int
    {
        return $this->nanoseconds;
    }

    /**
     * Detects microseconds in the date string and adjusts
     * the string to be PHP DateTime compatible by removing
     * the nanosecond information, if present.
     *
     * Example nanosecond string:
     *
     * `22.666777888`
     *
     * The following parts are extracted:
     *
     * - `22` = Seconds
     * - `666` = Milliseconds
     * - `666777` = Microseconds
     * - `666777888` = Nanoseconds
     *
     * @return void
     */
    private function detectMicroseconds() : void
    {
        preg_match('/([0-9]{2})\.([0-9]{6,9})/', $this->dateTime, $matches);

        if(empty($matches[0])) {
            return;
        }

        $seconds = $matches[1] ?? '';
        // Account for the possibility of less than 9 digits
        $nanoseconds = str_pad($matches[2] ?? '', 9, '0', STR_PAD_RIGHT);
        $microseconds = substr($nanoseconds, 0, 6);

        $adjusted = sprintf(
            '%s.%s',
            $seconds,
            $microseconds
        );

        $this->nanoseconds = (int)$nanoseconds;

        $this->dateTime = str_replace(
            $matches[0],
            $adjusted,
            $this->dateTime
        );
    }

    /**
     * @return TimeZoneInfo|NamedTimeZoneInfo|NULL
     */
    public function getTimeZoneInfo() : ?TimeZoneInfo
    {
        return $this->timeZoneOffset;
    }

    /**
     * @return void
     * @throws Microtime_Exception
     */
    private function detectTimeZoneOffset() : void
    {
        // The regular expression focuses on the time information only
        preg_match('/(UTC|GMT|Z)|([a-z]+\/[a-z]+)|([+-][0-9]{2}+:[0-9]{2}+)|([+-][0-9]{4})/i', $this->dateTime, $matches);

        if(empty($matches[0])) {
            return;
        }

        $matches = $this->emptyToNull($matches);

        $this->timeZoneOffset = TimeZoneInfo::create(
            $matches[1] ?? // Z / UTC / GMT
            $matches[2] ?? // Europe/Paris
            $matches[3] ?? // +02:00
            $matches[4] ?? // +0200
            ''
        );

        $this->timeZone = $this->timeZoneOffset->getDateTimeZone();
    }

    /**
     * @param array<int,string> $values
     * @return array<int,string|null>
     */
    private function emptyToNull(array $values) : array
    {
        $result = array();

        foreach($values as $value) {
            if(empty($value)) {
                $result[] = null;
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Parses the custom time zone string if present.
     *
     * @return void
     * @throws Microtime_Exception
     */
    private function detectTimeZoneName() : void
    {
        $offsets = OffsetParser::getIdentifierOffsets();

        foreach($offsets as $offset)
        {
            $pos = stripos($this->dateTime, $offset['identifier']);

            if($pos === false) {
                continue;
            }

            $matched = substr($this->dateTime, $pos, strlen($offset['identifier']));

            // Remove the timezone part from the date string, so that
            // the DateTime constructor can parse the date.
            $this->dateTime = str_replace($matched, '', $this->dateTime);
            $this->timeZoneOffset = TimeZoneInfo::createFromName($offset['identifier']);
            $this->timeZone = $this->timeZoneOffset->getDateTimeZone();
            return;
        }
    }

    public function __toString() : string
    {
        return $this->getDateTime();
    }

    /**
     * @return string
     */
    public function getDateTime() : string
    {
        return $this->dateTime;
    }

    /**
     * @return DateTimeZone
     */
    public function getTimeZone() : DateTimeZone
    {
        return $this->timeZone;
    }
}
