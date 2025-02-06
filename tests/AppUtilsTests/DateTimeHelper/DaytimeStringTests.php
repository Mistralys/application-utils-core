<?php

declare(strict_types=1);

namespace AppUtilsTests\DateTimeHelper;

use AppUtils\DateTimeHelper\DaytimeStringInfo;
use AppUtilsTestClasses\BaseTestCase;
use function AppUtils\parseDaytimeString;

/**
 * @covers \AppUtils\DateTimeHelper\DaytimeStringInfo
 */
final class DaytimeStringTests extends BaseTestCase
{
    // region: _Tests

    public function test_invalidFormats() : void
    {
        $this->assertHasErrorCode(
            DaytimeStringInfo::VALIDATION_UNRECOGNIZED_TIME_FORMAT,
            parseDaytimeString('12:34:56')
        );

        $this->assertHasErrorCode(
            DaytimeStringInfo::VALIDATION_UNRECOGNIZED_TIME_FORMAT,
            parseDaytimeString('something')
        );
    }

    public function test_valuesOutOfBounds() : void
    {
        $this->assertHasErrorCode(
            DaytimeStringInfo::VALIDATION_INVALID_HOUR,
            parseDaytimeString('32:00')
        );

        $this->assertHasErrorCode(
            DaytimeStringInfo::VALIDATION_INVALID_MINUTE,
            parseDaytimeString('05:74')
        );
    }

    public function test_invalidValuesHaveDefaultTime() : void
    {
        $this->assertSame('00:00', parseDaytimeString('something')->getNormalized());
    }

    public function test_emptyIsDifferentFromMidnight() : void
    {
        $empty = parseDaytimeString(null);
        $midnight = parseDaytimeString('00:00');

        $this->assertSame('00:00', $midnight->getNormalized());
        $this->assertSame('00:00', $empty->getNormalized());

        $this->assertTrue($empty->isEmpty());
        $this->assertFalse($midnight->isEmpty());
    }

    public function test_toReadableWithEmptyValue() : void
    {
        $empty = parseDaytimeString(null);
        $midnight = parseDaytimeString('00:00');

        $this->assertSame('--:--', $empty->toReadable());
        $this->assertSame('00:00', $midnight->toReadable());
    }

    public function test_emptyValueIsValid() : void
    {
        $this->assertIsValid(parseDaytimeString(''));
        $this->assertIsValid(parseDaytimeString('       '));
        $this->assertIsValid(parseDaytimeString(null));
    }

    public function test_createFromValidString() : void
    {
        $info = parseDaytimeString('12:34');

        $this->assertTrue($info->isValid());
        $this->assertSame(12, $info->getHours());
        $this->assertSame(34, $info->getMinutes());
    }

    public function test_freeSpacing() : void
    {
        $info = parseDaytimeString('  12  :  34  ');

        $this->assertTrue($info->isValid());
        $this->assertSame(12, $info->getHours());
        $this->assertSame(34, $info->getMinutes());
    }

    public function test_nonZeroPaddedValues() : void
    {
        $info = parseDaytimeString('2:5');

        $this->assertSame(2, $info->getHours());
        $this->assertSame(5, $info->getMinutes());
    }

    public function test_roundMinutesDown() : void
    {
        $info = parseDaytimeString('12:34');

        $rounded = $info->roundTo(15);

        $this->assertSame(12, $rounded->getHours());
        $this->assertSame(30, $rounded->getMinutes());
    }

    public function test_roundMinutesUp() : void
    {
        $info = parseDaytimeString('12:44');

        $rounded = $info->roundTo(15);

        $this->assertSame(12, $rounded->getHours());
        $this->assertSame(45, $rounded->getMinutes());
    }

    public function test_roundMinutesHourUp() : void
    {
        $info = parseDaytimeString('12:59');

        $rounded = $info->roundToQuarterHour();

        $this->assertSame(13, $rounded->getHours());
        $this->assertSame(0, $rounded->getMinutes());
    }

    public function test_roundMinutesMidnightUp() : void
    {
        $info = parseDaytimeString('23:59');

        $rounded = $info->roundToQuarterHour();

        $this->assertSame(0, $rounded->getHours());
        $this->assertSame(0, $rounded->getMinutes());
    }

    public function test_roundToUnevenValue() : void
    {
        $info = parseDaytimeString('12:34');

        $rounded = $info->roundTo(7);

        $this->assertSame(12, $rounded->getHours());
        $this->assertSame(35, $rounded->getMinutes());
    }

    public function test_restoreFromTotalSeconds() : void
    {
        $info = DaytimeStringInfo::fromString('16:42');

        $this->assertSame(
            $info->getTotalSeconds(),
            DaytimeStringInfo::fromSeconds($info->getTotalSeconds())->getTotalSeconds()
        );
    }

    public function test_isMethods() : void
    {
        $this->assertTrue(parseDaytimeString('05:00')->isMorning());
        $this->assertTrue(parseDaytimeString('11:59')->isMorning());

        $this->assertTrue(parseDaytimeString('12:00')->isNoon());
        $this->assertTrue(parseDaytimeString('12:59')->isNoon());

        $this->assertTrue(parseDaytimeString('13:00')->isAfternoon());
        $this->assertTrue(parseDaytimeString('16:59')->isAfternoon());

        $this->assertTrue(parseDaytimeString('17:00')->isEvening());
        $this->assertTrue(parseDaytimeString('20:59')->isEvening());

        $this->assertTrue(parseDaytimeString('21:00')->isNight());
        $this->assertTrue(parseDaytimeString('05:00')->isNight());
    }

    public function test_isAfter() : void
    {
        $this->assertTrue(parseDaytimeString('12:00')->isAfter('11:59'));
        $this->assertTrue(parseDaytimeString('00:01')->isAfter('00:00'));
    }

    public function test_isBefore() : void
    {
        $this->assertTrue(parseDaytimeString('11:59')->isBefore('12:00'));
        $this->assertTrue(parseDaytimeString('00:00')->isBefore('00:01'));
    }

    public function test_getTimeDifference() : void
    {
        $this->assertSame((20*60), parseDaytimeString('12:00')->getTimeDifference('12:20'));
        $this->assertSame(-(20*60), parseDaytimeString('12:00')->getTimeDifference('11:40'));
    }

    // endregion

    // region: Support methods

    public function assertIsValid(DaytimeStringInfo $info) : void
    {
        $this->assertTrue($info->isValid());
    }

    public function assertHasErrorCode(int $code, DaytimeStringInfo $info) : void
    {
        $this->assertFalse($info->isValid());
        $this->assertSame($code, $info->getErrorCode());
    }

    protected function setUp(): void
    {
        parent::setUp();

        DaytimeStringInfo::resetEmptyTimeText();
    }

    // endregion
}
