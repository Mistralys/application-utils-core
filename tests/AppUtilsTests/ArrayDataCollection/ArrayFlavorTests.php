<?php

declare(strict_types=1);

namespace AppUtilsTests\ArrayDataCollection;

use AppUtils\ArrayDataCollection;
use AppUtils\BaseException;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\Microtime;
use AppUtilsTestClasses\BaseTestCase;
use DateTime;
use function AppUtils\parseVariable;

class ArrayFlavorTests extends BaseTestCase
{
    // region: _Tests

    public function test_filterIndexedStrings() : void
    {
        $this->assertSame(
            array(
                '',
                'test',
                '123',
                '123.456',
                'numeric-key'
            ),
            $this->getFlavored()->filterIndexedStrings()
        );
    }

    public function test_filterIndexedStringsPruneEmpty() : void
    {
        $this->assertSame(
            array(
                'test',
                '123',
                '123.456',
                'numeric-key'
            ),
            $this->getFlavored()->filterIndexedStrings(true)
        );
    }

    public function test_toIndexedStrings() : void
    {
        $this->assertSame(
            array(
                '',
                'test',
                '',
                '123',
                '123',
                'true',
                '123.456',
                '123.456',
                'numeric-key'
            ),
            $this->getFlavored()->toIndexedStrings()
        );
    }

    public function test_toIndexedStringsPruneEmpty() : void
    {
        $this->assertSame(
            array(
                'test',
                '123',
                '123',
                'true',
                '123.456',
                '123.456',
                'numeric-key'
            ),
            $this->getFlavored()->toIndexedStrings(true)
        );
    }

    public function test_toIndexedStringsNullAware() : void
    {
        $this->assertSame(
            array(
                NULL,
                'test',
                NULL,
                '123',
                '123',
                'true',
                '123.456',
                '123.456',
                'numeric-key'
            ),
            $this->getFlavored()->toIndexedStringsN()
        );
    }

    public function test_toAssocString() : void
    {
        $this->assertSame(
            array(
                'empty-string' => '',
                'string' => 'test',
                'null' => '',
                'integer' => '123',
                'int-string' => '123',
                'boolean' => 'true',
                'float' => '123.456',
                'float-string' => '123.456',
                42 => 'numeric-key', // numeric key
            ),
            $this->getFlavored()->toAssocString()
        );
    }

    public function test_toAssocStringPruneEmpty() : void
    {
        $this->assertSame(
            array(
                'string' => 'test',
                'integer' => '123',
                'int-string' => '123',
                'boolean' => 'true',
                'float' => '123.456',
                'float-string' => '123.456',
                42 => 'numeric-key',
            ),
            $this->getFlavored()->toAssocString(true)
        );
    }

    public function test_toAssocStringNullAware() : void
    {
        $this->assertSame(
            array(
                'empty-string' => null,
                'string' => 'test',
                'null' => null,
                'integer' => '123',
                'int-string' => '123',
                'boolean' => 'true',
                'float' => '123.456',
                'float-string' => '123.456',
                42 => 'numeric-key',
            ),
            $this->getFlavored()->toAssocStringN()
        );
    }

    public function test_filterAssocString() : void
    {
        $this->assertSame(
            array(
                'empty-string' => '',
                'string' => 'test',
                'int-string' => '123',
                'float-string' => '123.456',
                42 => 'numeric-key'
            ),
            $this->getFlavored()->filterAssocString()
        );
    }

    public function test_filterAssocStringPruneEmpty() : void
    {
        $this->assertSame(
            array(
                'string' => 'test',
                'int-string' => '123',
                'float-string' => '123.456',
                42 => 'numeric-key'
            ),
            $this->getFlavored()->filterAssocString(true)
        );
    }

    public function test_filterAssocStringNullAware() : void
    {
        $this->assertSame(
            array(
                'empty-string' => '',
                'string' => 'test',
                'null' => null,
                'int-string' => '123',
                'float-string' => '123.456',
                42 => 'numeric-key'
            ),
            $this->getFlavored()->filterAssocStringN()
        );
    }

