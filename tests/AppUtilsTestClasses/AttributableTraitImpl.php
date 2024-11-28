<?php
/**
 * @package Application Utils
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace AppUtilsTestClasses;

use AppUtils\AttributeCollection;
use AppUtils\Interfaces\AttributableInterface;
use AppUtils\Traits\AttributableTrait;

/**
 * Used to test {@see AttributableTrait}.
 *
 * @package Application Utils
 * @subpackage UnitTests
 */
final class AttributableTraitImpl implements AttributableInterface
{
    use AttributableTrait;

    private AttributeCollection $attributes;

    public function __construct()
    {
        $this->attributes = AttributeCollection::create();
    }

    public function getAttributes(): AttributeCollection
    {
        return $this->attributes;
    }
}
