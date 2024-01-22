<?php
/**
 * @package AppUtils
 * @subpackage Type Filter
 */

declare(strict_types=1);

namespace AppUtils\TypeFilter;

/**
 * Handles values more leniently for strings, numbers, and booleans.
 *
 * @package AppUtils
 * @subpackage Type Filter
 */
class LenientType extends BaseTypeFilter
{
    public function getStringOrNull() : ?string
    {
        if((is_string($this->value)) || is_numeric($this->value)) {
            return (string)$this->value;
        }

        return null;
    }

    public function getIntOrNull() : ?int
    {
        if((is_numeric($this->value) || is_bool($this->value)) && !$this->isEmptyOrNull()) {
            return (int)$this->value;
        }

        return null;
    }

    public function getFloatOrNull() : ?float
    {
        if((is_numeric($this->value) || is_bool($this->value)) && !$this->isEmptyOrNull()) {
            return (float)$this->value;
        }

        return null;
    }

    public function getBoolOrNull() : ?bool
    {
        if(is_bool($this->value)) {
            return $this->value;
        }

        if($this->value === 1 || $this->value === '1') {
            return true;
        }

        if($this->value === 0 || $this->value === '0') {
            return false;
        }

        return null;
    }
}
