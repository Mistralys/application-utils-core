<?php

declare(strict_types=1);

namespace AppUtilsTests\ArrayDataCollection;

use AppUtils\ArrayDataCollection;
use AppUtilsTestClasses\BaseTestCase;

final class ArraySetterTests extends BaseTestCase
{
    public function test_pushArrayNotExists() : void
    {
        $collection = ArrayDataCollection::create();

        $collection->setArray('items')->pushIndexed('first');

        $this->assertSame(array('first'), $collection->getArray('items'));
    }

    public function test_pushArrayExists() : void
    {
        $collection = ArrayDataCollection::create(array(
            'items' => array('first')
        ));

        $collection->setArray('items')->pushIndexed('second');

        $this->assertSame(array('first','second'), $collection->getArray('items'));
    }

    public function test_setArrayKeyNotExists() : void
    {
        $collection = ArrayDataCollection::create();

        $collection->setArray('items')->setAssoc('key', 'value');

        $this->assertSame(array('key' => 'value'), $collection->getArray('items'));
    }

    public function test_setArrayKeyExists() : void
    {
        $collection = ArrayDataCollection::create(array(
            'items' => array('key1' => 'value1')
        ));

        $collection->setArray('items')->setAssoc('key2', 'value2');

        $this->assertSame(array('key1' => 'value1', 'key2' => 'value2'), $collection->getArray('items'));
    }

    public function test_removeArrayKey() : void
    {
        $collection = ArrayDataCollection::create(array(
            'items' => array('key1' => 'value1')
        ));

        $collection->setArray('items')->removeAssoc('key1');

        $this->assertSame(array(), $collection->getArray('items'));
    }

    public function test_clearArray() : void
    {
        $collection = ArrayDataCollection::create(array(
            'items' => array('key1' => 'value1')
        ));

        $collection->setArray('items')->clear();

        $this->assertSame(array(), $collection->getArray('items'));
    }

    public function test_shiftIndexedArray() : void
    {
        $collection = ArrayDataCollection::create(array(
            'items' => array('first', 'second', 'third')
        ));

        $this->assertSame('first', $collection->setArray('items')->shiftIndexed());
        $this->assertSame(array('second', 'third'), $collection->getArray('items'));

        $this->assertSame('second', $collection->setArray('items')->shiftIndexed());
        $this->assertSame(array('third'), $collection->getArray('items'));

        $this->assertSame('third', $collection->setArray('items')->shiftIndexed());
        $this->assertSame(array(), $collection->getArray('items'));

        $this->assertNull($collection->setArray('items')->shiftIndexed());
        $this->assertSame(array(), $collection->getArray('items'));
    }

    public function test_unshiftIndexArray() : void
    {
        $collection = ArrayDataCollection::create(array(
            'items' => array('second', 'third')
        ));

        $collection->setArray('items')->unshiftIndexed('first');

        $this->assertSame(array('first', 'second', 'third'), $collection->getArray('items'));

        $collection->setArray('items')->unshiftIndexed('zero');

        $this->assertSame(array('zero', 'first', 'second', 'third'), $collection->getArray('items'));
    }

    public function test_setArrayReplacesExistingNonArrayValue() : void
    {
        $collection = ArrayDataCollection::create(array(
            'items' => 'not an array'
        ));

        $collection->setArray('items')->pushIndexed('first');

        $this->assertSame(array('first'), $collection->getArray('items'));
    }

    public function test_mergeArrayWith() : void
    {
        $collection = ArrayDataCollection::create(array(
            'items' => array(
                'item' => 'value'
            )
        ));

        $collection->setArray('items')->mergeWith(array(
            'another_item' => 'another_value'
        ));

        $this->assertSame(
            array(
                'item' => 'value',
                'another_item' => 'another_value'
            ),
            $collection->getArray('items')
        );
    }

    public function test_sortKeys() : void
    {
        $collection = ArrayDataCollection::create(array(
            'items' => array(
                'b' => 2,
                'a' => 1,
                'c' => 3
            )
        ));

        $collection->setArray('items')->sortKeys();

        $this->assertSame(
            array(
                'a' => 1,
                'b' => 2,
                'c' => 3
            ),
            $collection->getArray('items')
        );
    }

    public function test_sortKeysWithNaturalCaseCallback() : void
    {
        $collection = ArrayDataCollection::create(array(
            'items' => array(
                'BArgh' => 1,
                'bArgh' => 2,
                '10argh' => 4,
                '1argh' => 3
            )
        ));

        $collection->setArray('items')->sortKeysNatCase();

        $this->assertSame(
            array(
                '1argh' => 3,
                '10argh' => 4,
                'BArgh' => 1,
                'bArgh' => 2,
            ),
            $collection->getArray('items')
        );
    }

    public function test_setArrayIndex() : void
    {
        $collection = ArrayDataCollection::create();

        $collection->setArray('items')->setIndex(2, 'two');

        $array = $collection->getArray('items');

        $this->assertArrayHasKey(2, $array);
        $this->assertSame('two', $array[2]);
    }

    public function test_setArrayReplaceWith() : void
    {
        $collection = ArrayDataCollection::create(array(
            'items' => array(
                'item' => 'value'
            )
        ));

        $collection->setArray('items')->replaceWith(array(
            'another_item' => 'another_value'
        ));

        $this->assertSame(
            array(
                'another_item' => 'another_value'
            ),
            $collection->getArray('items')
        );
    }
}
