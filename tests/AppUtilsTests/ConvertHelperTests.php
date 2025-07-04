<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtils\StringHelper;
use AppUtils\VariableInfo;
use AppUtilsTestClasses\BaseTestCase;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use AppUtils\ConvertHelper_SizeNotation;
use AppUtils\ConvertHelper_StorageSizeEnum;
use DateInterval;
use ForceUTF8\Encoding;
use stdClass;

final class ConvertHelperTests extends BaseTestCase
{
    protected string $assetsFolder;

    protected function setUp(): void
    {
        parent::setUp();

        if (isset($this->assetsFolder)) {
            return;
        }

        $this->assetsFolder = $this->assetsRootFolder . '/ConvertHelper';
    }

    /**
     * @see ConvertHelper::areVariablesEqual()
     */
    public function test_areVariablesEqual(): void
    {
        $tests = array(
            array('0', 0, true, 'String zero, numeric zero'),
            array('0', null, false, 'String zero, NULL'),
            array(null, 0, false, 'NULL, numeric zero'),
            array(false, null, false, 'FALSE, NULL'),
            array(false, '', false, 'FALSE, empty string'),
            array('1', 1, true, 'String 1, numeric 1'),
            array('112.58', 112.58, true, 'String float, numeric float'),
            array('', '', true, 'Empty string, empty string'),
            array('', null, true, 'Empty string, NULL'),
            array(null, null, true, 'NULL, NULL'),
            array('string', 'other', false, 'String, different string'),
            array('string', 'string', true, 'String, same string'),
            array(array('yo'), array('yo'), true, 'Array, same array'),
            array(array('yo'), array('no'), false, 'Array, different array'),
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::areVariablesEqual($test[0], $test[1]);

            $this->assertEquals($test[2], $result);
        }
    }

    /**
     * @see ConvertHelper::filenameRemoveExtension()
     */
    public function test_filenameRemoveExtension(): void
    {
        $tests = array(
            'somename.ext' => 'somename',
            '/path/to/file.txt' => 'file',
            'F:\\path\name.extension' => 'name',
            'With.Several.Dots.file' => 'With.Several.Dots',
            '.ext' => ''
        );

        foreach ($tests as $string => $expected) {
            $actual = ConvertHelper::filenameRemoveExtension($string);

            $this->assertEquals($expected, $actual);
        }
    }

    /**
     * @see ConvertHelper::isStringHTML()
     */
    public function test_isStringHTML(): void
    {
        $tests = array(
            'Text without HTML' => false,
            'Text with < signs >' => false,
            'Text with <b>Some</b> HTML' => true,
            'Just a <br> single tag' => true,
            'Auto-closing <div/> here' => true,
            '' => false,
            '    ' => false,
            '>>>>' => false,
            '<!-- -->' => false,
            'Simple & ampersand' => false,
            'Encoded &amp; ampersand' => true
        );

        foreach ($tests as $string => $expected) {
            $actual = ConvertHelper::isStringHTML($string);

            $this->assertEquals($expected, $actual);
        }
    }

    public function test_toString() : void
    {
        $tests = array(
            array(
                'label' => 'Zero',
                'value' => 0,
                'expected' => '0'
            ),
            array(
                'label' => 'String zero',
                'value' => '0',
                'expected' => '0'
            ),
            array(
                'label' => 'NULL',
                'value' => null,
                'expected' => ''
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => ''
            ),
            array(
                'label' => 'String with text',
                'value' => 'Hello World',
                'expected' => 'Hello World'
            ),
            array(
                'label' => 'Array',
                'value' => array('foo', 'bar'),
                'expected' => ''
            ),
            array(
                'label' => 'Object',
                'value' => new stdClass(),
                'expected' => ''
            ),
            array(
                'label' => 'Boolean',
                'value' => true,
                'expected' => 'true'
            ),
            array(
                'label' => 'Float value',
                'value' => 3.14,
                'expected' => '3.14'
            )
        );

        foreach ($tests as $test) {
            $this->assertSame(
                $test['expected'],
                ConvertHelper::toString($test['value']),
                $test['label']
            );
        }
    }

    public function test_toStringN() : void
    {
        $tests = array(
            array(
                'label' => 'Zero',
                'value' => 0,
                'expected' => '0'
            ),
            array(
                'label' => 'String zero',
                'value' => '0',
                'expected' => '0'
            ),
            array(
                'label' => 'NULL',
                'value' => null,
                'expected' => null
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => null
            ),
            array(
                'label' => 'String with text',
                'value' => 'Hello World',
                'expected' => 'Hello World'
            ),
            array(
                'label' => 'Array',
                'value' => array('foo', 'bar'),
                'expected' => null
            ),
            array(
                'label' => 'Object',
                'value' => new stdClass(),
                'expected' => null
            ),
            array(
                'label' => 'Boolean',
                'value' => true,
                'expected' => 'true'
            ),
            array(
                'label' => 'Float value',
                'value' => 3.14,
                'expected' => '3.14'
            )
        );

        foreach ($tests as $test) {
            $this->assertSame(
                $test['expected'],
                ConvertHelper::toStringN($test['value']),
                $test['label']
            );
        }
    }

    public function test_bool2string(): void
    {
        $tests = array(
            true => 'true',
            false => 'false',
            'true' => 'true',
            'false' => 'false',
            'yes' => 'true',
            'no' => 'false',
        );

        foreach ($tests as $bool => $expected) {
            $actual = ConvertHelper::bool2string($bool);

            $this->assertEquals($expected, $actual);
        }
    }

