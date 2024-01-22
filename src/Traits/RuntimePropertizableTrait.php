<?php
/**
 * @package AppUtils
 * @subpackage Traits
 */

declare(strict_types=1);

namespace AppUtils\Traits;

use AppUtils\Interfaces\RuntimePropertizableInterface;
use AppUtils\TypeFilter\StrictType;

/**
 * This trait is used to add properties to an object that
 * are used only at runtime, and are not persisted.
 * The freeform key => value pairs are stored in memory.
 *
 * Use case is to attach additional data to an object
 * that can be accessed later, without having to extend
 * the object's class.
 *
 * Thanks to the use of the {@see StrictType} class for
 * the property values, typing is guaranteed.
 *
 * @package AppUtils
 * @subpackage Traits
 * @see RuntimePropertizableInterface
 * @see \AppUtilsTests\Traits\RuntimePropertizableTests
 */
trait RuntimePropertizableTrait
{
    /**
     * @var array<string, mixed>
     */
    private array $runtimeProperties = array();

    /**
     * @param string $name
     * @param mixed|NULL $value
     * @return $this
     */
    public function setRuntimeProperty(string $name, $value) : self
    {
        $this->runtimeProperties[$name] = $value;
        return $this;
    }

    /**
     * Checks if the runtime property exists and is not NULL.
     * @param string $name
     * @return bool
     */
    public function hasRuntimeProperty(string $name) : bool
    {
        return isset($this->runtimeProperties[$name]);
    }

    /**
     * @param string $name
     * @return StrictType
     */
    public function getRuntimeProperty(string $name) : StrictType
    {
        return StrictType::createStrict($this->runtimeProperties[$name] ?? null);
    }

    /**
     * Fetches the raw property value if it exists, instead
     * of through a type filter.
     *
     * @param string $name
     * @return mixed|null
     */
    public function getRuntimePropertyRaw(string $name)
    {
        return $this->runtimeProperties[$name] ?? null;
    }

    /**
     * @return $this
     */
    public function clearRuntimeProperties() : self
    {
        $this->runtimeProperties = array();
        return $this;
    }
}
