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

class CollectionTests extends BaseTestCase
{
    // region: _Tests

    public function test_createFromExisting() : void
    {
        $collection = ArrayDataCollection::create();

        $instance = ArrayDataCollection::create($collection);

        $this->assertSame($collection, $instance);
    }

    public function test_getString() : void
    {
        $tests = array(
            'bool' => array(
                'value' => true,
                'expected' => ''
            ),
            'string' => array(
                'value' => 'string',
                'expected' => 'string'
            ),
            'null' => array(
                'value' => null,
                'expected' => ''
            ),
            'int' => array(
                'value' => 42,
                'expected' => '42'
            ),
            'float' => array(
                'value' => 14.78,
                'expected' => '14.78'
            ),
            'array' => array(
                'value' => array('data' => 'here'),
                'expected' => ''
            )
        );

        $collection = $this->create($tests);

        foreach($tests as $name => $test)
        {
            $this->assertSame(
                $test['expected'],
                $collection->getString($name),
                $this->renderMessage($name, $test)
            );
        }
    }

    public function test_getBool() : void
    {
        $tests = array(
            // FALSE values
            'string' => array(
                'value' => 'string',
                'expected' => false
            ),
            'null' => array(
                'value' => null,
                'expected' => false
            ),
            'int' => array(
                'value' => 42,
                'expected' => false
            ),
            'float' => array(
                'value' => 14.78,
                'expected' => false
            ),
            'array' => array(
                'value' => array('data' => 'here'),
                'expected' => false
            ),

            // TRUE values
            'integer_one' => array(
                'value' => 1,
                'expected' => true
            ),
            'bool' => array(
                'value' => true,
                'expected' => true
            ),
            'yes' => array(
                'value' => 'yes',
                'expected' => true
            ),
            'true' => array(
                'value' => 'true',
                'expected' => true
            )
        );

        $collection = $this->create($tests);

        foreach($tests as $name => $test)
        {
            $this->assertSame(
                $test['expected'],
                $collection->getBool($name),
                $this->renderMessage($name, $test)
            );
        }
    }

    public function test_getInt() : void
    {
        $tests = array(
            'bool' => array(
                'value' => true,
                'expected' => 0
            ),
            'null' => array(
                'value' => null,
                'expected' => 0
            ),
            'array' => array(
                'value' => array('data' => 'here'),
                'expected' => 0
            ),
            'int' => array(
                'value' => 42,
                'expected' => 42
            ),
            'int-string' => array(
                'value' => '42',
                'expected' => 42
            ),
            'float' => array(
                'value' => 14.78,
                'expected' => 14
            ),
            'float-string' => array(
                'value' => '25.493',
                'expected' => 25
            )
        );

        $collection = $this->create($tests);

        foreach($tests as $name => $test)
        {
            $this->assertSame(
                $test['expected'],
                $collection->getInt($name),
                $this->renderMessage($name, $test)
            );
        }
    }

    public function test_getFloat() : void
    {
        $tests = array(
            'bool' => array(
                'value' => true,
                'expected' => 0.0
            ),
            'null' => array(
                'value' => null,
                'expected' => 0.0
            ),
            'int' => array(
                'value' => 42,
                'expected' => 42.0
            ),
            'int-string' => array(
                'value' => '42',
                'expected' => 42.0
            ),
            'float' => array(
                'value' => 14.78,
                'expected' => 14.78
            ),
            'float-string' => array(
                'value' => '78.456',
                'expected' => 78.456
            ),
            'array' => array(
                'value' => array('data' => 'here'),
                'expected' => 0.0
            )
        );

        $collection = $this->create($tests);

        foreach($tests as $name => $test)
        {
            $this->assertSame(
                $test['expected'],
                $collection->getFloat($name),
                $this->renderMessage($name, $test)
            );
        }
    }

