<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtilsTestClasses\BaseTestCase;
use DateInterval;
use DateTime;
use AppUtils\DateTimeHelper\DurationConverter;

final class DurationConverterTests extends BaseTestCase
{
    public function test_conversion() : void
    {
        $now = new DateTime();

        $tests = array(
            array(
                'duration' => 50,
                'expected' => '50 seconds ago'
            ),
            array(
                'duration' => DurationConverter::SECONDS_PER_HOUR - 1,
                'expected' => '59 minutes ago'
            ),
            array(
                'duration' => DurationConverter::SECONDS_PER_DAY - 1,
                'expected' => '23 hours ago'
            ),
            array(
                'duration' => DurationConverter::SECONDS_PER_WEEK - 1,
                'expected' => '6 days ago'
            ),
            array(
                'duration' => DurationConverter::SECONDS_PER_MONTH_APPROX - 1,
                'expected' => '4 weeks ago'
            ),
            array(
                'duration' => DurationConverter::SECONDS_PER_YEAR - 1,
                'expected' => '11 months ago'
            ),
            array(
                'duration' => DurationConverter::SECONDS_PER_YEAR + 1,
                'expected' => 'One year ago'
            )
        );

        foreach ($tests as $test)
        {
            $converter = new DurationConverter();
            $converter->setDateFrom($now);

            $target = new DateTime();
            $target->add(new DateInterval('PT' . $test['duration'] . 'S'));
            $converter->setDateTo($target);

            $result = $converter->convert();

            $this->assertEquals($test['expected'], $result);
        }
    }
}
