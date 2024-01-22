<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtils\TypeFilter\BaseTypeFilter;
use AppUtilsTestClasses\BaseTestCase;
use stdClass;

final class TypeFilterTests extends BaseTestCase
{
    public function test_nullValue() : void
    {
        $strict = BaseTypeFilter::createStrict(null);
        $lenient = BaseTypeFilter::createLenient(null);

        $this->assertSame('', $strict->getString());
        $this->assertNull($strict->getStringOrNull());
        $this->assertSame('', $lenient->getString());
        $this->assertNull($lenient->getStringOrNull());

        $this->assertSame(0, $strict->getInt());
        $this->assertNull($strict->getIntOrNull());
        $this->assertSame(0, $lenient->getInt());
        $this->assertNull($lenient->getIntOrNull());

        $this->assertSame(0.0, $strict->getFloat());
        $this->assertNull($strict->getFloatOrNull());
        $this->assertSame(0.0, $lenient->getFloat());
        $this->assertNull($lenient->getFloatOrNull());

        $this->assertFalse($strict->getBool());
        $this->assertFalse($strict->getAnyBool());
        $this->assertNull($strict->getBoolOrNull());

        $this->assertSame(array(), $strict->getArray());
        $this->assertNull($strict->getArrayOrNull());

        $this->assertNull($strict->getDateOrNull());

        $this->assertNull($strict->getObjectOrNull(stdClass::class));
    }

    public function test_zeroValue() : void
    {
        $strict = BaseTypeFilter::createStrict(0);
        $lenient = BaseTypeFilter::createLenient(0);

        $this->assertSame('', $strict->getString());
        $this->assertNull($strict->getStringOrNull());
        $this->assertSame('0', $lenient->getString());
        $this->assertSame('0', $lenient->getStringOrNull());

        $this->assertSame(0, $strict->getInt());
        $this->assertSame(0, $strict->getIntOrNull());
        $this->assertSame(0, $lenient->getInt());
        $this->assertSame(0, $lenient->getIntOrNull());

        $this->assertSame(0.0, $strict->getFloat());
        $this->assertNull($strict->getFloatOrNull());
        $this->assertSame(0.0, $lenient->getFloat());
        $this->assertSame(0.0, $lenient->getFloatOrNull());

        $this->assertFalse($strict->getBool());
        $this->assertFalse($strict->getAnyBool());
        $this->assertNull($strict->getBoolOrNull());

        $this->assertSame(array(), $strict->getArray());
        $this->assertNull($strict->getArrayOrNull());

        $this->assertNull($strict->getDateOrNull());

        $this->assertNull($strict->getObjectOrNull(stdClass::class));
    }

    public function test_zeroStringValue() : void
    {
        $strict = BaseTypeFilter::createStrict('0');
        $lenient = BaseTypeFilter::createLenient('0');

        $this->assertSame('0', $strict->getString());
        $this->assertSame('0', $strict->getStringOrNull());
        $this->assertSame('0', $lenient->getString());
        $this->assertSame('0', $lenient->getStringOrNull());

        $this->assertSame(0, $strict->getInt());
        $this->assertNull($strict->getIntOrNull());
        $this->assertSame(0, $lenient->getInt());
        $this->assertSame(0, $lenient->getIntOrNull());

        $this->assertSame(0.0, $strict->getFloat());
        $this->assertNull($strict->getFloatOrNull());
        $this->assertSame(0.0, $lenient->getFloat());
        $this->assertSame(0.0, $lenient->getFloatOrNull());

        $this->assertFalse($strict->getBool());
        $this->assertFalse($strict->getAnyBool());
        $this->assertNull($strict->getBoolOrNull());

        $this->assertSame(array(), $strict->getArray());
        $this->assertNull($strict->getArrayOrNull());

        $this->assertNull($strict->getDateOrNull());

        $this->assertNull($strict->getObjectOrNull(stdClass::class));
    }