    public function test_getArray() : void
    {
        $tests = array(
            'null' => array(
                'value' => null,
                'expected' => array()
            ),
            'array' => array(
                'value' => array('data' => 'here'),
                'expected' => array('data' => 'here')
            ),
            'float' => array(
                'value' => 14.78,
                'expected' => array()
            )
        );

        $collection = $this->create($tests);

        foreach($tests as $name => $test)
        {
            $this->assertSame(
                $test['expected'],
                $collection->getArray($name),
                $this->renderMessage($name, $test)
            );
        }
    }

    public function test_getJSON() : void
    {
        $tests = array(
            'null' => array(
                'value' => null,
                'expected' => array()
            ),
            'boolean' => array(
                'value' => 'true',
                'expected' => array()
            ),
            'json' => array(
                'value' => json_encode(array('data' => 'here'), JSON_THROW_ON_ERROR),
                'expected' => array('data' => 'here')
            ),
            'array' => array(
                'value' => array('data' => 'here'),
                'expected' => array('data' => 'here')
            )
        );

        $collection = $this->create($tests);

        foreach($tests as $name => $test)
        {
            $this->assertSame(
                $test['expected'],
                $collection->getJSONArray($name),
                $this->renderMessage($name, $test)
            );
        }
    }

    public function test_getJSONInvalid() : void
    {
        $collection = ArrayDataCollection::create(array('json' => 'not valid JSON'));

        $this->assertSame(array(), $collection->getJSONArray('json'));
    }

    public function test_setKey() : void
    {
        $collection = ArrayDataCollection::create();

        $this->assertNull($collection->getKey('foo'));

        $collection->setKey('foo', 'string');

        $this->assertSame('string', $collection->getKey('foo'));
    }

    public function test_setKey_overwrite() : void
    {
        $collection = ArrayDataCollection::create(array('foo' => 'bar'));

        $this->assertSame('bar', $collection->getKey('foo'));

        $collection->setKey('foo', 'overwritten');

        $this->assertSame('overwritten', $collection->getKey('foo'));
    }

    public function test_setKeys() : void
    {
        $collection = ArrayDataCollection::create(array(
            'existing' => 'value'
        ));

        $collection->setKeys(array(
            'foo' => 'bar',
            'existing' => 'overwritten'
        ));

        $this->assertSame('bar', $collection->getKey('foo'));
        $this->assertSame('overwritten', $collection->getKey('existing'));
    }

    public function test_combine() : void
    {
        $collectionA = ArrayDataCollection::create()
            ->setKey('foo', 'bar_A')
            ->setKey('a_only', 'value_A');

        $collectionB = ArrayDataCollection::create()
            ->setKey('foo','bar_B')
            ->setKey('b_only', 'value_B');

        $collectionC = $collectionA->combine($collectionB);

        $this->assertSame('bar_B', $collectionC->getKey('foo'));
        $this->assertSame('value_A', $collectionC->getKey('a_only'));
        $this->assertSame('value_B', $collectionC->getKey('b_only'));
    }

    public function test_mergeWith() : void
    {
        $collectionA = ArrayDataCollection::create()
            ->setKey('foo', 'bar_A')
            ->setKey('a_only', 'value_A');

        $collectionB = ArrayDataCollection::create()
            ->setKey('foo','bar_B')
            ->setKey('b_only', 'value_B');

        $collectionA->mergeWith($collectionB);

        $this->assertSame('bar_B', $collectionA->getKey('foo'));
        $this->assertSame('value_A', $collectionA->getKey('a_only'));
        $this->assertSame('value_B', $collectionA->getKey('b_only'));
    }

    public function test_keyExists() : void
    {
        $collection = ArrayDataCollection::create(array('foo' => null));

        $this->assertFalse($collection->keyExists('bar'));
        $this->assertTrue($collection->keyExists('foo'));
    }

    public function test_keyHasValue() : void
    {
        $collection = ArrayDataCollection::create(array('foo' => null, 'bar' => 'yes'));

        $this->assertFalse($collection->keyHasValue('foo'));
        $this->assertTrue($collection->keyHasValue('bar'));
    }

