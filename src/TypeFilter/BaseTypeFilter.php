<?php
/**
 * @package AppUtils
 * @subpackage Type Filter
 */

declare(strict_types=1);

namespace AppUtils\TypeFilter;

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use AppUtils\Interfaces\StringableInterface;
use DateTime;

/**
 * Utility class used to access values by specific types,
 * to ensure type safety.
 *
 * Usage:
 *
 * - {@see self::createStrict()} for strict type checking
 * - {@see self::createLenient()} for more lenient type checking for strings and numbers
 *
 * @package AppUtils
 * @subpackage Type Filter
 */
abstract class BaseTypeFilter implements StringableInterface
{
    /**
     * @var mixed
     */
    protected $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Create a new type filter instance that does strict type checking.
     * @param mixed|NULL $value
     * @return StrictType
     */
    public static function createStrict($value) : StrictType
    {
        return new StrictType($value);
    }

    /**
     * Create a new type filter instance that does lenient type checking.
     *
     * Changes compared to the strict type filter:
     *
     * - Numeric strings are accepted as numbers.
     * - Integer 0 and 1 are accepted as boolean values.
     * - Numeric values are accepted as strings.
     *
     * @param mixed|NULL $value
     * @return LenientType
     */
    public static function createLenient($value) : LenientType
    {
        return new LenientType($value);
    }

    /**
     * Returns strings or numeric values as strings.
     * @return string
     */
    public function getString() : string
    {
        return (string)$this->getStringOrNull();
    }

    abstract public function getStringOrNull() : ?string;

    public function getDate() : DateTime
    {
        return $this->getObject(DateTime::class);
    }

    public function getDateOrNull() : ?DateTime
    {
        return $this->getObjectOrNull(DateTime::class);
    }

    /**
     * Fetches a strictly typed boolean value.
     * For a more flexible variant, see {@see self::getAnyBool()}.
     *
     * @return bool
     */
    public function getBool() : bool
    {
        return $this->getBoolOrNull() === true;
    }

    abstract public function getBoolOrNull() : ?bool;

    /**
     * Converts any boolean-compatible value to a boolean.
     * See {@see ConvertHelper::string2bool()} for details.
     *
     * @return bool
     * @throws ConvertHelper_Exception
     */
    public function getAnyBool() : bool
    {
        return ConvertHelper::string2bool($this->value);
    }

    public function getInt() : int
    {
        return (int)$this->getIntOrNull();
    }

    abstract public function getIntOrNull() : ?int;

    public function getFloat() : float
    {
        return (float)$this->getFloatOrNull();
    }

    abstract public function getFloatOrNull() : ?float;

    public function isEmptyOrNull() : bool
    {
        return $this->value !== 0 && $this->value !== '0' && empty($this->value);
    }

    /**
     * @return array<mixed>
     */
    public function getArray() : array
    {
        return (array)$this->getArrayOrNull();
    }

    /**
     * @return array<mixed>|null
     */
    public function getArrayOrNull() : ?array
    {
        if(is_array($this->value)) {
            return $this->value;
        }

        return null;
    }

    /**
     * Assumes that the value is an object of the given class,
     * and returns it or throws an exception.
     *
     * @template ClassInstanceType
     * @param class-string<ClassInstanceType> $class
     * @return ClassInstanceType
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public function getObject(string $class)
    {
        return ClassHelper::requireObjectInstanceOf(
            $class,
            $this->value
        );
    }

    /**
     * If the value is of the given class, returns it,
     * otherwise returns null.
     *
     * @template ClassInstanceType
     * @param class-string<ClassInstanceType> $class
     * @return ClassInstanceType|NULL
     */
    public function getObjectOrNull(string $class)
    {
        if($this->value instanceof $class) {
            return $this->value;
        }

        return null;
    }

    public function __toString() : string
    {
        return $this->getString();
    }
}
