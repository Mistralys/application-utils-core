<?php

declare(strict_types=1);

namespace AppUtils;

use AppUtils\DateTimeHelper\DurationStringInfo;
use AppUtils\DateTimeHelper\IntervalConverter;
use AppUtils\DateTimeHelper\DateIntervalExtended;
use AppUtils\DateTimeHelper\DurationConverter;
use AppUtils\DateTimeHelper\TimeConverter;
use DateInterval;
use DateTime;

class DateTimeHelper
{
    public const SECONDS_PER_MINUTE = 60;
    public const SECONDS_PER_HOUR = 3600;
    public const SECONDS_PER_DAY = 86400;
    public const SECONDS_PER_WEEK = 604800;

    /**
     * @var array<int,string[]>
     */
    protected static array $months = array();

    /**
     * @var string[]
     */
    protected static array $days = array();

    /**
     * @var string[]
     */
    protected static array $daysShort = array();

    /**
     * @var string[]
     */
    protected static array $daysInvariant = array(
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    );

    /**
     * Converts the specified number of seconds into
     * a human-readable string split in months, weeks,
     * days, hours, minutes and seconds.
     *
     * @param float|int $seconds
     * @return string
     */
    public static function time2string($seconds) : string
    {
        return (new TimeConverter($seconds))->toString();
    }

    /**
     * Converts a timestamp into an easily understandable
     * format, e.g. "2 hours", "1 day", "3 months"
     *
     * If you set the date to parameter, the difference
     * will be calculated between the two dates and not
     * the current time.
     *
     * @param integer|DateTime $datefrom
     * @param integer|DateTime $dateto
     * @return string
     *
     * @throws ConvertHelper_Exception
     * @see DurationConverter::ERROR_NO_DATE_FROM_SET
     */
    public static function duration2string($datefrom, $dateto = -1) : string
    {
        return DurationConverter::toString($datefrom, $dateto);
    }

    /**
     * Converts a standardized duration string (e.g. `1h 30m 14s`)
     * into a DateInterval object. See the {@see DurationStringInfo}
     * class for details.
     *
     * @param string $durationString
     * @return DateIntervalExtended
     * @throws ConvertHelper_Exception
     */
    public static function durationString2interval(string $durationString) : DateIntervalExtended
    {
        return DateIntervalExtended::fromDurationString($durationString);
    }

    /**
     * Converts a date to the corresponding day name.
     *
     * @param DateTime $date
     * @param bool $short
     * @return string|NULL
     */
    public static function toDayName(DateTime $date, bool $short=false) : ?string
    {
        $day = $date->format('l');
        $invariant = self::getDayNamesInvariant();

        $idx = array_search($day, $invariant);
        if($idx !== false) {
            $localized = self::getDayNames($short);
            return $localized[$idx];
        }

        return null;
    }

    /**
     * Retrieves a list of english day names.
     * @return string[]
     */
    public static function getDayNamesInvariant() : array
    {
        return self::$daysInvariant;
    }

    /**
     * Retrieves the day names list for the current locale.
     *
     * @param bool $short
     * @return string[]
     */
    public static function getDayNames(bool $short=false) : array
    {
        self::initDays();

        if($short) {
            return self::$daysShort;
        }

        return self::$days;
    }

    /**
     * Transforms a date into a generic human-readable date, optionally with time.
     * If the year is the same as the current one, it is omitted.
     *
     * - 6 Jan 2012
     * - 12 Dec 2012 17:45
     * - 5 Aug
     *
     * @param DateTime $date
     * @param bool $includeTime
     * @param bool $shortMonth
     * @return string
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_MONTHTOSTRING_NOT_VALID_MONTH_NUMBER
     */
    public static function toListLabel(DateTime $date, bool $includeTime = false, bool $shortMonth = false) : string
    {
        $today = new DateTime();
        if($date->format('d.m.Y') === $today->format('d.m.Y'))
        {
            $label = t('Today');
        }
        else
        {
            $label = $date->format('d') . '. ' . self::month2string((int)$date->format('m'), $shortMonth) . ' ';

            if ($date->format('Y') != date('Y'))
            {
                $label .= $date->format('Y');
            }
        }

        $toolTipDateFormat = 'd.m.Y';

        if ($includeTime)
        {
            $label .= $date->format(' H:i');
            $toolTipDateFormat .= ' H:i';
        }

        return
            '<span title="'.$date->format($toolTipDateFormat).'">'.
            trim($label).
            '</span>';
    }

