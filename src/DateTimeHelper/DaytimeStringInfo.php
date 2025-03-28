<?php
/**
 * @package Application Utils
 * @subpackage DateTime Helper
 */

declare(strict_types=1);

namespace AppUtils\DateTimeHelper;

use AppUtils\ConvertHelper;
use AppUtils\DateTimeHelper;
use AppUtils\Traits\SimpleErrorStateInterface;
use AppUtils\Traits\SimpleErrorStateTrait;
use function AppUtils\parseDaytimeString;
use function AppUtils\parseNumberImmutable;
use function AppUtils\sb;
use function AppUtils\t;

/**
 * Utility class that parses a string representing a time of day
 * and provides information about it.
 *
 * ## Features
 *
 * - Allowed values range from `00:00` to `23:59`.
 * - The time can be empty, in which case it defaults to `00:00`.
 * - The time can be specified with or without leading zeroes.
 * - Free-spacing: Whitespace-agnostic.
 * - Free separator choice, see {@see self::ALLOWED_SEPARATOR_CHARS}.
 * - The time can be rounded to the nearest quarter-hour or custom values.
 * - Validation with meaningful error messages.
 *
 * ## Usage
 *
 * Use the global function {@see parseDaytimeString()} to parse
 * a time string.
 *
 * Alternatively, use any of the static methods:
 *
 * - {@see self::fromString()} to parse a string.
 * - {@see self::fromSeconds()} to parse a number of seconds.
 *
 * @package Application Utils
 * @subpackage DateTime Helper
 */
class DaytimeStringInfo implements SimpleErrorStateInterface
{
    use SimpleErrorStateTrait;

    public const VALIDATION_INVALID_HOUR = 171701;
    public const VALIDATION_UNRECOGNIZED_TIME_FORMAT = 171702;
    public const VALIDATION_INVALID_MINUTE = 171703;

    public const DEFAULT_EMPTY_TIME_TEXT = '--:--';

    public const ALLOWED_SEPARATOR_CHARS = array(
        ':',
        ' ',
        '-',
        '.',
        '/',
        '_',
        ',',
        '+',
    );

    private int $hours = 0;
    private int $minutes = 0;
    public static string $emptyTimeText = self::DEFAULT_EMPTY_TIME_TEXT;
    private bool $empty = false;

    protected function __construct(?string $timeString)
    {
        if(empty($timeString)) {
            $this->empty = true;
            $timeString = '00:00';
        }

        $this->parse($timeString);
    }

    /**
     * Automatically detects the kind of input and returns a new instance.
     * This also allows passing an existing instance of this class, which
     * will return that same instance without modifying it.
     *
     * @param string|int|DaytimeStringInfo|NULL $time String value = time string.
     *                                                Integer value = seconds to time.
     *                                               `null` = empty 00:00 time.
     *                                                Daytime instance = returns the same instance.
     * @return DaytimeStringInfo
     */
    public static function fromAuto($time) : DaytimeStringInfo
    {
        if($time instanceof self) {
            return $time;
        }

        if(is_int($time)) {
            return self::fromSeconds($time);
        }

        return self::fromString($time);
    }

    /**
     * Parses a time string and returns a new instance.
     *
     * @param string|null $timeString
     * @return DaytimeStringInfo
     */
    public static function fromString(?string $timeString) : DaytimeStringInfo
    {
        $timeString = trim((string)$timeString);

        return new self($timeString);
    }

    /**
     * Creates a new instance from a number of seconds, which are
     * converted to hours and minutes.
     *
     * > NOTE: An out-of-bounds value will be validated just like
     * > a parsed date string, so use {@see self::isValid()} to check
     * > if the returned time is valid.
     *
     * @param int $seconds
     * @return DaytimeStringInfo
     */
    public static function fromSeconds(int $seconds) : DaytimeStringInfo
    {
        $hours = (int)($seconds / DateTimeHelper::SECONDS_PER_HOUR);
        $minutes = (int)(($seconds % DateTimeHelper::SECONDS_PER_HOUR) / DateTimeHelper::SECONDS_PER_MINUTE);

        return self::fromString(sprintf('%02d:%02d', $hours, $minutes));
    }

