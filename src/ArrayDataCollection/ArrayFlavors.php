<?php
/**
 * @package Application Utils
 * @subpackage Collections
 */

declare(strict_types=1);

namespace AppUtils\ArrayDataCollection;

use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;

/**
 * Utility class providing various methods for filtering and converting arrays.
 *
 * ## Usage
 *
 * - The `toXXX()` methods apply type conversion rules to values.
 * - The `filterXXX()` methods only filter values without any type conversion.
 *
 * ## Null-Aware Methods
 *
 * Methods ending with `N` (e.g., `toIndexedStringsN()`) are null-aware:
 *
 * - The `toXXXN()` methods convert empty strings to `NULL`.
 * - The `filterXXXN()` methods preserve `NULL` values.
 *
 * @package Application Utils
 * @subpackage Collections
 */
class ArrayFlavors
{
    /**
     * @var array<int|string,mixed>
     */
    private array $value;

    /**
     * @param array<int|string,mixed> $value The array to be processed.
     */
    public function __construct(array $value)
    {
        $this->value = $value;
    }

    // region: Filtering Methods

    /**
     * Filters an indexed array to keep only strings.
     * Resets the keys of the resulting array.
     *
     * @param bool $pruneEmptyValues
     * @return string[]
     */
    public function filterIndexedStrings(bool $pruneEmptyValues=false) : array
    {
        $result = array();

        foreach($this->value as $value)
        {
            if(!is_string($value)) {
                continue;
            }

            if($pruneEmptyValues && $value === '') {
                continue;
            }

            $result[] = $value;
        }

        return $result;
    }

