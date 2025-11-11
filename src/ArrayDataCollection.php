<?php
/**
 * @package Application Utils
 * @subpackage Collections
 * @see \AppUtils\ArrayDataCollection
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\ArrayDataCollection\ArrayDataCollectionException;
use AppUtils\ArrayDataCollection\ArrayDataObservable;
use AppUtils\ArrayDataCollection\ArrayFlavors;
use AppUtils\ArrayDataCollection\ArraySetters;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use DateTime;
use Throwable;

/**
 * Collection class used to work with associative arrays used to
 * store key => value pairs.
 *
 * Offers strict typed methods to access the available keys, to
 * remove the hassle of checking whether keys exist, and whether
 * they are of the expected type.
 *
 * ## Exception-free handling
 *
 * The collection is not intended to validate any of the stored
 * data, this is the purview of the host class. The utility
 * methods will only return values that match the expected type.
 * Invalid data is ignored, and a matching default value returned.
 *
 * ## Observing changes to the collection
 *
 * Use the {@see ArrayDataObservable} class to monitor changes
 * to the collection. This allows you to automatically react
 * to changes in the data.
 *
 * @package Application Utils
 * @subpackage Collections
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ArrayDataCollection
{
    /**
     * @var array<string|int,mixed>
     */
    protected array $data = array();

    /**
     * @param array<string|int,mixed> $data
     */
    public function __construct(array $data=array())
    {
        $this->setKeys($data);
    }

    /**
     * @param ArrayDataCollection|array<string|int,mixed>|NULL $data
     * @return ArrayDataCollection
     */
    public static function create(ArrayDataCollection|array|null $data=array()) : self
    {
        if($data instanceof ArrayDataObservable) {
            $data = $data->getData();
        }

        if($data instanceof self) {
            return $data;
        }

        return new self((array)$data);
    }

    /**
     * Creates an array converter from a JSON encoded array.
     *
     * @param string $json
     * @return ArrayDataCollection
     * @throws JSONConverterException
     */
    public static function createFromJSON(string $json) : ArrayDataCollection
    {
        return self::create(JSONConverter::json2array($json));
    }

    /**
     * @return array<string|int,mixed>
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * @param array<string|int,mixed> $data
     * @return $this
     */
    public function setKeys(array $data) : self
    {
        foreach($data as $key => $value)
        {
            $this->setKey((string)$key, $value);
        }

        return $this;
    }

    /**
     * @param string $name
     * @param mixed|NULL $value
     * @return $this
     */
    public function setKey(string $name, mixed $value) : self
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Gets a helper to set array values in a strictly typed manner.
     * @param string $name
     * @return ArraySetters
     */
    public function setArray(string $name) : ArraySetters
    {
        return new ArraySetters($this, $name);
    }

    /**
     * Merges the current collection's data with that of
     * the target collection, replacing existing values.
     *
     * @param ArrayDataCollection $collection
     * @return $this
     */
    public function mergeWith(ArrayDataCollection $collection) : self
    {
        return $this->setKeys($collection->getData());
    }

    /**
     * Combines the current collection's data with the
     * target collection, and returns a new collection
     * that contains the data of both collections.
     *
     * NOTE: The source collection's values are overwritten
     * by the target collection in the process.
     *
     * @param ArrayDataCollection $collection
     * @return ArrayDataCollection
     */
    public function combine(ArrayDataCollection $collection) : ArrayDataCollection
    {
        return self::create($this->data)->setKeys($collection->getData());
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getKey(string $name) : mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * The stored value can be a string or a number.
     * All other types and values will return an empty
     * string.
     *
     * @param string $name
     * @return string
     */
    public function getString(string $name) : string
    {
        $value = $this->getKey($name);

        if(is_string($value)) {
            return $value;
        }

        if(is_numeric($value)) {
            return (string)$value;
        }

        return '';
    }

    /**
     * Like {@see ArrayDataCollection::getString()}, but returns
     * `null` if the value is empty or `null`.
     *
     * @param string $name
     * @return string|null
     */
    public function getStringN(string $name) : ?string
    {
        $value = $this->getString($name);
        if($value !== '') {
            return $value;
        }

        return null;
    }

    /**
     * The stored value can be an integer or float,
     * or a string containing an integer or float.
     * All other types and values return <code>0</code>.
     *
     * @param string $name
     * @return int
     */
    public function getInt(string $name) : int
    {
        $value = $this->getKey($name);

        if(is_numeric($value)) {
            return (int)$value;
        }

        return 0;
    }

    /**
     * Attempts to decode the stored string as JSON.
     *
     * > NOTE: Only _valid JSON_ that decodes into an array is
     * > accepted. Invalid JSON, booleans or numbers will
     * > return an empty array.
     *
     * @param string $name
     * @return array<int|string,mixed> The decoded array, or an empty array otherwise.
     */
    public function getJSONArray(string $name) : array
    {
        $value = $this->getKey($name);

        // Does not need to be decoded after all
        if(is_array($value)) {
            return $value;
        }

        if(empty($value) || !is_string($value)) {
            return array();
        }

        return JSONConverter::json2arraySilent($value);
    }

    /**
     * @param string $name
     * @return array<int|string,mixed>
     */
    public function getArray(string $name) : array
    {
        $value = $this->getKey($name);

        if(is_array($value)) {
            return $value;
        }

        return array();
    }

    /**
     * Gives options to access array values in a strictly typed manner.
     *
     * @param string $name
     * @return ArrayFlavors
     */
    public function getArrayFlavored(string $name) : ArrayFlavors
    {
        return new ArrayFlavors($this->getArray($name));
    }

    public function getBool(string $name) : bool
    {
        $value = $this->getKey($name);

        if(is_string($value)) {
            $value = strtolower($value);
        }

        return
            $value === true
            ||
            $value === 'true'
            ||
            $value === 'yes'
            ||
            $value === 1
            ||
            $value === '1';
    }

    public function getFloat(string $name) : float
    {
        $value = $this->getKey($name);

        if(is_numeric($value)) {
            return (float)$value;
        }

        return 0.0;
    }

    /**
     * Whether the specified key exists in the data set,
     * even if its value is <code>NULL</code>.
     *
     * @param string $name
     * @return bool
     */
    public function keyExists(string $name) : bool
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * Whether the specified key exists in the data set,
     * and has a non-<code>NULL</code> value.
     *
     * @param string $name
     * @return bool
     */
    public function keyHasValue(string $name) : bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Removes the specified key from the data set, if it exists.
     *
     * @param string $name
     * @return $this
     */
    public function removeKey(string $name) : self
    {
        unset($this->data[$name]);
        return $this;
    }

    /**
     * Fetches a {@see DateTime} instance from the stored
     * key value, which can be either of the following:
     *
     * - A timestamp (int|string)
     * - A DateTime string
     *
     * @param string $name
     * @return DateTime|null The {@see DateTime} instance, or <code>NULL</code> if empty or invalid.
     *
     * @see self::requireDateTime() For an exception-throwing variant with guaranteed return value.
     */
    public function getDateTime(string $name) : ?DateTime
    {
        $value = $this->getString($name);

        if(empty($value)) {
            return null;
        }

        if(is_numeric($value)) {
            $date = new DateTime();
            $date->setTimestamp((int)$value);
            return $date;
        }

        try
        {
            return new DateTime($value);
        }
        catch (Throwable)
        {
            return null;
        }
    }

    /**
     * Like {@see self::getDateTime()}, but will throw an exception
     * if no valid date/time value is found. Use this when you know
     * that the key must contain a valid date/time value.
     *
     * @param string $name
     * @return DateTime
     * @throws ArrayDataCollectionException
     */
    public function requireDateTime(string $name) : DateTime
    {
        $date = $this->getDateTime($name);
        if($date !== null) {
            return $date;
        }

        throw new ArrayDataCollectionException(
            'No date/time value found for key.',
            sprintf(
                'The data key [%s] is empty or does not contain a valid date/time value. '.PHP_EOL.
                'Current value: [%s]',
                $name,
                parseVariable($this->getKey($name))->enableType()->toString()
            ),
            ArrayDataCollectionException::CODE_MISSING_DATETIME
        );
    }

    /**
     * Restores a {@see Microtime} instance from a key previously
     * set using {@see ArrayDataCollection::setMicrotime()}.
     *
     * @param string $name
     * @return Microtime|null The {@see Microtime} instance, or <code>NULL</code> if empty or invalid.
     *
     * @see self::requireMicrotime() For an exception-throwing variant with guaranteed return value.
     */
    public function getMicrotime(string $name) : ?Microtime
    {
        try
        {
            return Microtime::createFromString($this->getString($name));
        }
        catch (Throwable)
        {
            return null;
        }
    }

    /**
     * Like {@see self::getMicrotime()}, but will throw an exception
     * if no valid microtime value is found. Use this when you know
     * that the key must contain a valid microtime value.
     *
     * @param string $name
     * @return Microtime
     * @throws ArrayDataCollectionException
     */
    public function requireMicrotime(string $name) : Microtime
    {
        $time = $this->getMicrotime($name);
        if($time !== null) {
            return $time;
        }

        throw new ArrayDataCollectionException(
            'No microtime value found for key.',
            sprintf(
                'The data key [%s] is empty or does not contain a valid microtime value. '.PHP_EOL.
                'Current value: [%s]',
                $name,
                parseVariable($this->getKey($name))->enableType()->toString()
            ),
            ArrayDataCollectionException::CODE_MISSING_MICROTIME
        );
    }

    /**
     * Sets a date and time key, which can be restored later
     * using {@see ArrayDataCollection::getDateTime()} or
     * {@see ArrayDataCollection::getTimestamp()}.
     *
     * @param string $name
     * @param DateTime $time
     * @return $this
     */
    public function setDateTime(string $name, DateTime $time) : self
    {
        return $this->setKey($name, $time->format(DATE_W3C));
    }

    /**
     * Sets a microtime key: Guarantees that the microseconds
     * information will be persisted correctly if restored later
     * using {@see ArrayDataCollection::getMicrotime()}.
     *
     * **NOTE:** Fetching a timestamp from a microtime key with
     * {@see ArrayDataCollection::getTimestamp()} will work,
     * but the microseconds information will be lost.
     *
     * @param string $name
     * @param Microtime $time
     * @return $this
     */
    public function setMicrotime(string $name, Microtime $time) : self
    {
        return $this->setKey($name, $time->getISODate());
    }

    /**
     * Fetches a stored timestamp. The source value can be
     * any of the following:
     *
     * - An timestamp (int|string)
     * - A DateTime key
     * - A Microtime key
     *
     * @param string $name
     * @return int The timestamp, or <code>0</code> if none/invalid.
     */
    public function getTimestamp(string $name) : int
    {
        $date = $this->getDateTime($name);

        if($date !== null)
        {
            return $date->getTimestamp();
        }

        return 0;
    }

    /**
     * Clears all keys from the collection.
     * @return $this
     */
    public function clearKeys() : self
    {
        $this->data = array();
        return $this;
    }

    /**
     * @return array<int,string|int> All available keys in the collection.
     * @param bool $sorted Whether to sort the keys alphabetically before returning them.
     */
    public function getKeys(bool $sorted) : array
    {
        $keys = array_keys($this->data);

        if(!$sorted) {
            return $keys;
        }

        usort(
            $keys,
            /**
             * @param string|int $a
             * @param string|int $b
             * @return int
             */
            static function(string|int $a, string|int $b) : int {
                return strnatcasecmp((string)$a, (string)$b);
            }
        );

        return $keys;
    }
}