    public function test_boolStrict2string(): void
    {
        $this->assertSame('true', ConvertHelper::boolStrict2string(true));
        $this->assertSame('false', ConvertHelper::boolStrict2string(false));
        $this->assertSame('yes', ConvertHelper::boolStrict2string(true, true));
        $this->assertSame('no', ConvertHelper::boolStrict2string(false, true));
    }

    public function test_string2bool(): void
    {
        $tests = array(
            array(
                'value' => 0,
                'expected' => false
            ),
            array(
                'value' => 1,
                'expected' => true
            ),
            array(
                'value' => '0',
                'expected' => false
            ),
            array(
                'value' => '1',
                'expected' => true
            ),
            array(
                'value' => false,
                'expected' => false
            ),
            array(
                'value' => true,
                'expected' => true
            ),
            array(
                'value' => 'false',
                'expected' => false
            ),
            array(
                'value' => 'true',
                'expected' => true
            ),
            array(
                'value' => 'no',
                'expected' => false
            ),
            array(
                'value' => 'yes',
                'expected' => true
            ),
            array(
                'value' => null,
                'expected' => false
            ),
            array(
                'value' => array(),
                'expected' => false
            ),
            array(
                'value' => new stdClass(),
                'expected' => false
            ),
            array(
                'value' => 'TRUE',
                'expected' => true
            ),
            array(
                'value' => 'FALSE',
                'expected' => false
            ),
            array(
                'value' => 'TruE',
                'expected' => true
            ),
            array(
                'value' => 'FalSe',
                'expected' => false
            )
        );

        foreach ($tests as $test) {
            $actual = ConvertHelper::string2bool($test['value']);

            $this->assertSame($test['expected'], $actual);
        }
    }

    public function test_isStringASCII(): void
    {
        $tests = array(
            array('regular text', true, 'Regular text'),
            array('()?%$"46[]{}!+*', true, 'ASCII Characters'),
            array('A single ö', false, 'Special character'),
            array('', true, 'Empty string'),
            array(null, true, 'NULL'),
            array(42, true, 'Integer'),
            array(0.7, true, 'Float')
        );

        foreach ($tests as $def) {
            $actual = ConvertHelper::isStringASCII($def[0]);

            $this->assertEquals($def[1], $actual, $def[2]);
        }
    }

    public function test_isBooleanString(): void
    {
        $tests = array(
            array(
                'value' => 1,
                'expected' => true
            ),
            array(
                'value' => 0,
                'expected' => true
            ),
            array(
                'value' => '1',
                'expected' => true
            ),
            array(
                'value' => '0',
                'expected' => true
            ),
            array(
                'value' => 'true',
                'expected' => true
            ),
            array(
                'value' => 'false',
                'expected' => true
            ),
            array(
                'value' => 'yes',
                'expected' => true
            ),
            array(
                'value' => 'no',
                'expected' => true
            ),
            array(
                'value' => '',
                'expected' => false
            ),
            array(
                'value' => null,
                'expected' => false
            ),
            array(
                'value' => 'bla',
                'expected' => false
            )
        );

        foreach ($tests as $def) {
            $this->assertEquals(ConvertHelper::isBoolean($def['value']), $def['expected']);
        }
    }

    public function test_string2array(): void
    {
        $tests = array(
            array(
                'string' => '',
                'result' => array()
            ),
            array(
                'string' => 'Hello',
                'result' => array('H', 'e', 'l', 'l', 'o')
            ),
            array(
                'string' => 'äöü',
                'result' => array('ä', 'ö', 'ü')
            ),
            array(
                'string' => "And spa\ns",
                'result' => array('A', 'n', 'd', ' ', 's', 'p', 'a', "\n", 's')
            ),
        );

        foreach ($tests as $def) {
            $this->assertEquals($def['result'], ConvertHelper::string2array($def['string']));
        }
    }

    public function test_text_cut(): void
    {
        $tests = array(
            array(
                'string' => 'Here is some text to test cutting on.',
                'result' => 'Here is some tex...',
                'length' => 16,
                'char' => '...'
            ),
            array(
                'string' => 'Here is some text to test cutting on.',
                'result' => 'Here is some tex [...]',
                'length' => 16,
                'char' => ' [...]'
            ),
        );

        foreach ($tests as $def) {
            $this->assertEquals(
                $def['result'],
                ConvertHelper::text_cut($def['string'], $def['length'], $def['char'])
            );
        }
    }

    public function test_time2string(): void
    {
        $tests = array(
            array(
                'time' => -10,
                'expected' => '0 seconds'
            ),
            array(
                'time' => 0,
                'expected' => '0 seconds'
            ),
            array(
                'time' => 0.5,
                'expected' => 'less than a second'
            ),
            array(
                'time' => 20,
                'expected' => '20 seconds'
            )
        );

        foreach ($tests as $def) {
            $this->assertEquals($def['expected'], ConvertHelper::time2string($def['time']));
        }
    }

