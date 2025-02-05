<?php

declare(strict_types=1);

namespace AppUtilsTests\DateTimeHelper;

use AppUtils\DateTimeHelper\TimeDurationCalculator;
use AppUtilsTestClasses\BaseTestCase;

final class TimeDurationCalculatorTests extends BaseTestCase
{
    public function test_calculateDuration() : void
    {
        $calc = TimeDurationCalculator::create('09:00', '10:25');

        $this->assertTrue($calc->isValid(), (string)$calc->getErrorMessage());
        $this->assertSame('1h 25m', $calc->getDuration()->getNormalized());
    }

    public function test_calculateEndTime() : void
    {
        $calc = TimeDurationCalculator::create('08:00', null, '1h 25m');
        $endTime = $calc->getEndTime();

        $this->assertTrue($calc->isValid(), (string)$calc->getErrorMessage());
        $this->assertNotNull($endTime);
        $this->assertSame('09:25', $endTime->getNormalized());
    }

    public function test_calculateStartTime() : void
    {
        $calc = TimeDurationCalculator::create(null, '13:30', '15m');
        $startTime = $calc->getStartTime();

        $this->assertTrue($calc->isValid(), (string)$calc->getErrorMessage());
        $this->assertNotNull($startTime);
        $this->assertSame('13:15', $startTime->getNormalized());
    }

    public function test_keepDuration() : void
    {
        $calc = TimeDurationCalculator::create(null, null, '33m');

        $this->assertTrue($calc->isValid(), (string)$calc->getErrorMessage());
        $this->assertNull($calc->getStartTime());
        $this->assertNull($calc->getEndTime());
        $this->assertSame('33m', $calc->getDuration()->getNormalized());
    }

    public function test_validation_notEnoughDataToCalculate() : void
    {
        $calc = TimeDurationCalculator::create();

        $this->assertFalse($calc->isValid());
        $this->assertSame(TimeDurationCalculator::VALIDATION_MISSING_INFORMATION_FOR_CALCULATION, $calc->getErrorCode());
    }

    public function test_validation_invalidInputValue() : void
    {
        $calc = TimeDurationCalculator::create('argh', '10:00', '1h 25m');

        $this->assertFalse($calc->isValid());
        $this->assertSame(TimeDurationCalculator::VALIDATION_INPUT_VALUES_INVALID, $calc->getErrorCode());
    }

    public function test_validation_startTimeIsAfterEndTime() : void
    {
        $calc = TimeDurationCalculator::create('12:00', '10:00', '1h 25m');

        $this->assertFalse($calc->isValid());
        $this->assertSame(TimeDurationCalculator::VALIDATION_INPUT_VALUES_INVALID, $calc->getErrorCode());
    }

    public function test_durationIsZeroIfInvalid() : void
    {
        $calc = TimeDurationCalculator::create();

        $this->assertFalse($calc->isValid());
        $this->assertSame(0, $calc->getDuration()->getTotalSeconds());
    }
}
