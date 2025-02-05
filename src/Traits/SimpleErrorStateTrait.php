<?php
/**
 * @package Application Utils
 * @subpackage Traits
 */

declare(strict_types=1);

namespace AppUtils\Traits;

/**
 * Trait used to store a simple error state,
 * for a single error message and code.
 *
 * ## Usage
 *
 * 1. Implement the interface {@see SimpleErrorStateInterface} in your class.
 * 2. Use the trait in your class.
 * 3. Use {@see self::setError()} to store an error message and code.
 * 4. Use {@see self::isValid()} to check the result.
 *
 * @package Application Utils
 * @subpackage Traits
 * @see SimpleErrorStateInterface
 */
trait SimpleErrorStateTrait
{
    private ?string $errorMessage = null;
    private ?int $errorCode = null;

    public function isValid() : bool
    {
        return $this->errorMessage === null;
    }

    public function getErrorMessage() : ?string
    {
        return $this->errorMessage;
    }

    public function getErrorCode() : ?int
    {
        return $this->errorCode;
    }

    private function setError(string $message, int $code) : void
    {
        $this->errorMessage = $message;
        $this->errorCode = $code;
    }
}