    public function test_remove() : void
    {
        $collection = ArrayDataCollection::create(array('foo' => 'bar'));

        $this->assertTrue($collection->keyExists('foo'));

        $collection->removeKey('foo');

        $this->assertFalse($collection->keyExists('foo'));
    }

    public function test_dateTime() : void
    {
        $collection = ArrayDataCollection::create();

        $value = '2022-09-14 12:00:00';

        $collection->setDateTime('date', new DateTime($value));

        $stored = $collection->getDateTime('date');

        $this->assertNotNull($stored);
        $this->assertSame(
            $value,
            $stored->format('Y-m-d H:i:s')
        );
    }

    public function test_dateTimeTimestamp() : void
    {
        $collection = ArrayDataCollection::create();

        $date = new DateTime('2022-09-14 12:00:00');

        $collection->setDateTime('date', $date);

        $this->assertSame(
            $date->getTimestamp(),
            $collection->getTimestamp('date')
        );
    }

    public function test_microtimeTimestamp() : void
    {
        $collection = ArrayDataCollection::create();

        $date = Microtime::createNow();

        $collection->setMicrotime('date', $date);

        $this->assertSame(
            $date->getTimestamp(),
            $collection->getTimestamp('date')
        );
    }

    public function test_microtime() : void
    {
        $collection = ArrayDataCollection::create();
        $time = Microtime::createNow();

        $collection->setMicrotime('time', $time);

        $stored = $collection->getMicrotime('time');

        $this->assertNotNull($stored);
        $this->assertSame(
            $time->getISODate(),
            $stored->getISODate()
        );
    }

    public function test_createFromJSON() : void
    {
        $json = JSONConverter::var2json(array(
            'foo' => 'bar',
            'int' => 42,
            'float' => 14.78,
            'array' => array('data' => 'here')
        ));

        $collection = ArrayDataCollection::createFromJSON($json);

        $this->assertSame('bar', $collection->getString('foo'));
        $this->assertSame(42, $collection->getInt('int'));
        $this->assertSame(14.78, $collection->getFloat('float'));
        $this->assertSame(array('data' => 'here'), $collection->getArray('array'));
    }

    public function test_getStringN() : void
    {
        $collection = ArrayDataCollection::create(array(
            'null' => null,
            'empty-string' => '',
            'zero' => 0,
            'zero-string' => '0',
            'array' => array('data' => 'here')
        ));

        $this->assertNull($collection->getStringN('null'));
        $this->assertNull($collection->getStringN('empty-string'));
        $this->assertSame('0', $collection->getStringN('zero'));
        $this->assertSame('0', $collection->getStringN('zero-string'));
        $this->assertNull($collection->getStringN('array'));
    }

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

    public function test_clearKeys() : void
    {
        $collection = ArrayDataCollection::create(array(
            'foo' => 'bar',
            'int' => 42
        ));

        $this->assertTrue($collection->keyExists('foo'));
        $this->assertTrue($collection->keyExists('int'));

        $collection->clearKeys();

        $this->assertSame(array(), $collection->getData());
    }

    // endregion

    // region: Support methods


    /**
     * @param string $name
     * @param array{expected:mixed} $test
     * @return string
     * @throws BaseException
     */
    private function renderMessage(string $name, array $test) : string
    {
        return
            '['.$name.'] did not match expected value:'.PHP_EOL.
            parseVariable($test['expected'])->enableType()->toString();
    }

    /**
     * @param array<string,array{value:mixed,expected:mixed}> $tests
     * @return ArrayDataCollection
     */
    private function create(array $tests) : ArrayDataCollection
    {
         return ArrayDataCollection::create($this->compileData($tests));
    }

    /**
     * @param array<string,array{value:mixed,expected:mixed}> $tests
     * @return array<string,mixed>
     */
    private function compileData(array $tests) : array
    {
        $data = array();

        foreach($tests as $name => $test)
        {
            $data[$name] = $test['value'];
        }

        return $data;
    }

    // endregion
}