    public function test_isBool(): void
    {
        $tests = array(
            array(
                'label' => 'NULL value',
                'value' => null,
                'expected' => false
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => false
            ),
            array(
                'label' => 'String zero',
                'value' => '0',
                'expected' => true
            ),
            array(
                'label' => 'Numeric zero',
                'value' => 0,
                'expected' => true
            ),
            array(
                'label' => 'String one',
                'value' => '1',
                'expected' => true
            ),
            array(
                'label' => 'Numeric one',
                'value' => 1,
                'expected' => true
            ),
            array(
                'label' => 'String true',
                'value' => 'true',
                'expected' => true
            ),
            array(
                'label' => 'String yes',
                'value' => 'yes',
                'expected' => true
            ),
            array(
                'label' => 'String false',
                'value' => 'false',
                'expected' => true
            ),
            array(
                'label' => 'String true',
                'value' => 'true',
                'expected' => true
            ),
            array(
                'label' => 'Boolean true',
                'value' => true,
                'expected' => true
            ),
            array(
                'label' => 'Boolean false',
                'value' => false,
                'expected' => true
            )
        );

        foreach ($tests as $def) {
            $isBool = ConvertHelper::isBoolean($def['value']);

            $this->assertEquals($def['expected'], $isBool, $def['label']);
        }
    }

    public function test_parseQueryString(): void
    {
        $tests = array(
            array(
                'label' => 'Empty string value',
                'value' => '',
                'expected' => array()
            ),
            array(
                'label' => 'Whitespace value',
                'value' => '  ',
                'expected' => array()
            ),
            array(
                'label' => 'Single parameter',
                'value' => 'foo=bar',
                'expected' => array('foo' => 'bar')
            ),
            array(
                'label' => 'Multiple parameters',
                'value' => 'foo=bar&bar=foo&something=more',
                'expected' => array(
                    'foo' => 'bar',
                    'bar' => 'foo',
                    'something' => 'more'
                )
            ),
            array(
                'label' => 'Parameters with HTML encoded ampersand',
                'value' => 'foo=bar&amp;bar=foo',
                'expected' => array(
                    'foo' => 'bar',
                    'bar' => 'foo'
                )
            ),
            array(
                'label' => 'Parameter name with dot',
                'value' => 'foo.bar=result',
                'expected' => array(
                    'foo.bar' => 'result'
                )
            ),
            array(
                'label' => 'Parameter name with space',
                'value' => 'foo bar=result',
                'expected' => array(
                    'foo bar' => 'result'
                )
            ),
            array(
                'label' => 'Parameter name with space and dot',
                'value' => 'f.oo bar=result',
                'expected' => array(
                    'f.oo bar' => 'result'
                )
            ),
            array(
                // with parse_str, this would not be possible since foo.bar would be converted to foo_bar.
                'label' => 'Mixed underscores and dots (conflict test)',
                'value' => 'foo.bar=result1&foo_bar=result2',
                'expected' => array(
                    'foo.bar' => 'result1',
                    'foo_bar' => 'result2'
                )
            ),
            array(
                // with parse_str, this would not be possible since foo.bar would be converted to foo_bar.
                'label' => 'Mixed underscores and spaces (conflict test)',
                'value' => 'foo bar=result1&foo_bar=result2',
                'expected' => array(
                    'foo bar' => 'result1',
                    'foo_bar' => 'result2'
                )
            ),
            array(
                // check that the replacement mechanism does not confuse parameter names
                'label' => 'Parameter names starting like other parameter names',
                'value' => 'foo=bar&foo.bar=ditto',
                'expected' => array(
                    'foo' => 'bar',
                    'foo.bar' => 'ditto'
                )
            ),
            array(
                'label' => 'Parameter name with colon',
                'value' => 'foo:bar=result',
                'expected' => array(
                    'foo:bar' => 'result'
                )
            ),
            array(
                'label' => 'Parameter name surrounded by spaces',
                'value' => '  foo  =result',
                'expected' => array(
                    '  foo  ' => 'result'
                )
            ),
            array(
                'label' => 'Parameter name surrounded by pre-encoded spaces',
                'value' => '%20%20foo%20%20=result',
                'expected' => array(
                    '  foo  ' => 'result'
                )
            ),
            array(
                'label' => 'Parameter name URL encoded should not conflict',
                'value' => 'foobar=' . urlencode('&foo=bar'),
                'expected' => array(
                    'foobar' => '&foo=bar'
                )
            ),
            array(
                'label' => 'Direct from parse_url',
                'value' => parse_url('https://domain.com?foo=bar', PHP_URL_QUERY),
                'expected' => array(
                    'foo' => 'bar'
                )
            )
        );

        foreach ($tests as $def) {
            $result = ConvertHelper::parseQueryString($def['value']);

            $this->assertEquals($def['expected'], $result, $def['label']);
        }
    }

