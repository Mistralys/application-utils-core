<?php
/**
 * @package Application Utils
 * @subpackage Traits
 * @see \AppUtils\Interfaces\ClassableInterface
 */

declare(strict_types=1);

namespace AppUtils\Interfaces;

use AppUtils\Traits\ClassableTrait;

/**
 * Interface for classes that use {@see ClassableTrait}.
 * The trait itself fulfills most of the interface, but
 * it is used to guarantee internal type checks will work,
 * as well as ensure the abstract methods are implemented.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see ClassableTrait
 */
interface ClassableInterface
{
    /**
     * @return bool
     */
    public function hasClasses() : bool;

    /**
     * @param string $name
     * @return $this
     */
    public function addClass($name); // no type hints on purpose for HTML_QuickForm compatibility

    /**
     * @param string[] $names
     * @return $this
     */
    public function addClasses(array $names) : self;

    /**
     * @param string $name
     * @return bool
     */
    public function hasClass(string $name) : bool;

    /**
     * @param string $name
     * @return $this
     */
    public function removeClass(string $name) : self;

    /**
     * @return string[]
     */
    public function getClasses() : array;

    /**
     * @return string
     */
    public function classesToString() : string;

    /**
     * @return string
     */
    public function classesToAttribute() : string;
}