    public function test_arrayValue() : void
    {
        $array = array('foo' => 'bar');
        $var = BaseTypeFilter::createStrict($array);

        $this->assertSame('', $var->getString());
        $this->assertNull($var->getStringOrNull());

        $this->assertSame(0, $var->getInt());
        $this->assertNull($var->getIntOrNull());

        $this->assertSame(0.0, $var->getFloat());
        $this->assertNull($var->getFloatOrNull());

        $this->assertSame($array, $var->getArray());
        $this->assertSame($array, $var->getArrayOrNull());

        $this->assertNull($var->getDateOrNull());

        $this->assertNull($var->getObjectOrNull(stdClass::class));
    }

    public function test_boolValue() : void
    {
        $strict = BaseTypeFilter::createStrict(true);
        $lenient = BaseTypeFilter::createLenient(true);

        $this->assertSame('', $strict->getString());
        $this->assertNull($strict->getStringOrNull());
        $this->assertSame('', $lenient->getString());
        $this->assertNull($lenient->getStringOrNull());

        $this->assertSame(0, $strict->getInt());
        $this->assertNull($strict->getIntOrNull());
        $this->assertSame(1, $lenient->getInt());
        $this->assertSame(1, $lenient->getIntOrNull());

        $this->assertSame(0.0, $strict->getFloat());
        $this->assertNull($strict->getFloatOrNull());
        $this->assertSame(1.0, $lenient->getFloat());
        $this->assertSame(1.0, $lenient->getFloatOrNull());

        $this->assertTrue($strict->getBool());
        $this->assertTrue($strict->getAnyBool());
        $this->assertTrue($strict->getBoolOrNull());
        $this->assertTrue($lenient->getBool());
        $this->assertTrue($lenient->getAnyBool());
        $this->assertTrue($lenient->getBoolOrNull());

        $this->assertSame(array(), $strict->getArray());
        $this->assertNull($strict->getArrayOrNull());

        $this->assertNull($strict->getDateOrNull());

        $this->assertNull($strict->getObjectOrNull(stdClass::class));
    }

    public function test_boolValueNumeric() : void
    {
        $strict = BaseTypeFilter::createStrict(1);
        $lenient = BaseTypeFilter::createLenient(1);

        $this->assertSame('', $strict->getString());
        $this->assertNull($strict->getStringOrNull());
        $this->assertSame('1', $lenient->getString());
        $this->assertSame('1', $lenient->getStringOrNull());

        $this->assertSame(1, $strict->getInt());
        $this->assertSame(1, $strict->getIntOrNull());
        $this->assertSame(1, $lenient->getInt());
        $this->assertSame(1, $lenient->getIntOrNull());

        $this->assertSame(0.0, $strict->getFloat());
        $this->assertNull($strict->getFloatOrNull());
        $this->assertSame(1.0, $lenient->getFloat());
        $this->assertSame(1.0, $lenient->getFloatOrNull());

        $this->assertFalse($strict->getBool());
        $this->assertTrue($strict->getAnyBool());
        $this->assertNull($strict->getBoolOrNull());
        $this->assertTrue($lenient->getBool());
        $this->assertTrue($lenient->getAnyBool());
        $this->assertTrue($lenient->getBoolOrNull());

        $this->assertSame(array(), $strict->getArray());
        $this->assertNull($strict->getArrayOrNull());

        $this->assertNull($strict->getDateOrNull());

        $this->assertNull($strict->getObjectOrNull(stdClass::class));
    }

    public function test_getObjectValue() : void
    {
        $val = BaseTypeFilter::createStrict(new stdClass());

        $this->assertNull($val->getStringOrNull());
        $this->assertNull($val->getIntOrNull());
        $this->assertNull($val->getFloatOrNull());
        $this->assertNull($val->getBoolOrNull());
        $this->assertNull($val->getArrayOrNull());
        $this->assertNull($val->getDateOrNull());
        $this->assertInstanceOf(stdClass::class, $val->getObjectOrNull(stdClass::class));
    }
}