    /**
     * Returns a human-readable month name given the month number. Can optionally
     * return the shorthand version of the month. Translated into the current
     * application locale.
     *
     * @param int|string $monthNr
     * @param boolean $short
     * @return string
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_MONTHTOSTRING_NOT_VALID_MONTH_NUMBER
     */
    public static function month2string($monthNr, bool $short = false) : string
    {
        self::initMonths();

        $monthNr = intval($monthNr);
        if (!isset(self::$months[$monthNr]))
        {
            throw new ConvertHelper_Exception(
                'Invalid month number',
                sprintf('%1$s is not a valid month number.', $monthNr),
                ConvertHelper::ERROR_MONTHTOSTRING_NOT_VALID_MONTH_NUMBER
            );
        }

        if ($short) {
            return self::$months[$monthNr][1];
        }

        return self::$months[$monthNr][0];
    }

    /**
     * Converts a DateTime object to a timestamp, which
     * is PHP 5.2 compatible.
     *
     * @param DateTime $date
     * @return integer
     */
    public static function toTimestamp(DateTime $date) : int
    {
        return (int)$date->format('U');
    }
    /**
     * Converts a timestamp into a DateTime instance.
     *
     * @param int $timestamp
     * @return DateTime
     */
    public static function fromTimestamp(int $timestamp) : DateTime
    {
        $date = new DateTime();
        $date->setTimestamp($timestamp);
        return $date;
    }


    private static function initMonths() : void
    {
        if (!empty(self::$months))
        {
            return;
        }

        self::$months = array(
            1 => array(t('January'), t('Jan')),
            2 => array(t('February'), t('Feb')),
            3 => array(t('March'), t('Mar')),
            4 => array(t('April'), t('Apr')),
            5 => array(t('May'), t('May')),
            6 => array(t('June'), t('Jun')),
            7 => array(t('July'), t('Jul')),
            8 => array(t('August'), t('Aug')),
            9 => array(t('September'), t('Sep')),
            10 => array(t('October'), t('Oct')),
            11 => array(t('November'), t('Nov')),
            12 => array(t('December'), t('Dec'))
        );
    }

    private static function initDays() : void
    {
        if(!empty(self::$daysShort))
        {
            return;
        }

        self::$daysShort = array(
            t('Mon'),
            t('Tue'),
            t('Wed'),
            t('Thu'),
            t('Fri'),
            t('Sat'),
            t('Sun')
        );

        self::$days = array(
            t('Monday'),
            t('Tuesday'),
            t('Wednesday'),
            t('Thursday'),
            t('Friday'),
            t('Saturday'),
            t('Sunday')
        );
    }

    /**
     * Converts a date interval to a human-readable string with
     * all necessary time parts, e.g. "1 year, 2 months and 4 days".
     *
     * @param DateInterval $interval
     * @return string
     * @throws ConvertHelper_Exception
     * @see IntervalConverter
     *
     * @see IntervalConverter::ERROR_MISSING_TRANSLATION
     */
    public static function interval2string(DateInterval $interval) : string
    {
        return (new IntervalConverter())
            ->toString($interval);
    }

    /**
     * Converts an interval to its total number of days.
     * @param DateInterval $interval
     * @return int
     */
    public static function interval2days(DateInterval $interval) : int
    {
        return DateIntervalExtended::toDays($interval);
    }

    /**
     * Converts an interval to its total number of hours.
     * @param DateInterval $interval
     * @return int
     */
    public static function interval2hours(DateInterval $interval) : int
    {
        return DateIntervalExtended::toHours($interval);
    }

    /**
     * Converts an interval to its total number of minutes.
     * @param DateInterval $interval
     * @return int
     */
    public static function interval2minutes(DateInterval $interval) : int
    {
        return DateIntervalExtended::toMinutes($interval);
    }

    /**
     * Converts an interval to its total number of seconds.
     * @param DateInterval $interval
     * @return int
     */
    public static function interval2seconds(DateInterval $interval) : int
    {
        return DateIntervalExtended::toSeconds($interval);
    }

    /**
     * Calculates the total amount of days / hours / minutes or seconds
     * of a date interval object (depending on the specified units), and
     * returns the total amount.
     *
     * @param DateInterval $interval
     * @param string $unit What total value to calculate.
     * @return integer
     *
     * @see DateIntervalExtended::INTERVAL_SECONDS
     * @see DateIntervalExtended::INTERVAL_MINUTES
     * @see DateIntervalExtended::INTERVAL_HOURS
     * @see DateIntervalExtended::INTERVAL_DAYS
     */
    public static function interval2total(DateInterval $interval, string $unit=DateIntervalExtended::INTERVAL_SECONDS) : int
    {
        return DateIntervalExtended::toTotal($interval, $unit);
    }
}
