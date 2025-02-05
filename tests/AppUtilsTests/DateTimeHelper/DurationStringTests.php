<?php

declare(strict_types=1);

namespace AppUtilsTests\DateTimeHelper;

use AppUtils\DateTimeHelper\DateIntervalExtended;
use AppUtilsTestClasses\BaseTestCase;
use function AppUtils\parseDurationString;

/**
 * @covers \AppUtils\DateTimeHelper\DurationStringInfo
 */
final class DurationStringTests extends BaseTestCase
{
    public function test_parse() : void
    {
        $info = parseDurationString('1d 2h 3m 4s');

        $this->assertSame(1, $info->getDays());
        $this->assertSame(2, $info->getHours());
        $this->assertSame(3, $info->getMinutes());
        $this->assertSame(4, $info->getSeconds());
    }

    public function test_multipleValuesGetAddedTogether() : void
    {
        $info = parseDurationString('3m 12m 5m');

        $this->assertSame(20, $info->getMinutes());
    }

    public function test_getTotalSeconds() : void
    {
        $info = parseDurationString('1h 20m 14s');

        $this->assertSame(
            (
                (1*60*60) +
                (20*60) +
                14
            ),
            $info->getTotalSeconds()
        );
    }

    public function test_convertBackToString() : void
    {
        $info = parseDurationString('1d 2h 3m 5h 2d');

        $this->assertSame('3d 7h 3m', $info->getNormalized());
    }

    public function test_emptyString() : void
    {
        $info = parseDurationString('');

        $this->assertSame('', $info->getNormalized());
        $this->assertSame(0, $info->getTotalSeconds());
    }

    public function test_mixedLabels() : void
    {
        $info = parseDurationString('1d 2h 3m 4s 5day 6hours 7minute 8second');

        $this->assertSame('6d 8h 10m 12s', $info->getNormalized());
    }

    public function test_freeSpacing() : void
    {
        $info = parseDurationString('1 d    2    hours    1  s8m');

        $this->assertSame('1d 2h 8m 1s', $info->getNormalized());
    }

    public function test_invalidStuff() : void
    {
        $info = parseDurationString('1d 2h 3m 4s 5x 6y 7z');

        $this->assertFalse($info->isValid());
        $this->assertSame('5x 6y 7z', $info->getInvalidText());
    }

    public function test_createInterval() : void
    {
        $this->assertSame(
            (45*60) + 14,
            DateIntervalExtended::fromDurationString('45m 14s')->getTotalSeconds()
        );
    }
}