    public function test_findString(): void
    {
        $tests = array(
            array(
                'label' => 'Empty needle',
                'haystack' => 'We were walking, and a foo appeared just like that.',
                'needle' => '',
                'caseInsensitive' => false,
                'expected' => array()
            ),
            array(
                'label' => 'No matches present',
                'haystack' => '',
                'needle' => 'foo',
                'caseInsensitive' => false,
                'expected' => array()
            ),
            array(
                'label' => 'One match present',
                'haystack' => 'We were walking, and a foo appeared just like that.',
                'needle' => 'foo',
                'caseInsensitive' => false,
                'expected' => array(
                    array(
                        'pos' => 23,
                        'match' => 'foo'
                    )
                )
            ),
            array(
                'label' => 'One match present, different case',
                'haystack' => 'We were walking, and a Foo appeared just like that.',
                'needle' => 'foo',
                'caseInsensitive' => false,
                'expected' => array()
            ),
            array(
                'label' => 'One match present, different case, case insensitive',
                'haystack' => 'We were walking, and a Foo appeared just like that.',
                'needle' => 'foo',
                'caseInsensitive' => true,
                'expected' => array(
                    array(
                        'pos' => 23,
                        'match' => 'Foo'
                    )
                )
            ),
            array(
                'label' => 'Several matches',
                'haystack' => 'We were walking, and a foo with another foo ran by, whith a foo trailing behind.',
                'needle' => 'foo',
                'caseInsensitive' => false,
                'expected' => array(
                    array(
                        'pos' => 23,
                        'match' => 'foo'
                    ),
                    array(
                        'pos' => 40,
                        'match' => 'foo'
                    ),
                    array(
                        'pos' => 60,
                        'match' => 'foo'
                    )
                )
            ),
            array(
                'label' => 'Several matches, different cases',
                'haystack' => 'We were walking, and a foo with another Foo ran by, whith a fOo trailing behind.',
                'needle' => 'foo',
                'caseInsensitive' => false,
                'expected' => array(
                    array(
                        'pos' => 23,
                        'match' => 'foo'
                    )
                )
            ),
            array(
                'label' => 'Several matches, different cases, case insensitive',
                'haystack' => 'We were walking, and a foo with another Foo ran by, whith a fOo trailing behind.',
                'needle' => 'foo',
                'caseInsensitive' => true,
                'expected' => array(
                    array(
                        'pos' => 23,
                        'match' => 'foo'
                    ),
                    array(
                        'pos' => 40,
                        'match' => 'Foo'
                    ),
                    array(
                        'pos' => 60,
                        'match' => 'fOo'
                    )
                )
            ),
            array(
                'label' => 'One match using unicode characters',
                'haystack' => 'And a föö.',
                'needle' => 'föö',
                'caseInsensitive' => false,
                'expected' => array(
                    array(
                        'pos' => 6,
                        'match' => 'föö'
                    )
                )
            ),
            array(
                'label' => 'One match with a newline',
                'haystack' => 'And a\n foo.',
                'needle' => 'foo',
                'caseInsensitive' => false,
                'expected' => array(
                    array(
                        'pos' => 8,
                        'match' => 'foo'
                    )
                )
            )
        );

        foreach ($tests as $test) {
            $this->findString_checkTest($test);
        }
    }

    /**
     * @param array{label:string,haystack:string,needle:string,caseInsensitive:bool,expected:array<int,array{pos:int,match:string}>} $test
     * @return void
     */
    public function findString_checkTest(array $test): void
    {
        $matches = ConvertHelper::findString($test['needle'], $test['haystack'], $test['caseInsensitive']);

        $this->assertCount(count($test['expected']), $matches, 'Amount of matches should match.');

        foreach ($matches as $idx => $match) {
            $testMatch = $test['expected'][$idx];

            $this->assertEquals($testMatch['pos'], $match->getPosition(), 'The position of needle should match.');
            $this->assertEquals($testMatch['match'], $match->getMatchedString(), 'The matched string should match.');
        }
    }

    public function test_explodeTrim(): void
    {
        $tests = array(
            array(
                'label' => 'Empty string value',
                'delimiter' => ',',
                'string' => '',
                'expected' => array()
            ),
            array(
                'label' => 'Empty delimiter',
                'delimiter' => '',
                'string' => 'Some text here',
                'expected' => array()
            ),
            array(
                'label' => 'Comma delimiter, no spaces',
                'delimiter' => ',',
                'string' => 'foo,bar',
                'expected' => array(
                    'foo',
                    'bar'
                )
            ),
            array(
                'label' => 'Comma delimiter, with spaces',
                'delimiter' => ',',
                'string' => '  foo  ,  bar   ',
                'expected' => array(
                    'foo',
                    'bar'
                )
            ),
            array(
                'label' => 'Comma delimiter, with newlines',
                'delimiter' => ',',
                'string' => "  foo  \n,\n  bar\n   ",
                'expected' => array(
                    'foo',
                    'bar'
                )
            ),
            array(
                'label' => 'Comma delimiter, empty entries',
                'delimiter' => ',',
                'string' => ',foo,,bar,',
                'expected' => array(
                    'foo',
                    'bar'
                )
            )
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::explodeTrim($test['delimiter'], $test['string']);

            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }

    public function test_date2timestamp(): void
    {
        $timestamp = mktime(10, 15, 0, 2, 2, 2006);
        $date = ConvertHelper::timestamp2date($timestamp);

        $back = ConvertHelper::date2timestamp($date);

        $this->assertEquals($timestamp, $back);
    }

    public function test_isInteger(): void
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => false
            ),
            array(
                'label' => 'Numeric Zero',
                'value' => 0,
                'expected' => true
            ),
            array(
                'label' => 'String zero',
                'value' => '0',
                'expected' => true
            ),
            array(
                'label' => 'Numeric 1',
                'value' => 1,
                'expected' => true
            ),
            array(
                'label' => 'Numeric -50',
                'value' => -50,
                'expected' => true
            ),
            array(
                'label' => 'String -50',
                'value' => '-50',
                'expected' => true
            ),
            array(
                'label' => 'Boolean true',
                'value' => true,
                'expected' => false
            ),
            array(
                'label' => 'Array',
                'value' => array('foo' => 'bar'),
                'expected' => false
            ),
            array(
                'label' => 'Object',
                'value' => new stdClass(),
                'expected' => false
            ),
            array(
                'label' => 'Integer value 145',
                'value' => 145,
                'expected' => true
            ),
            array(
                'label' => 'Integer value 1000',
                'value' => 1000,
                'expected' => true
            ),
            array(
                'label' => 'String integer',
                'value' => '1458',
                'expected' => true
            ),
            array(
                'label' => 'Decimal value',
                'value' => 10.45,
                'expected' => false
            ),
            array(
                'label' => 'String decimal',
                'value' => '10.89',
                'expected' => false
            )
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::isInteger($test['value']);

