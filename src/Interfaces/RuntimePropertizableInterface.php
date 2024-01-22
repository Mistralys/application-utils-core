<?php
/**
 * @package AppUtils
 * @subpackage Traits
 */

declare(strict_types=1);

namespace AppUtils\Interfaces;

use AppUtils\TypeFilter\StrictType;

/**
 * Interface for the {@see RuntimePropertizableTrait} trait.
 *
 * @package AppUtils
 * @subpackage Traits
 * @see RuntimePropertizableTrait
 */
interface RuntimePropertizableInterface
{
    /**
     * @param string $name
     * @param mixed|NULL $value
     * @return $this
     */
    public function setRuntimeProperty(string $name, $value) : self;
    public function hasRuntimeProperty(string $name) : bool;

    /**
     * @param string $name
     * @return mixed|NULL
     */
    public function getRuntimeProperty(string $name) : StrictType;
    public function getRuntimePropertyRaw(string $name);

    /**
     * Deletes the values of all runtime properties.
     * @return $this
     */
    public function clearRuntimeProperties() : self;
}
