<?php
/**
 * @package AppUtils
 * @subpackage ArrayDataCollection
 */

declare(strict_types=1);

namespace AppUtils\ArrayDataCollection;

use AppUtils\ArrayDataCollection;

/**
 * Helper class to provide array manipulation methods
 * for a specific key in an array-based value in
 * {@see ArrayDataCollection}.
 *
 * @package AppUtils
 * @subpackage ArrayDataCollection
 */
class ArraySetters
{
    private string $key;
    private ArrayDataCollection $collection;

    public function __construct(ArrayDataCollection $collection, string $key)
    {
        $this->collection = $collection;
        $this->key = $key;
    }

    /**
     * @return array<int|string,mixed>
     */
    private function getArray() : array
    {
        return $this->collection->getArray($this->key);
    }

    /**
     * Clears the array, setting it to an empty array.
     * @return ArrayDataCollection
     */
    public function clear() : ArrayDataCollection
    {
        return $this->setValue(array());
    }

    /**
     * Sets the array value.
     *
     * @param array<int|string,mixed> $value
     * @return ArrayDataCollection
     */
    private function setValue(array $value) : ArrayDataCollection
    {
        $this->collection->setKey($this->key, $value);
        return $this->collection;
    }

    /**
     * Pushes an entry to the end of the array.
     *
     * > WARNING: Does not check the type of the
     * > array. It is assumed that the target array
     * > is an indexed array.
     *
     * @param mixed $value
     * @return ArrayDataCollection
     */
    public function pushIndexed(mixed $value) : ArrayDataCollection
    {
        $array = $this->getArray();

        $array[] = $value;

        return $this->setValue($array);
    }

    public function mergeWith(array $values) : ArrayDataCollection
    {
        return $this->setValue(array_merge(
            $this->getArray(),
            $values
        ));
    }

    public function replaceWith(array $values) : ArrayDataCollection
    {
        return $this->setValue($values);
    }

    public function setIndex(int $index, mixed $value) : ArrayDataCollection
    {
        $array = $this->getArray();

        $array[$index] = $value;

        return $this->setValue($array);
    }

    /**
     * Removes the first entry of the array.
     *
     * Has no effect if the array is empty.
     *
     * > WARNING: Does not check the type of the
     * > array. It is assumed that the target array
     * > is an indexed array.
     *
     * @return mixed
     */
    public function shiftIndexed() : mixed
    {
        $array = $this->getArray();
        $return = null;

        if(!empty($array)) {
            $return = array_shift($array);
            $this->setValue($array);
        }

        return $return;
    }

    /**
     * Prepends an entry to the start of the array.
     *
     * > WARNING: Does not check the type of the
     * > array. It is assumed that the target array
     * > is an indexed array.
     *
     * @param mixed $value
     * @return ArrayDataCollection
     */
    public function unshiftIndexed(mixed $value) : ArrayDataCollection
    {
        $array = $this->getArray();

        array_unshift($array, $value);

        return $this->setValue($array);
    }

    /**
     * Sets an associative array value of an array key.
     *
     * @param string $key The array key to set.
     * @param mixed $value
     * @return ArrayDataCollection
     */
    public function setAssoc(string $key, mixed $value) : ArrayDataCollection
    {
        $array = $this->getArray();
        $array[$key] = $value;
        return $this->setValue($array);
    }

    /**
     * Removes an associative array value of an array key.
     *
     * @param string $key The array key to remove.
     * @return ArrayDataCollection
     */
    public function removeAssoc(string $key) : ArrayDataCollection
    {
        $array = $this->getArray();

        if(array_key_exists($key, $array)) {
            unset($array[$key]);
            $this->setValue($array);
        }

        return $this->collection;
    }

    public function sortKeys(?callable $sortCallback=null) : ArrayDataCollection
    {
        $array = $this->getArray();

        if($sortCallback !== null) {
            uksort($array, $sortCallback);
        } else {
            ksort($array);
        }

        return $this->setValue($array);
    }

    public function sortKeysNatCase() : ArrayDataCollection
    {
        return $this->sortKeys('strnatcasecmp');
    }
}
