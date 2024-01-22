<?php
/**
 * @package Application Utils
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace AppUtilsTestClasses;

use AppUtils\Interfaces\RuntimePropertizableInterface;
use AppUtils\Traits\RuntimePropertizableTrait;

/**
 * Stub implementation of the runtime propertizable interface.
 *
 * @package Application Utils
 * @subpackage UnitTests
 */
class RuntimePropertizableTraitImpl implements RuntimePropertizableInterface
{
    use RuntimePropertizableTrait;
}
