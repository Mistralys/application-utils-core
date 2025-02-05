<?php
/**
 * @package Application Utils
 * @subpackage Traits
 */

declare(strict_types=1);

namespace AppUtils\Traits;

/**
 * Interface for classes that use the trait {@see SimpleErrorStateTrait}.
 *
 * @package Application Utils
 * @subpackage Traits
 * @see SimpleErrorStateTrait
 */
interface SimpleErrorStateInterface
{
    public function isValid() : bool;
    public function getErrorMessage() : ?string;
    public function getErrorCode() : ?int;
}
