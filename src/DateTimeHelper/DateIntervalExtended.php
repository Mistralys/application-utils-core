<?php
/**
 * @package Application Utils
 * @subpackage DateTimeHelper
 */

declare(strict_types=1);

namespace AppUtils\DateTimeHelper;

use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use DateInterval;
use DateTime;
use Exception;

/**
 * DateInterval wrapper, that makes it much easier to
 * work with intervals. The methods are typed, so no
 * conversions are necessary. A number of utility methods
 * also help.
 * 
 * Automatically fixes the issue of date interval properties
 * not being populated entirely when it is created using
 * a format string.
 * 
 * @see \AppUtils\parseInterval()
 * @subpackage DateTimeHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @package Application Utils
 */
class DateIntervalExtended
{
    public const ERROR_CANNOT_GET_DATE_DIFF = 43601;

    public const INTERVAL_DAYS = 'days';
    public const INTERVAL_HOURS = 'hours';
    public const INTERVAL_MINUTES = 'minutes';
    public const INTERVAL_SECONDS = 'seconds';

    public const TOKEN_SECONDS = 's';
    public const TOKEN_MINUTES = 'i';
    public const TOKEN_HOURS = 'h';
    public const TOKEN_DAYS = 'd';
    public const TOKEN_MONTHS = 'm';
    public const TOKEN_YEARS = 'y';
    
    protected DateInterval $interval;
    protected int $seconds;

    /**
     * @param int $seconds
     *
     * @throws ConvertHelper_Exception
     * @throws Exception
     * @see DateIntervalExtended::ERROR_CANNOT_GET_DATE_DIFF
     */
    protected function __construct(int $seconds)
    {
        $this->seconds = $seconds;
        
        $d1 = new DateTime();
        $d2 = new DateTime();
        $d2->add(new DateInterval('PT'.$this->seconds.'S'));
        
        $interval = $d2->diff($d1);
        
        if(!$interval instanceof DateInterval)
        {
            throw new ConvertHelper_Exception(
                'Cannot create interval',
                sprintf('Getting the date diff failed to retrieve the interval for [%s] seconds.', $this->seconds),
                self::ERROR_CANNOT_GET_DATE_DIFF
            );
        }
        
        $this->interval = $interval;
    }

    /**
     * Creates the interval from a specific amount of seconds.
     *
     * @param int $seconds
     * @return DateIntervalExtended
     * @throws ConvertHelper_Exception
     */
    public static function fromSeconds(int $seconds)
    {
        return new DateIntervalExtended($seconds);
    }
    
   /**
    * Creates the interval from an existing regular interval instance.
    * 
    * @param DateInterval $interval
    * @return DateIntervalExtended
    */
    public static function fromInterval(DateInterval $interval)
    {
        return self::fromSeconds(ConvertHelper::interval2seconds($interval));
    }
    
   /**
    * Retrieves the PHP native date interval.
    * 
    * @return DateInterval
    */
    public function getInterval() : DateInterval
    {
        return $this->interval;
    }
    
    public function getSeconds() : int
    {
        return $this->getToken(self::TOKEN_SECONDS);
    }
    
    public function getHours() : int
    {
        return $this->getToken(self::TOKEN_HOURS);
    }
    
    public function getMinutes() : int
    {
        return $this->getToken(self::TOKEN_MINUTES);
    }
    
    public function getDays() : int
    {
        return $this->getToken(self::TOKEN_DAYS);
    }
    
    public function getMonths() : int
    {
        return $this->getToken(self::TOKEN_MONTHS);
    }
    
    public function getYears() : int
    {
        return $this->getToken(self::TOKEN_YEARS);
    }
    
   /**
    * Retrieves a specific time token, e.g. "h" (for hours).
    * Using the constants to specifiy the tokens is recommended.
    * 
    * @param string $token
    * @return int
    * 
    * @see DateIntervalExtended::TOKEN_SECONDS
    * @see DateIntervalExtended::TOKEN_MINUTES
    * @see DateIntervalExtended::TOKEN_HOURS
    * @see DateIntervalExtended::TOKEN_DAYS
    * @see DateIntervalExtended::TOKEN_MONTHS
    * @see DateIntervalExtended::TOKEN_YEARS
    */
    public function getToken(string $token) : int
    {
        return (int)$this->interval->$token;
    }
    
   /**
    * The total amount of seconds in the interval (including
    * everything, from seconds to days, months, years...).
    * 
    * @return int
    */
    public function getTotalSeconds() : int
    {
        return $this->seconds;
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
     * @see ConvertHelper::INTERVAL_SECONDS
     * @see ConvertHelper::INTERVAL_MINUTES
     * @see ConvertHelper::INTERVAL_HOURS
     * @see ConvertHelper::INTERVAL_DAYS
     */
    public static function toTotal(DateInterval $interval, string $unit=ConvertHelper::INTERVAL_SECONDS) : int
    {
        $total = (int)$interval->format('%a');
        if ($unit == ConvertHelper::INTERVAL_DAYS) {
            return $total;
        }

        $total = ($total * 24) + ((int)$interval->h );
        if ($unit == ConvertHelper::INTERVAL_HOURS) {
            return $total;
        }

        $total = ($total * 60) + ((int)$interval->i );
        if ($unit == ConvertHelper::INTERVAL_MINUTES) {
            return $total;
        }

        $total = ($total * 60) + ((int)$interval->s );
        if ($unit == ConvertHelper::INTERVAL_SECONDS) {
            return $total;
        }

        return 0;
    }

    /**
     * Converts an interval to its total amount of days.
     * @param DateInterval $interval
     * @return int
     */
    public static function toDays(DateInterval $interval) : int
    {
        return self::toTotal($interval, ConvertHelper::INTERVAL_DAYS);
    }

    /**
     * Converts an interval to its total amount of hours.
     * @param DateInterval $interval
     * @return int
     */
    public static function toHours(DateInterval $interval) : int
    {
        return self::toTotal($interval, ConvertHelper::INTERVAL_HOURS);
    }

    /**
     * Converts an interval to its total amount of minutes.
     * @param DateInterval $interval
     * @return int
     */
    public static function toMinutes(DateInterval $interval) : int
    {
        return self::toTotal($interval, ConvertHelper::INTERVAL_MINUTES);
    }

    /**
     * Converts an interval to its total amount of seconds.
     * @param DateInterval $interval
     * @return int
     */
    public static function toSeconds(DateInterval $interval) : int
    {
        return self::toTotal($interval, ConvertHelper::INTERVAL_SECONDS);
    }
}
