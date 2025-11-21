<?php
/**
 * @package Application Utils
 * @subpackage Collections
 */

declare(strict_types=1);

namespace AppUtils\ArrayDataCollection;

use AppUtils\ArrayDataCollection;

/**
 * Extension of {@see ArrayDataCollection} that supports observers
 * to monitor changes to the collection. Use this to automatically
 * react to changes in the data.
 *
 * ## Value comparisons
 *
 * When checking for changes, values are converted to handle empty
 * checks and type differences to avoid triggering change events
 * unnecessarily.
 *
 * See the method {@see self::convertValue()} for details on how
 * values are converted before comparison. If needed, you can override
 * this method to customize the conversion logic.
 *
 * @package Application Utils
 * @subpackage Collections
 */
class ArrayDataObservable extends ArrayDataCollection
{
    /**
     * @param array<string,mixed>|ArrayDataCollection|ArrayDataObservable $data
     * @return ArrayDataObservable
     */
    public static function create($data=array()) : self
    {
        if($data instanceof self) {
            return $data;
        }

        if($data instanceof ArrayDataCollection) {
            $data = $data->getData();
        }

        return new self($data);
    }

    // region: Overridden Methods

    private bool $multiRemoval = false;

    public function clearKeys(): self
    {
        $this->multiRemoval = true;

        foreach ($this->getKeys(false) as $key) {
            $this->removeKey($key);
        }

        $this->multiRemoval = false;

        $this->triggerCollectionChanged();

        return $this;
    }

    public function removeKey(string $name): self
    {
        $oldValue = $this->getKey($name);

        parent::removeKey($name);

        $this->triggerKeyRemoved($name, $oldValue);

        return $this;
    }

    public function setKey(string $name, mixed $value): self
    {
        $oldValue = $this->getKey($name);

        if (!array_key_exists($name, $this->data)) {
            parent::setKey($name, $value);
            $this->triggerKeyAdded($name, $value);
            return $this;
        }

        if ($this->areValuesDifferent($oldValue, $value)) {
            parent::setKey($name, $value);
            $this->triggerKeyChanged($name, $oldValue, $value);
        }

        return $this;
    }

    /**
     * @param mixed $oldValue
     * @param mixed $newValue
     * @return bool
     */
    protected function areValuesDifferent(mixed $oldValue, mixed $newValue) : bool
    {
        return $this->convertValue($oldValue) !== $this->convertValue($newValue);
    }

    /**
     * @param mixed $value
     * @return mixed|null
     */
    public function convertValue(mixed $value) : mixed
    {
        if($value === '' || $value === array()) {
            $value = null;
        }

        // Floats and ints are converted to strings for the comparison.
        if(is_numeric($value)) {
            $value = (string)$value;
        }
        // 0 and 1 cannot be used, because they cannot be uniquely
        // identified as booleans or integers. Even 'yes' and 'no'
        // are not perfect, but they are less likely to cause issues.
        elseif($value === 'yes' || $value === 'true') {
            $value = true;
        } elseif($value === 'no' || $value === 'false') {
            $value = false;
        } elseif(is_array($value)) {
            ksort($value);
            array_map($this->convertValue(...), $value);
        }

        return $value;
    }

    // endregion

    // region: Observers

    private array $dataObservers = array();
    private array $keyObservers = array();
    private array $removeObservers = array();
    private array $addObservers = array();
    private int $observerCounter = 0;

    /**
     * Add an observer that is triggered when any data changes in the collection.
     * This includes adding, updating, or removing keys.
     *
     * @param callable(ArrayDataObservable) : void $observer
     * @return int
     */
    public function onCollectionChanged(callable $observer): int
    {
        $this->observerCounter++;
        $this->dataObservers[$this->observerCounter] = $observer;
        return $this->observerCounter;
    }

    /**
     * Add an observer that is triggered when the value of a specific key
     * changes in the collection.
     *
     * This includes adding, removing, or updating the key.
     * Use {@see self::onKeyAdded()} and {@see self::onKeyRemoved()}
     * for more precise control.
     *
     * @param callable(ArrayDataObservable, string, mixed, mixed) : void $observer
     * @return int
     */
    public function onKeyChanged(callable $observer): int
    {
        $this->observerCounter++;
        $this->keyObservers[$this->observerCounter] = $observer;
        return $this->observerCounter;
    }

    /**
     * @param callable(ArrayDataObservable, string, mixed) : void $observer
     * @return int
     */
    public function onKeyAdded(callable $observer): int
    {
        $this->observerCounter++;
        $this->addObservers[$this->observerCounter] = $observer;
        return $this->observerCounter;
    }

    /**
     * Adds an observer that is triggered when a key is removed from the collection.
     *
     * > NOTE: Setting a key to `null` is considered a change, not a removal.
     *
     * @param callable(ArrayDataObservable, string, mixed) : void $observer
     * @return int
     */
    public function onKeyRemoved(callable $observer): int
    {
        $this->observerCounter++;
        $this->removeObservers[$this->observerCounter] = $observer;
        return $this->observerCounter;
    }

    private function triggerKeyAdded(string $name, $value): void
    {
        foreach ($this->addObservers as $observer) {
            $observer($this, $name, $value);
        }

        $this->triggerKeyChanged($name, null, $value);
    }

    private function triggerKeyChanged(string $name, $oldValue, $newValue): void
    {
        foreach ($this->keyObservers as $observer) {
            $observer($this, $name, $oldValue, $newValue);
        }

        if (!$this->multiRemoval) {
            $this->triggerCollectionChanged();
        }
    }

    private function triggerKeyRemoved(string $name, $oldValue): void
    {
        foreach ($this->removeObservers as $observer) {
            $observer($this, $name, $oldValue);
        }

        $this->triggerKeyChanged($name, $oldValue, null);

        if (!$this->multiRemoval) {
            $this->triggerCollectionChanged();
        }
    }

    private function triggerCollectionChanged(): void
    {
        foreach ($this->dataObservers as $observer) {
            $observer($this);
        }
    }

    /**
     * Removes the observer with the given ID, if any.
     * @param int $observerID
     * @return $this
     */
    public function removeObserver(int $observerID): self
    {
        unset($this->keyObservers[$observerID]);
        unset($this->dataObservers[$observerID]);
        unset($this->removeObservers[$observerID]);
        unset($this->addObservers[$observerID]);
        return $this;
    }

    /**
     * Removes all observers that have been added, if any.
     * @return $this
     */
    public function clearObservers(): self
    {
        $this->keyObservers = array();
        $this->dataObservers = array();
        $this->removeObservers = array();
        $this->addObservers = array();
        return $this;
    }

    // endregion
}
