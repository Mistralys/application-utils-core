<?php

declare(strict_types=1);

namespace AppUtilsTests\ArrayDataCollection;

use AppUtils\ArrayDataCollection;
use AppUtils\ArrayDataCollection\ArrayDataObservable;
use AppUtilsTestClasses\BaseTestCase;

final class ObservableTests extends BaseTestCase
{
    // region: _Tests

    public function test_removeKey() : void
    {
        $collection = $this->createCollection(array(
            'test' => 123
        ));

        $this->assertTrue($collection->keyExists('test'));

        $collection->removeKey('test');

        $this->assertNull($collection->getKey('test'));
        $this->assertFalse($collection->keyExists('test'));

        $this->assertKeyRemovedTriggered('test');
    }

    public function test_clearingTheCollectionTriggersKeyRemovals() : void
    {
        $collection = $this->createCollection(array(
            'a' => 1,
            'b' => 2,
            'c' => 3
        ));

        $collection->clearKeys();

        $this->assertKeysRemovedTriggered(array('a', 'b', 'c'));

        $this->assertEmpty($collection->getData());
    }

    public function test_addNewKeyIsTriggered() : void
    {
        $collection = $this->createCollection();

        $collection->setKey('newKey', 'newValue');

        $this->assertSame('newValue', $collection->getKey('newKey'));

        $this->assertKeyAddedTriggered('newKey', 'newValue');
    }

    public function test_addKeyAlsoTriggersAKeyChange() : void
    {
        $collection = $this->createCollection();

        $collection->setKey('newKey', 'newValue');

        $this->assertKeyChangeTriggered('newKey', null, 'newValue');
    }

    public function test_settingKeyToNullIsNotARemoval() : void
    {
        $collection = $this->createCollection(array(
            'key1' => 'value1'
        ));

        $collection->setKey('key1', null);

        $this->assertTrue($collection->keyExists('key1'));
        $this->assertSame(null, $collection->getKey('key1'));

        $this->assertKeyChangeTriggered('key1', 'value1', null);
        $this->assertKeyRemovedNotTriggered('key1');
    }

    public function test_removingAKeyIsACollectionChange() : void
    {
        $collection = $this->createCollection(array(
            'key1' => 'value1'
        ));

        $collection->removeKey('key1');

        $this->assertCollectionChangedTriggered();
    }

    public function test_settingAKeyIsAKeyChange() : void
    {
        $collection = $this->createCollection(array(
            'key1' => 'value1'
        ));

        $collection->setKey('key1', 'newValue');

        $this->assertKeyChangeTriggered('key1', 'value1', 'newValue');
    }

    public function test_settingAKeyIsACollectionChange() : void
    {
        $collection = $this->createCollection();

        $collection->setKey('key1', 'newValue');

        $this->assertCollectionChangedTriggered();
    }

    public function test_settingToTheSameValueIsNotAChange() : void
    {
        $collection = $this->createCollection(array(
            'key1' => 'value1'
        ));

        $collection->setKey('key1', 'value1');

        $this->assertCollectionChangedNotTriggered();
    }

    public function test_changingNullToEmptyStringIsNotAChange() : void
    {
        $collection = $this->createCollection(array(
            'key1' => null
        ));

        $collection->setKey('key1', '');

        $this->assertCollectionChangedNotTriggered();
    }

    public function test_removeObserver() : void
    {
        $collection = ArrayDataObservable::create(array(
            'key1' => 'value1',
            'key2' => 'value2'
        ));

        $number = $collection->onKeyRemoved($this->callback_keyRemoved(...));

        $collection->removeKey('key1');
        $this->assertKeyRemovedTriggered('key1');

        $collection->removeObserver($number);

        $collection->removeKey('key2');
        $this->assertKeyRemovedNotTriggered('key2');
    }

    public function test_clearAllObservers() : void
    {
        $collection = ArrayDataObservable::create(array(
            'key1' => 'value1',
            'key2' => 'value2'
        ));

        $collection->onKeyRemoved($this->callback_keyRemoved(...));

        $collection->clearObservers();

        $collection->removeKey('key1');
        $this->assertKeyRemovedNotTriggered('key1');
    }

    public function test_createCollectionWithRegularCollection() : void
    {
        $collection = ArrayDataObservable::create(ArrayDataCollection::create(array('key1' => 'value1')));

        $this->assertSame('value1', $collection->getKey('key1'));
    }

    public function test_createRegularCollectionWithObservableRevertsBackToRegular() : void
    {
        $collection = ArrayDataObservable::create(array(
            'key1' => 'value1'
        ));

        $new = ArrayDataCollection::create($collection);

        $this->assertNotInstanceOf(ArrayDataObservable::class, $new);
    }