    public function test_filterAssocStringString() : void
    {
        $this->assertSame(
            array(
                'empty-string' => '',
                'string' => 'test',
                'int-string' => '123',
                'float-string' => '123.456',
            ),
            $this->getFlavored()->filterAssocStringString()
        );
    }

    public function test_filterAssocStringStringPruneEmpty() : void
    {
        $this->assertSame(
            array(
                'string' => 'test',
                'int-string' => '123',
                'float-string' => '123.456',
            ),
            $this->getFlavored()->filterAssocStringString(true)
        );
    }

    public function test_filterAssocStringStringNullAware() : void
    {
        $this->assertSame(
            array(
                'empty-string' => '',
                'string' => 'test',
                'null' => null,
                'int-string' => '123',
                'float-string' => '123.456'
            ),
            $this->getFlavored()->filterAssocStringStringN()
        );
    }

    public function test_filterAssocStringScalar() : void
    {
        $this->assertSame(
            array(
                'empty-string' => '',
                'string' => 'test',
                'integer' => 123,
                'int-string' => '123',
                'boolean' => true,
                'float' => 123.456,
                'float-string' => '123.456'
            ),
            $this->getFlavored()->filterAssocStringScalar()
        );
    }

    public function test_filterAssocStringScalarPruneEmpty() : void
    {
        $this->assertSame(
            array(
                'string' => 'test',
                'integer' => 123,
                'int-string' => '123',
                'boolean' => true,
                'float' => 123.456,
                'float-string' => '123.456'
            ),
            $this->getFlavored()->filterAssocStringScalar(true)
        );
    }

    public function test_filterAssocStringScalarNullAware() : void
    {
        $this->assertSame(
            array(
                'empty-string' => '',
                'string' => 'test',
                'null' => null,
                'integer' => 123,
                'int-string' => '123',
                'boolean' => true,
                'float' => 123.456,
                'float-string' => '123.456'
            ),
            $this->getFlavored()->filterAssocStringScalarN()
        );
    }

    public function test_filterAssocScalar() : void
    {
        $this->assertSame(
            array(
                'empty-string' => '',
                'string' => 'test',
                'integer' => 123,
                'int-string' => '123',
                'boolean' => true,
                'float' => 123.456,
                'float-string' => '123.456',
                42 => 'numeric-key'
            ),
            $this->getFlavored()->filterAssocScalar()
        );
    }

    public function test_filterAssocScalarPruneEmpty() : void
    {
        $this->assertSame(
            array(
                'string' => 'test',
                'integer' => 123,
                'int-string' => '123',
                'boolean' => true,
                'float' => 123.456,
                'float-string' => '123.456',
                42 => 'numeric-key'
            ),
            $this->getFlavored()->filterAssocScalar(true)
        );
    }

    public function test_filterAssocScalarN() : void
    {
        $this->assertSame(
            array(
                'empty-string' => '',
                'string' => 'test',
                'null' => null,
                'integer' => 123,
                'int-string' => '123',
                'boolean' => true,
                'float' => 123.456,
                'float-string' => '123.456',
                42 => 'numeric-key'
            ),
            $this->getFlavored()->filterAssocScalarN()
        );
    }

    public function test_filterIndexedIntegers() : void
    {
        $this->assertSame(
            array(
                123
            ),
            $this->getFlavored()->filterIndexedIntegers()
        );
    }

    public function test_toIndexedIntegers() : void
    {
        $this->assertSame(
            array(
                123,
                123,
                123,
                123,
            ),
            $this->getFlavored()->toIndexedIntegers()
        );
    }

    // endregion

    // region: Support methods

    private function getFlavored() : ArrayDataCollection\ArrayFlavors
    {
        return ArrayDataCollection::create(self::TEST_ARRAY)->getArrayFlavored('test');
    }

    private const TEST_ARRAY = array(
        'test' => array(
            'array' => array(),
            'empty-string' => '',
            'string' => 'test',
            'null' => null,
            'integer' => 123,
            'int-string' => '123',
            'boolean' => true,
            'float' => 123.456,
            'float-string' => '123.456',
            42 => 'numeric-key'
        )
    );

    // endregion
}
