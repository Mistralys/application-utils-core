<?php
/**
 * @package Application Utils
 * @subpackage DateTime Helper
 */

declare(strict_types=1);

namespace AppUtils\DateTimeHelper;

use AppUtils\Traits\SimpleErrorStateInterface;
use AppUtils\Traits\SimpleErrorStateTrait;
use DateInterval;
use function AppUtils\parseDaytimeString;
use function AppUtils\parseDurationString;
use function AppUtils\t;

/**
 * Given a combination of start time, end time and duration,
 * calculates all possible missing values.
 *
 * ## Possible combinations
 *
 * 1. Start and End time = Duration is calculated.
 * 2. Start and Duration = End time is calculated.
 * 3. End time and Duration = Start time is calculated.
 * 4. Duration = Used as-is, start and end stay empty.
 *
 * ## Usage
 *
 * Use the static method {@see create()} to create an instance,
 * then check for errors via {@see self::isValid()} and access
 * the results via the getter methods if no errors are present.
 *
 * > NOTE: With errors, the duration will always be `0`, and
 * > the start and end times will be `null`.
 *
 * @package Application Utils
 * @subpackage DateTime Helper
 */
class TimeDurationCalculator implements SimpleErrorStateInterface
{
    use SimpleErrorStateTrait;

    public const VALIDATION_INPUT_VALUES_INVALID = 171901;
    public const VALIDATION_MISSING_INFORMATION_FOR_CALCULATION = 171902;
    public const VALIDATION_INVALID_RESULT = 171903;

    private ?DaytimeStringInfo $startTime = null;
    private ?DaytimeStringInfo $endTime = null;
    private DurationStringInfo $duration;

    /**
     * @param string|integer|DaytimeStringInfo|NULL $startTime
     * @param string|integer|DaytimeStringInfo|NULL $endTime
     * @param string|integer|DurationStringInfo|DateInterval|DateIntervalExtended|NULL $duration
     */
    public function __construct($startTime=null, $endTime=null, $duration=null)
    {
        $this->duration = parseDurationString(null);

        $this->parse(
            parseDaytimeString($startTime),
            parseDaytimeString($endTime),
            parseDurationString($duration)
        );
    }

    /**
     * @param string|integer|DaytimeStringInfo|NULL $startTime
     * @param string|integer|DaytimeStringInfo|NULL $endTime
     * @param string|integer|DurationStringInfo|DateInterval|DateIntervalExtended|NULL $duration
     */
    public static function create($startTime=null, $endTime=null, $duration=null) : self
    {
        return new self($startTime, $endTime, $duration);
    }

    private function parse(DaytimeStringInfo $startTime, DaytimeStringInfo $endTime, DurationStringInfo $duration) : void
    {
        if(!$startTime->isValid() || !$endTime->isValid() || !$duration->isValid()) {
            $this->setError(
                t('One or more of the input values are invalid.'),
                self::VALIDATION_INPUT_VALUES_INVALID
            );
            return;
        }

        $startSeconds = $startTime->getTotalSeconds();
        $endSeconds = $endTime->getTotalSeconds();
        $durationSeconds = $duration->getTotalSeconds();

        if($endSeconds > 0 && $startSeconds > $endSeconds) {
            $this->setError(
                t('The start time is later than the end time.'),
                self::VALIDATION_INPUT_VALUES_INVALID
            );
            return;
        }

        // Start and End are provided
        if($startSeconds > 0 && $endSeconds > 0) {
            $this->calculateDuration($startTime, $endTime);
            return;
        }

        // Start and Duration are provided
        if($startSeconds > 0 && $durationSeconds > 0) {
            $this->calculateEndTime($startTime, $duration);
            return;
        }

        // End and Duration are provided
        if($endSeconds > 0 && $durationSeconds > 0) {
            $this->calculateStartTime($endTime, $duration);
            return;
        }

        // Only duration is provided
        if($durationSeconds > 0) {
            $this->duration = $duration;
            return;
        }

        $this->setError(
            t('The provided time and duration information is insufficient to calculate the missing values.'),
            self::VALIDATION_MISSING_INFORMATION_FOR_CALCULATION
        );
    }

    private function calculateDuration(DaytimeStringInfo $startTime, DaytimeStringInfo $endTime) : void
    {
        $duration = DurationStringInfo::fromSeconds($endTime->getTotalSeconds() - $startTime->getTotalSeconds());

        if(!$duration->isValid()) {
            $this->setError(
                t('The calculated duration is invalid.'),
                self::VALIDATION_INVALID_RESULT
            );
            return;
        }

        $this->duration = $duration;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    private function calculateEndTime(DaytimeStringInfo $startTime, DurationStringInfo $duration) : void
    {
        $endTime = DaytimeStringInfo::fromSeconds($startTime->getTotalSeconds() + $duration->getTotalSeconds());

        if(!$endTime->isValid()) {
            $this->setError(
                t('The calculated end time is invalid.'),
                self::VALIDATION_INVALID_RESULT
            );
            return;
        }

        $this->startTime = $startTime;
        $this->duration = $duration;
        $this->endTime = $endTime;
    }

    private function calculateStartTime(DaytimeStringInfo $endTime, DurationStringInfo $duration) : void
    {
        $startTime = DaytimeStringInfo::fromSeconds($endTime->getTotalSeconds() - $duration->getTotalSeconds());

        if(!$startTime->isValid()) {
            $this->setError(
                t('The calculated start time is invalid.'),
                self::VALIDATION_INVALID_RESULT
            );
            return;
        }

        $this->endTime = $endTime;
        $this->duration = $duration;
        $this->startTime = $startTime;
    }

    public function getStartTime() : ?DaytimeStringInfo
    {
        return $this->startTime;
    }

    public function getEndTime() : ?DaytimeStringInfo
    {
        return $this->endTime;
    }

    public function getDuration() : DurationStringInfo
    {
        return $this->duration;
    }
}