    private function parse(string $timeString) : void
    {
        $timeString = str_replace(array("\t", "\n", "\r"), ' ', $timeString);

        while (strpos($timeString, '  ') !== false) {
            $timeString = str_replace('  ', ' ', $timeString);
        }

        $parts = ConvertHelper::explodeTrim($this->detectSeparator($timeString), $timeString);

        // If seconds are present, ignore them.
        if(count($parts) === 3) {
            array_pop($parts);
        }

        if(count($parts) !== 2) {
            $this->checkFormat($parts, $timeString);
            return;
        }

        $hour = (int)parseNumberImmutable($parts[0])->getNumber();
        $minute = (int)parseNumberImmutable($parts[1])->getNumber();

        if($hour < 0 || $hour > 23) {
            $this->setError(
                t(
                    'Invalid hour value, it must be between %1$s and %2$s.',
                    '0',
                    '23'
                ),
                self::VALIDATION_INVALID_HOUR
            );
            return;
        }

        if($minute < 0 || $minute > 59) {
            $this->setError(
                t(
                    'Invalid minute value, it must be between %1$s and %2$s.',
                    '0',
                    '59'
                ),
                self::VALIDATION_INVALID_MINUTE
            );
            return;
        }

        $this->hours = $hour;
        $this->minutes = $minute;
    }

    private function detectSeparator(string $timeString) : string
    {
        foreach(self::ALLOWED_SEPARATOR_CHARS as $separator) {
            if(strpos($timeString, $separator) !== false) {
                return $separator;
            }
        }

        return ':';
    }

    private function checkFormat(array $parts, string $time) : void
    {
        if(strpos($time, ':') === false) {
            $this->setError(
                t('The time string must contain a colon separator, e.g. %1$s.', '`09:45`'),
                self::VALIDATION_UNRECOGNIZED_TIME_FORMAT
            );
            return;
        }

        if(count($parts) === 1) {
            $this->setError(
                (string)sb()
                    ->t('The time string must contain both hours and minutes, e.g. %1$s.', '`09:45`')
                    ->t('To leave either side of the colon empty, use %1$s.', '`00`'),
                self::VALIDATION_UNRECOGNIZED_TIME_FORMAT
            );
            return;
        }

        if(count($parts) > 2) {
            $this->setError(
                t('The time string must contain only hours and minutes, separated by a single colon.'),
                self::VALIDATION_UNRECOGNIZED_TIME_FORMAT
            );
            return;
        }

        $this->setError(
            t('Unrecognized time string format.'),
            self::VALIDATION_UNRECOGNIZED_TIME_FORMAT
        );
    }

    /**
     * Rounds the time to the nearest quarter-hour.
     *
     * @return DaytimeStringInfo A new instance with the rounded time.
     */
    public function roundToQuarterHour() : DaytimeStringInfo
    {
        return $this->roundTo(15);
    }

    /**
     * Rounds the time to the nearest minute interval (up and down).
     *
     * @param int $minutes
     * @return DaytimeStringInfo A new instance with the rounded time.
     * @throws DateTimeException {@see DateTimeException::ERROR_OPERATION_DENIED_ON_INVALID_DAYTIME}
     */
    public function roundTo(int $minutes) : DaytimeStringInfo
    {
        $this->requireValid();

        $diff = $this->minutes % $minutes;
        $maxDiff = $minutes / 2;

        if ($diff > $maxDiff) {
            $target = $this->minutes + ($minutes-$diff);
        }else{
            $target = $this->minutes - $diff;
        }

        if($target === 60) {
            $target = 0;
            $this->hours++;

            if($this->hours === 24) {
                $this->hours = 0;
            }
        }

        return self::fromString(sprintf('%02d:%02d', $this->hours, $target));
    }

    /**
     * Normalizes the time to a string in the format `HH:MM`.
     * @return string
     */
    public function getNormalized() : string
    {
        return sprintf('%02d:%02d', $this->hours, $this->minutes);
    }

    /**
     * Returns the normalized time or the empty time string
     * if the time value is empty.
     *
     * > NOTE: The empty time string used can be customized
     * > using {@see self::setEmptyTimeText()}.
     *
     * @return string
     */
    public function toReadable() : string
    {
        if(!$this->isEmpty()) {
            return $this->getNormalized();
        }

        return self::$emptyTimeText;
    }