    public function test_valueConversionsHandleValueChanges() : void
    {
        $collection = ArrayDataObservable::create(array(
            'null' => null,
            'zero' => 0,
            'bool' => false,
            'emptyString' => '',
            'emptyArray' => array(),
            'assocArray' => array(
                'a' => 1,
                'b' => 2
            )
        ));

        $collection->onKeyChanged($this->callback_keyChanged(...));

        $collection->setKey('null', '');
        $collection->setKey('emptyString', array());
        $collection->setKey('emptyArray', null);
        $collection->setKey('zero', '0');
        $collection->setKey('bool', 'false');
        $collection->setKey('assocArray', array('b' => 2, 'a' => 1)); // same values, different order

        $this->assertKeyChangeNotTriggered('zero');
        $this->assertKeyChangeNotTriggered('bool');
        $this->assertKeyChangeNotTriggered('null');
        $this->assertKeyChangeNotTriggered('emptyString');
        $this->assertKeyChangeNotTriggered('emptyArray');
        $this->assertKeyChangeNotTriggered('assocArray');
    }

    // endregion

    // region: Support methods

    /**
     * @var string[]
     */
    private array $removedKeys = array();

    /**
     * @var string[]
     */
    private array $addedKeys = array();

    /**
     * @var array<string,array{old: mixed, new: mixed}>
     */
    private array $changedKeys = array();

    private bool $collectionChanged = false;

    private function callback_keyRemoved(ArrayDataObservable $collection, string $name) : void
    {
        $this->removedKeys[] = $name;
    }

    private function callback_keyAdded(ArrayDataObservable $collection, string $name, mixed $value) : void
    {
        $this->addedKeys[$name] = $value;
    }

    private function callback_keyChanged(ArrayDataObservable $collection, string $name, mixed $oldValue, mixed $newValue) : void
    {
        $this->changedKeys[$name] = array(
            'old' => $oldValue,
            'new' => $newValue
        );
    }

    private function callback_collectionChanged(ArrayDataObservable $collection) : void
    {
        $this->collectionChanged = true;
    }

    private function assertCollectionChangedTriggered() : void
    {
        $this->assertTrue($this->collectionChanged, 'The collection change was not triggered as expected.');
    }

    private function assertCollectionChangedNotTriggered() : void
    {
        $this->assertFalse($this->collectionChanged, 'The collection change was triggered but was not expected to be triggered.');
    }

    private function assertKeyChangeNotTriggered(string $name) : void
    {
        $this->assertArrayNotHasKey($name, $this->changedKeys, sprintf(
            'The key [%s] was changed but was not expected to be changed.',
            $name
        ));
    }

    private function assertKeyChangeTriggered(string $name, mixed $oldValue, mixed $newValue) : void
    {
        $this->assertArrayHasKey($name, $this->changedKeys, sprintf(
            'The key [%s] was not changed as expected.',
            $name
        ));

        $this->assertSame($oldValue, $this->changedKeys[$name]['old'], sprintf(
            'The key [%s] was changed but with an unexpected old value.',
            $name
        ));

        $this->assertSame($newValue, $this->changedKeys[$name]['new'], sprintf(
            'The key [%s] was changed but with an unexpected new value.',
            $name
        ));
    }

    private function assertKeyAddedTriggered(string $name, mixed $value) : void
    {
        $this->assertArrayHasKey($name, $this->addedKeys, sprintf(
            'The key [%s] was not added as expected.',
            $name
        ));

        $this->assertSame($value, $this->addedKeys[$name], sprintf(
            'The key [%s] was added but with an unexpected value.',
            $name
        ));
    }

    private function assertKeyRemovedTriggered(string $name) : void
    {
        $this->assertContains($name, $this->removedKeys, sprintf(
            'The key [%s] was not removed as expected.',
            $name
        ));
    }

    private function assertKeyRemovedNotTriggered(string $name) : void
    {
        $this->assertNotContains($name, $this->removedKeys, sprintf(
            'The key [%s] was removed but was not expected to be removed.',
            $name
        ));
    }

    /**
     * @param string[] $names
     * @return void
     */
    private function assertKeysRemovedTriggered(array $names) : void
    {
        foreach($names as $name) {
            $this->assertKeyRemovedTriggered($name);
        }
    }

    /**
     * @param array<int|string,mixed> $data
     * @return ArrayDataObservable
     */
    private function createCollection(array $data=array()) : ArrayDataObservable
    {
        $collection = new ArrayDataObservable($data);

        $collection->onKeyRemoved($this->callback_keyRemoved(...));
        $collection->onKeyAdded($this->callback_keyAdded(...));
        $collection->onKeyChanged($this->callback_keyChanged(...));
        $collection->onCollectionChanged($this->callback_collectionChanged(...));

        return $collection;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->removedKeys = array();
    }

    // endregion
}
