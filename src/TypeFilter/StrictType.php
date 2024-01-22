<?php
/**
 * @package AppUtils
 * @subpackage Type Filter
 */

declare(strict_types=1);

namespace AppUtils\TypeFilter;

/**
 * Handles values in strict mode.
 *
 * @package AppUtils
 * @subpackage Type Filter
 */
class StrictType extends BaseTypeFilter
{
    public function getStringOrNull() : ?string
    {
        if((is_string($this->value))) {
            return (string)$this->value;
        }

        return null;
    }

    public function getIntOrNull() : ?int
    {
        if(is_int($this->value)) {
            return $this->value;
        }

        return null;
    }

    public function getFloatOrNull() : ?float
    {
        if(is_float($this->value)) {
            return $this->value;
        }

        return null;
    }

    public function getBoolOrNull() : ?bool
    {
        if(is_bool($this->value)) {
            return $this->value;
        }

        return null;
    }
}