            $this->assertSame($test['expected'], $result, $test['label']);
        }
    }

    public function test_seconds2interval(): void
    {
        $tests = array(
            array(
                'label' => '60 seconds = 1 minute',
                'seconds' => 60,
                'expected' => array(
                    'seconds' => 0,
                    'minutes' => 1,
                    'hours' => 0,
                    'days' => 0
                )
            ),
            array(
                'label' => '59 seconds = 59 seconds',
                'seconds' => 59,
                'expected' => array(
                    'seconds' => 59,
                    'minutes' => 0,
                    'hours' => 0,
                    'days' => 0
                )
            ),
            array(
                'label' => '3601 seconds = 1 hour, 1 second',
                'seconds' => 3601,
                'expected' => array(
                    'seconds' => 1,
                    'minutes' => 0,
                    'hours' => 1,
                    'days' => 0
                )
            )
        );

        foreach ($tests as $test) {
            $interval = ConvertHelper::seconds2interval($test['seconds']);

            $this->assertEquals($test['expected']['seconds'], $interval->s, $test['label']);
            $this->assertEquals($test['expected']['minutes'], $interval->i, $test['label']);
            $this->assertEquals($test['expected']['hours'], $interval->h, $test['label']);
            $this->assertEquals($test['expected']['days'], $interval->d, $test['label']);
        }
    }

    public function test_interval2total(): void
    {
        $tests = array(
            array(
                'label' => '100 seconds',
                'value' => ConvertHelper::seconds2interval(100),
                'expected' => 100,
                'units' => ConvertHelper::INTERVAL_SECONDS
            ),
            array(
                'label' => '3600 seconds',
                'value' => ConvertHelper::seconds2interval(3600),
                'expected' => 1,
                'units' => ConvertHelper::INTERVAL_HOURS
            ),
            array(
                'label' => '3 minutes and some seconds',
                'value' => ConvertHelper::seconds2interval(60 * 3 + 15),
                'expected' => 3,
                'units' => ConvertHelper::INTERVAL_MINUTES
            )
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::interval2total($test['value'], $test['units']);

            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }

    public function test_var2json(): void
    {
        $tests = array(
            array(
                'label' => 'Regular array',
                'value' => array('foo'),
                'expected' => '["foo"]'
            ),
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::var2json($test['value']);

            $this->assertEquals($test['expected'], $result);
        }
    }

    public function test_var2json_error(): void
    {
        $this->expectException(ConvertHelper_Exception::class);

        // the paragraph sign cannot be converted to JSON.
        ConvertHelper::var2json(array(utf8_decode('öäöü§')));
    }

    public function test_duration2string(): void
    {
        $time = time();

        $tests = array(
            array(
                'label' => '60 seconds ago',
                'from' => $time - 60,
                'to' => $time,
                'expected' => 'One minute ago'
            ),
            array(
                'label' => 'No to time set',
                'from' => $time - 60,
                'to' => -1,
                'expected' => 'One minute ago'
            ),
            array(
                'label' => 'Future time',
                'from' => $time + 60,
                'to' => $time,
                'expected' => 'In one minute'
            )
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::duration2string($test['from'], $test['to']);

            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }

    public function test_intervalstring(): void
    {
        $tests = array(
            array(
                'label' => '60 seconds',
                'interval' => new DateInterval('PT60S'),
                'expected' => '1 minute'
            ),
            array(
                'label' => '1 hour 25 seconds',
                'interval' => new DateInterval('PT' . (60 * 60 + 25) . 'S'),
                'expected' => '1 hour and 25 seconds'
            ),
            array(
                'label' => '3 days',
                'interval' => new DateInterval('PT' . (60 * 60 * 24 * 3) . 'S'),
                'expected' => '3 days'
            ),
            array(
                'label' => '6 days',
                'interval' => new DateInterval('PT' . (60 * 60 * 24 * 6) . 'S'),
                'expected' => '6 days'
            )
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::interval2string($test['interval']);

            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }

    public function test_size2bytes(): void
    {
        $tests = array(
            array(
                'label' => 'Zero value',
                'value' => '0',
                'expected' => 0
            ),
            array(
                'label' => '1 value',
                'value' => '1',
                'expected' => 1
            ),
            array(
                'label' => 'Empty string',
                'value' => '',
                'expected' => 0
            ),
            array(
                'label' => 'Negative value',
                'value' => '-100',
                'expected' => 0
            ),
            array(
                'label' => 'No units, integer',
                'value' => '500',
                'expected' => 500
            ),
            array(
                'label' => 'No units, float',
                'value' => '500.45',
                'expected' => 500
            ),
            array(
                'label' => 'No units, float, comma notation',
                'value' => '500,45',
                'expected' => 500
            ),
            array(
                'label' => 'Invalid string',
                'value' => 'Some text here',
                'expected' => 0
            ),
            array(
                'label' => 'Byte units, negative',
                'value' => '-500B',
                'expected' => 0
            ),
            array(
                'label' => 'Byte units',
                'value' => '500B',
                'expected' => 500
            ),
            array(
                'label' => 'Byte units, spaces',
                'value' => '   500     B     ',
                'expected' => 500
            ),
            array(
                'label' => 'Kilobytes',
                'value' => '1KB',
                'expected' => 1000
            ),
            array(
                'label' => 'Megabytes',
                'value' => '1MB',
                'expected' => 1000000
            ),
            array(
                'label' => 'Gigabytes',
                'value' => '1GB',
                'expected' => 1000000000
            ),
            array(
                'label' => 'iKilobytes',
                'value' => '1KiB',
                'expected' => 1024
            ),
            array(
                'label' => 'iMegabytes',
                'value' => '1MiB',
                'expected' => 1048576
            ),
            array(
                'label' => 'iGigabytes',
                'value' => '1GiB',
                'expected' => 1073741824
            ),
            array(
                'label' => 'iKilobytes, case insensitive',
                'value' => '1kib',
                'expected' => 1024
            ),
            array(
                'label' => 'Several units',
                'value' => '1 KB GiB',
                'expected' => 0
            )
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::size2bytes($test['value']);

            $this->assertSame($test['expected'], $result, $test['label']);
        }
    }

    public function test_parseSize(): void
    {
        $size = ConvertHelper::parseSize('50MB');

        $this->assertInstanceOf(ConvertHelper_SizeNotation::class, $size);
    }

    public function test_parseSize_errors(): void
    {
        $tests = array(
            array(
                'label' => 'Negative value',
                'value' => '-100',
                'error' => ConvertHelper_SizeNotation::VALIDATION_ERROR_NEGATIVE_VALUE
            ),
            array(
                'label' => 'Invalid string',
                'value' => 'Some text here',
                'error' => ConvertHelper_SizeNotation::VALIDATION_ERROR_UNRECOGNIZED_STRING
            ),
            array(
                'label' => 'Several units',
                'value' => '1 KB GiB',
                'error' => ConvertHelper_SizeNotation::VALIDATION_ERROR_MULTIPLE_UNITS
            )
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::parseSize($test['value']);

            $this->assertFalse($result->isValid(), $test['label']);
            $this->assertSame($test['error'], $result->getErrorCode(), $test['label']);
        }
    }

    public function test_bytes2readable(): void
    {
        $tests = array(
            array(
                'label' => 'Negative value',
                'value' => -100,
                'result' => '0 B'
            ),
            array(
                'label' => 'Max byte value',
                'value' => 999,
                'result' => '999 B'
            ),
            array(
                'label' => 'KB value',
                'value' => 1000,
                'result' => '1 KB'
            ),
            array(
                'label' => 'KB value',
                'value' => 1500,
                'result' => '1.5 KB'
            ),
            array(
                'label' => 'MB value',
                'value' => 1400000,
                'result' => '1.4 MB'
            ),
            array(
                'label' => 'GB value',
                'value' => 1400000000,
                'result' => '1.4 GB'
            ),
            array(
                'label' => 'TB value',
                'value' => 1400000000000,
                'result' => '1.4 TB'
            ),
            array(
                'label' => 'PB value',
                'value' => 1400000000000000,
                'result' => '1.4 PB'
            )
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::bytes2readable($test['value']);

            $this->assertSame($test['result'], $result, $test['label']);
        }
    }

    public function test_bytes2readable_precision(): void
    {
        $tests = array(
            array(
                'label' => 'Rounding up',
                'value' => 1800,
                'result' => '2 KB',
                'precision' => 0
            ),
            array(
                'label' => 'Rounding down',
                'value' => 1400,
                'result' => '1 KB',
                'precision' => 0
            ),
            array(
                'label' => 'Higher precision',
                'value' => 1480,
                'result' => '1.48 KB',
                'precision' => 2
            )
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::bytes2readable($test['value'], $test['precision']);

            $this->assertSame($test['result'], $result, $test['label']);
        }
    }

    public function test_bytes2readable_base2(): void
    {
        $tests = array(
            array(
                'label' => 'Max byte value',
                'value' => 1023,
                'result' => '1023 B',
            ),
            array(
                'label' => '0 value',
                'value' => 0,
                'result' => '0 B',
            ),
            array(
                'label' => '1 value',
                'value' => 1,
                'result' => '1 B',
            ),
            array(
                'label' => 'KiB value',
                'value' => 1024,
                'result' => '1 KiB',
            ),
            array(
                'label' => 'MiB value',
                'value' => 1024 ** 2,
                'result' => '1 MiB',
            ),
            array(
                'label' => 'GiB value',
                'value' => 1024 ** 3,
                'result' => '1 GiB',
            ),
            array(
                'label' => 'TiB value',
                'value' => 1024 ** 4,
                'result' => '1 TiB',
            ),
            array(
                'label' => 'PiB value',
                'value' => 1024 ** 5,
                'result' => '1 PiB',
            )
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::bytes2readable($test['value'], 0, ConvertHelper_StorageSizeEnum::BASE_2);

            $this->assertSame($test['result'], $result, $test['label']);
        }
    }

    public function test_storageSizeEnum_localeSwitching(): void
    {
        if (!class_exists('\AppLocalize\Localization')) {
            $this->markTestSkipped('The localization package is not installed.');
        }

        $size = ConvertHelper_StorageSizeEnum::getSizeByName('mb');

        $this->assertEquals('Megabyte', $size->getLabelSingular());

        \AppLocalize\Localization::addAppLocale('fr_FR');
        \AppLocalize\Localization::selectAppLocale('fr_FR');

        $size = ConvertHelper_StorageSizeEnum::getSizeByName('mb');

        $this->assertEquals('mégaoctet', $size->getLabelSingular());

        \AppLocalize\Localization::reset();
    }

    public function test_spaces2tabs(): void
    {
        $tests = array(
            array(
                'label' => 'No spaces',
                'value' => "Foo",
                'expected' => "Foo"
            ),
            array(
                'label' => 'Three spaces indentation',
                'value' => "   Foo",
                'expected' => "   Foo"
            ),
            array(
                'label' => 'Four spaces indentation',
                'value' => "    Foo",
                'expected' => "\tFoo"
            ),
            array(
                'label' => 'Seven spaces indentation',
                'value' => "       Foo",
                'expected' => "\t   Foo"
            ),
            array(
                'label' => 'Tabbed string',
                'value' => "\tFoo",
                'expected' => "\tFoo"
            ),
            array(
                'label' => 'Different spaces mix',
                'value' =>
                    "    Foo" . PHP_EOL .
                    "Foo    ",
                'expected' =>
                    "\tFoo" . PHP_EOL .
                    "Foo\t"
            )
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::spaces2tabs($test['value']);

            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }

    public function test_hidden2visible(): void
    {
        $tests = array(
            array(
                'label' => 'Spaces and newlines',
                'value' => " \n\r\t",
                'expected' => "[SPACE][LF][CR][TAB]"
            ),
            array(
                'label' => 'Control characters',
                'value' => "\x00\x0D\x15",
                'expected' => "[NUL][CR][NAK]"
            ),
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::hidden2visible($test['value']);

            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }

    public function test_normalizeTabs(): void
    {
        $tests = array(
            array(
                'label' => 'Two spaces indentation',
                'value' => "  Foo",
                'expected' => "  Foo"
            ),
            array(
                'label' => 'Four spaces indentation',
                'value' => "    Foo",
                'expected' => "\tFoo"
            ),
            array(
                'label' => 'Seven spaces indentation',
                'value' => "       Foo",
                'expected' => "\t   Foo"
            ),
            array(
                'label' => 'One-tabbed string',
                'value' => "\tFoo",
                'expected' => "\tFoo"
            ),
            array(
                'label' => 'Different tabs mix',
                'value' =>
                    "\t\t\tFoo" . PHP_EOL .
                    "\tFoo",
                'expected' =>
                    "\t\tFoo" . PHP_EOL .
                    "Foo"
            )
        );

        foreach ($tests as $test) {
            $result = ConvertHelper::normalizeTabs($test['value']);

            $this->assertEquals($test['expected'], $result, $test['label']);
        }
    }

    public function test_stripControlChars(): void
    {
        $string = file_get_contents($this->assetsFolder . '/ControlCharacters.txt');

        $result = ConvertHelper::stripControlCharacters($string);

        $this->assertEquals('SOHACKBELL', $result);
    }

    /**
     * Ensure that the automatic fixing of UTF8 characters works as intended.
     */
    public function test_stripControlChars_brokenUTF8(): void
    {
        $string = file_get_contents($this->assetsFolder . '/ControlCharactersBrokenUTF8.txt');

        $result = ConvertHelper::stripControlCharacters($string);

        $this->assertEquals('SOHACKBELLöäüYes', $result);
    }

    /**
     * The {@see ConvertHelper::callback2string()} method
     * must work even if the target is not a callable method.
     * This is different from {@see VariableInfo::toString()},
     * which can only detect callable arrays by checking if they
     * are indeed callable.
     *
     * Here it is assumed that the specified value is a callable method.
     */
    public function test_callback2string_notCallable(): void
    {
        $this->assertEquals(
            'foo::bar()',
            ConvertHelper::callback2string(array('foo', 'bar'))
        );
    }

    public function test_isUnicode() : void
    {
        $this->assertFalse(ConvertHelper::isStringUnicode('Some ASCII text !{}'));
        $this->assertTrue(ConvertHelper::isStringUnicode('Some Unicode text äöü'));
    }

    public function test_isUppercase() : void
    {
        $this->assertFalse(ConvertHelper::isCharUppercase('a'), '"a" must be lowercase');
        $this->assertTrue(ConvertHelper::isCharUppercase('A'), '"A" must be uppercase');
        $this->assertFalse(ConvertHelper::isCharUppercase('ä'), '"ä" must be lowercase');
        $this->assertTrue(ConvertHelper::isCharUppercase('Ä'), '"Ä" must be uppercase');
    }

    public function test_camel2snake() : void
    {
        $tests = array(
            array(
                'label' => 'Single',
                'text' => 'camel',
                'expected' => 'camel',
                'transliterate' => false
            ),
            array(
                'label' => 'Single with capital letters',
                'text' => 'Camel',
                'expected' => 'camel',
                'transliterate' => false
            ),
            array(
                'label' => 'Regular',
                'text' => 'camelCase',
                'expected' => 'camel_case',
                'transliterate' => false
            ),
            array(
                'label' => 'Longer',
                'text' => 'camelCaseString',
                'expected' => 'camel_case_string',
                'transliterate' => false
            ),
            array(
                'label' => 'With starting capital letter',
                'text' => 'CamelCase',
                'expected' => 'camel_case',
                'transliterate' => false
            ),
            array(
                'label' => 'With double capital letter',
                'text' => 'CamelACase',
                'expected' => 'camel_a_case',
                'transliterate' => false
            ),
            array(
                'label' => 'Unicode, no transliteration',
                'text' => 'ÖffnenDasFenster',
                'expected' => 'öffnen_das_fenster',
                'transliterate' => false
            ),
            array(
                'label' => 'Unicode, with transliteration',
                'text' => 'ÖffneDieTür',
                'expected' => 'oeffne_die_tuer',
                'transliterate' => true
            ),
            array(
                'label' => 'Special chars get stripped',
                'text' => '#Camel~With___Under--—?Scores!',
                'expected' => 'camel_with_under_scores',
                'transliterate' => false
            )
        );

        foreach($tests as $test)
        {
            $this->assertSame(
                Encoding::toUTF8($test['expected']),
                ConvertHelper::camel2snake($test['text'], $test['transliterate']),
                $test['label']
            );
        }
    }

    public function test_snake2camel() : void
    {
        $tests = array(
            array(
                'label' => 'Regular',
                'text' => 'snake_case',
                'expected' => 'snakeCase',
                'transliterate' => false
            ),
            array(
                'label' => 'Longer',
                'text' => 'snake_case_string',
                'expected' => 'snakeCaseString',
                'transliterate' => false
            ),
            array(
                'label' => 'With capital letters',
                'text' => 'Snake_Case',
                'expected' => 'snakeCase',
                'transliterate' => false
            ),
            array(
                'label' => 'All uppercase',
                'text' => 'SNAKE_CASE',
                'expected' => 'snakeCase',
                'transliterate' => false
            ),
            array(
                'label' => 'Duplicate and trailing underscores',
                'text' => '_snake____case_',
                'expected' => 'snakeCase',
                'transliterate' => false
            ),
            array(
                'label' => 'With double capital letter',
                'text' => 'snake_a_case',
                'expected' => 'snakeACase',
                'transliterate' => false
            ),
            array(
                'label' => 'Unicode, no transliteration',
                'text' => 'öffnen_das_fenster',
                'expected' => 'öffnenDasFenster',
                'transliterate' => false
            ),
            array(
                'label' => 'Unicode, with transliteration',
                'text' => 'öffnen_das_fenster',
                'expected' => 'oeffnenDasFenster',
                'transliterate' => true
            ),
            array(
                'label' => 'Capital unicode char transliteration',
                'text' => 'Foo_Über',
                'expected' => 'fooUeber',
                'transliterate' => true
            )
        );

        foreach($tests as $test)
        {
            $this->assertSame(
                Encoding::toUTF8($test['expected']),
                ConvertHelper::snake2camel($test['text'], $test['transliterate']),
                $test['label']
            );
        }
    }

    public function test_ucFirst() : void
    {
        $this->assertSame('Foo', ConvertHelper::ucFirst('foo'));
        $this->assertSame('Öffnen', ConvertHelper::ucFirst('öffnen'));
        $this->assertSame(' foo', ConvertHelper::ucFirst(' foo'));
    }

    public function test_toWords() : void
    {
        $this->assertSame(
            array(
                'foo4life',
                'x2',
                'bar',
                'Ho',
                'argh',
                'UPPER',
                'ext',
                'öäü'
            ),
            ConvertHelper::string2words('#foo4life x2 bar! Ho--— / argh?_UPPER.ext, öäü')->split()
        );
    }

    public function test_toWordsIncludeMoreWordCharacters() : void
    {
        $this->assertSame(
            array(
                'foo4life',
                'UPPER.ext'
            ),
            StringHelper::explodeWords(' foo4life UPPER.ext ', array('.'))
        );
    }

    public function test_toCamel() : void
    {
        $this->assertSame('camelCase', ConvertHelper::string2camel('camel case'));
        $this->assertSame('fooBarHere', ConvertHelper::string2camel('Foo BAR_here!'));
        $this->assertSame('öffneDieTür', ConvertHelper::string2Camel('Öffne die Tür!'));
        $this->assertSame('oeffneDieTuer', ConvertHelper::string2Camel('Öffne die Tür!', true));
        $this->assertSame('fooUeber', ConvertHelper::string2Camel('Foo Über', true), 'Transliterated capital letters must only have the first letter capitalized.');
        $this->assertSame('f00Bar', ConvertHelper::string2Camel('#+   f0—0!~Bar'));
        $this->assertSame('foo❤️Bar', ConvertHelper::string2Camel('foo❤️ bar'));
    }

    public function test_removeSpec️ialCharacters() : void
    {
        $this->assertSame('', ConvertHelper::removeSpecialChars('#+~*/`´?}][{()&%$§"\'!^_-—<>|,;.:=@€“”…¶¿·՚՜°！'));
    }

    public function test_toSnake() : void
    {
        $this->assertSame('snake_case', ConvertHelper::string2snake('snake case'));
        $this->assertSame('foo_bar_here', ConvertHelper::string2snake('Foo BAR_here!'));
        $this->assertSame('öffne_die_tür', ConvertHelper::string2snake('Öffne die Tür!'));
        $this->assertSame('oeffne_die_tuer', ConvertHelper::string2snake('Öffne die Tür!', true));
        $this->assertSame('foo_ueber', ConvertHelper::string2snake('Foo Über', true), 'Transliterated capital letters must only have the first letter capitalized.');
        $this->assertSame('f0_0_bar', ConvertHelper::string2snake('#+   f0—0!~Bar'));
        $this->assertSame('foo❤️_bar', ConvertHelper::string2snake('foo❤️ bar'));
    }
}
