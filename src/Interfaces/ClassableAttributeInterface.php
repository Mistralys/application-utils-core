<?php
/**
 * File containing the interface {@see \AppUtils\Interfaces\ClassableAttributeInterface}.
 *
 * @package Application Utils
 * @subpackage Traits
 * @see \AppUtils\Interfaces\ClassableAttributeInterface
 */

declare(strict_types=1);

namespace AppUtils\Interfaces;

use AppUtils\AttributeCollection;
use AppUtils\Traits\ClassableAttributeTrait;

/**
 * Interface for objects that implement the classable
 * interface using the `class` attribute of an
 * {@see AttributeCollection} instance.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see ClassableAttributeTrait
 */
interface ClassableAttributeInterface extends ClassableInterface
{
    public function getAttributes() : AttributeCollection;
}