    /**
     * Returns an array of integers from the specified key.
     *
     * - Float values are converted to integers.
     * - Non-numeric values are ignored.
     * - The keys of the resulting array are reset.
     *
     * @return array<int,int>
     */
    public function filterIndexedIntegers() : array
    {
        $result = array();

        foreach($this->value as $value)
        {
            if(is_int($value)) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Returns an associative array with string or integer
     * keys and string values.
     *
     * - Strictly only string values are kept.
     *
     * @return array<string|int,string>
     */
    public function filterAssocString(bool $pruneEmptyValues=false) : array
    {
        return array_filter(
            $this->filterAssocStringN(),
            /**
             * @param string|NULL $value
             * @return bool
             */
            function (?string $value) use($pruneEmptyValues) : bool {
                if($pruneEmptyValues && $value === '') {
                    return false;
                }
                return $value !== null;
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Returns an associative array with string or integer
     * keys and string or NULL values.
     *
     * - String values are kept.
     * - NULL values are preserved.
     *
     * @return array<string|int,string|NULL>
     */
    public function filterAssocStringN() : array
    {
        return array_filter(
            $this->value,
            /**
             * @param mixed $value
             */
            function ($value) : bool {
                return $value === NULL || is_string($value);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Returns an associative array with strictly only string
     * keys and string values.
     *
     * > NOTE: Since PHP internally converts string keys to
     * > integer when they are numeric, this method will
     * > remove any numeric keys.
     *
     * @return array<string,string>
     */
    public function filterAssocStringString(bool $pruneEmptyValues=false) : array
    {
        return array_filter(
            $this->filterAssocString($pruneEmptyValues),
            /**
             * @param string $value
             * @param int|string $key
             * @return bool
             */
            function (string $value, $key) : bool {
                return is_string($key);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Returns an associative array with string keys and values
     * that can be either strings or NULL.
     *
     * @return array<string,string|NULL>
     */
    public function filterAssocStringStringN() : array
    {
        return array_filter(
            $this->filterAssocStringN(),
            /**
             * @param string|NULL $value
             * @param int|string $key
             * @return bool
             */
            function (?string $value, $key) : bool {
                return is_string($key);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }
    /**
     * @return array<string,string|int|float|bool>
     */
    public function filterAssocStringScalar(bool $pruneEmptyValues=false) : array
    {
        return array_filter(
            $this->filterAssocScalar($pruneEmptyValues),
            /**
             * @param mixed $value
             * @param int|string $key
             * @return bool
             */
            function ($value, $key) : bool {
                return is_string($key);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @return array<string,string|int|float|bool|NULL>
     */
    public function filterAssocStringScalarN() : array
    {
        return array_filter(
            $this->filterAssocScalarN(),
            /**
             * @param string|int|float|bool|NULL $value
             * @param int|string $key
             * @return bool
             */
            function ($value, $key) : bool {
                return is_string($key);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @param bool $pruneEmptyValues
     * @return array<int|string,string|int|float|bool>
     */
    public function filterAssocScalar(bool $pruneEmptyValues=false) : array
    {
        return array_filter(
            $this->filterAssocScalarN(),
            /**
             * @param string|int|float|bool|NULL $value
             * @return bool
             */
            function ($value) use($pruneEmptyValues) : bool {
                if($value === null) {
                    return false;
                }

                if($pruneEmptyValues && $value === '') {
                    return false;
                }

                return true;
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @return array<int|string,string|int|float|bool|NULL>
     */
    public function filterAssocScalarN() : array
    {
        return array_filter(
            $this->value,
            /**
             * @param mixed $value
             * @return bool
             */
            function ($value) : bool {
                return $value === null || is_scalar($value);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    // endregion

    // region: Conversion Methods

    /**
     * Converts the array to a JSON string.
     *
     * @return string
     * @throws JSONConverterException
     */
    public function toJSON() : string
    {
        return JSONConverter::var2json($this->value);
    }

    /**
     * Gets the array as an {@see ArrayDataCollection} instance.
     * @return ArrayDataCollection
     */
    public function toCollection() : ArrayDataCollection
    {
        return ArrayDataCollection::create($this->value);
    }

    /**
     * Gets the array as an {@see ArrayDataObservable} instance.
     * @return ArrayDataObservable
     */
    public function toObservableCollection() : ArrayDataObservable
    {
        return ArrayDataObservable::create($this->value);
    }

    /**
     * Converts an indexed array to contain only strings.
     *
     * - Non-scalar values are ignored.
     * - Scalar values are converted to strings.
     * - The keys of the resulting array are reset.
     *
     * Value conversion rules:
     *
     * - String => String
     * - Integer => String
     * - Float => String
     * - Boolean => 'true' or 'false'
     * - NULL => Empty string
     *
     * @param bool $pruneEmptyValues
     * @return string[]
     */
    public function toIndexedStrings(bool $pruneEmptyValues=false) : array
    {
        $result = array();

        foreach($this->value as $value)
        {
            if($value !== null && !is_scalar($value)) {
                continue;
            }

            $value = ConvertHelper::toString($value);

            if($pruneEmptyValues && $value === '') {
                continue;
            }

            $result[] = $value;
        }

        return $result;
    }

    /**
     * Converts an indexed array to contain only strings.
     *
     * - Non-scalar values are ignored.
     * - Scalar values are converted to strings.
     * - The keys of the resulting array are reset.
     * - NULL values are preserved.
     * - Empty strings are converted to NULL.
     *
     * Value conversion rules:
     *
     * - String => String
     * - Integer => String
     * - Float => String
     * - Boolean => 'true' or 'false'
     * - NULL => NULL
     * - Empty string => NULL
     *
     * @return string[]
     */
    public function toIndexedStringsN() : array
    {
        $result = array();

        foreach($this->value as $value)
        {
            if($value !== null && !is_scalar($value)) {
                continue;
            }

            $result[] = ConvertHelper::toStringN($value);
        }

        return $result;
    }

    /**
     * Returns an array of integers from the specified key.
     *
     * - Float values are converted to integers.
     * - Non-numeric values are ignored.
     * - The keys of the resulting array are reset.
     *
     * @return int[]
     */
    public function toIndexedIntegers() : array
    {
        $result = array();

        foreach($this->value as $value)
        {
            if(is_numeric($value)) {
                $result[] = (int)$value;
            }
        }

        return $result;
    }

    /**
     * Returns an associative array with string or integer
     * keys and string values. Values are converted to strings
     * whenever possible.
     *
     * @return array<string|int,string>
     */
    public function toAssocString(bool $pruneEmptyValues=false) : array
    {
        $result = array();

        foreach($this->value as $key => $value)
        {
            if($value !== null && !is_scalar($value)) {
                continue;
            }

            $value = ConvertHelper::toString($value);

            if($pruneEmptyValues && $value === '') {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Returns an associative array with string or integer
     * keys and null-aware string values.
     *
     * - Scalar values are converted to strings.
     * - NULL values are preserved.
     * - Empty strings are converted to NULL.
     *
     * @return array<string|int,string|NULL>
     */
    public function toAssocStringN() : array
    {
        $result = array();

        foreach($this->value as $key => $value)
        {
            if($value !== null && !is_scalar($value)) {
                continue;
            }

            $result[$key] = ConvertHelper::toStringN($value);
        }

        return $result;
    }

    /**
     * Returns an associative array with string or integer
     * keys and scalar values.
     *
     * > NOTE: Functionally identical to {@see self::filterAssocScalar()}
     * > as no conversion rules are applied.
     *
     * @return array<string|int,string|int|float|bool>
     */
    public function toAssocScalar(bool $pruneEmptyValues=false) : array
    {
        return $this->filterAssocScalar($pruneEmptyValues);
    }

    /**
     * Returns an associative array with string or integer
     * keys and null-aware scalar values.
     *
     * - Only scalar values are preserved.
     * - NULL values are preserved.
     *
     * > NOTE: Functionally identical to {@see self::filterAssocScalarN()}
     * > as no conversion rules are applied.
     *
     * @return array<string|int,string|int|float|bool|NULL>
     */
    public function toAssocScalarN() : array
    {
        return $this->filterAssocScalarN();
    }

    // endregion

}