    /**
     * Sets a custom text to use when using {@see self::toReadable()} with
     * an empty time value.
     *
     * @param string $string
     * @return void
     */
    public static function setEmptyTimeText(string $string) : void
    {
        self::$emptyTimeText = $string;
    }

    /**
     * Resets the empty time text to the default ({@see self::DEFAULT_EMPTY_TIME_TEXT}).
     * @return void
     */
    public static function resetEmptyTimeText() : void
    {
        self::setEmptyTimeText(self::DEFAULT_EMPTY_TIME_TEXT);
    }

    /**
     * Checks whether this time was created with an empty
     * value (an empty string or `NULL`).
     *
     * Since an empty value is interpreted as `00:00`, this
     * makes it possible to distinguish between midnight and
     * an empty value.
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->empty;
    }

    public function getHours() : int
    {
        return $this->hours;
    }

    public function getMinutes() : int
    {
        return $this->minutes;
    }

    private ?int $totalSeconds = null;

    /**
     * Aggregates the total number of seconds in the hours and minutes.
     * This allows converting the time to integer, and back using
     * {@see self::fromSeconds()}.
     *
     * @return int
     */
    public function getTotalSeconds() : int
    {
        if(!isset($this->totalSeconds)) {
            $this->totalSeconds =
                ($this->hours * DateTimeHelper::SECONDS_PER_HOUR) +
                ($this->minutes * DateTimeHelper::SECONDS_PER_MINUTE);
        }

        return $this->totalSeconds;
    }

    /**
     * Time between >= 5:00 and < 12:00
     * @return bool
     */
    public function isMorning() : bool
    {
        return $this->hours >= 5 && $this->hours < 12;
    }

    /**
     * Time between >= 12:00 and < 13:00
     * @return bool
     */
    public function isNoon() : bool
    {
        return $this->hours >= 12 && $this->hours < 13;
    }

    /**
     * Time between >= 12:00 and < 17:00
     * @return bool
     */
    public function isAfternoon() : bool
    {
        return $this->hours >= 12 && $this->hours < 17;
    }

    /**
     * Time between >= 17:00 and < 21:00
     * @return bool
     */
    public function isEvening() : bool
    {
        return $this->hours >= 17 && $this->hours < 21;
    }

    /**
     * Time between (>= 21:00 and <= 23:00) or (>= 00:00 and <= 05:00)
     * @return bool
     */
    public function isNight() : bool
    {
        return
            ($this->hours >= 0 && $this->hours <= 5)
            ||
            ($this->hours >= 21 && $this->hours <= 23);
    }

    /**
     * Requires this time to be valid, and throws an exception if it is not.
     *
     * @return $this
     * @throws DateTimeException {@see DateTimeException::ERROR_OPERATION_DENIED_ON_INVALID_DAYTIME}
     */
    public function requireValid() : self
    {
        if($this->isValid()) {
            return $this;
        }

        throw new DateTimeException(
            'Date operation not permitted on an invalid daytime value.',
            sprintf(
                'The daytime value is invalid: #%s [%s]',
                $this->errorCode,
                $this->errorMessage
            ),
            DateTimeException::ERROR_OPERATION_DENIED_ON_INVALID_DAYTIME
        );
    }

    /**
     * Checks if the specified time is before/earlier than this time.
     * @param string|int|DaytimeStringInfo|NULL $time
     * @return bool
     */
    public function isAfter($time) : bool
    {
        return $this->getTotalSeconds() > self::fromAuto($time)->getTotalSeconds();
    }

    /**
     * Checks if the specified time is after/later than this time.
     * @param string|int|DaytimeStringInfo|NULL $time
     * @return bool
     */
    public function isBefore($time) : bool
    {
        return $this->getTotalSeconds() < self::fromAuto($time)->getTotalSeconds();
    }

    /**
     * Gets the difference in seconds between two times.
     *
     * @param string|int|DaytimeStringInfo|NULL $time
     * @return int Seconds difference: Positive or negative value, depending on whether the specified time is earlier or later than this time.
     */
    public function getTimeDifference($time) : int
    {
        return self::fromAuto($time)->getTotalSeconds() - $this->getTotalSeconds();
    }
}