<?php

declare(strict_types=1);

namespace AppUtils\Interfaces;

use Stringable;

interface StringableInterface extends Stringable
{
    /**
     * Converts the object to a string.
     *
     * @return string
     */
    public function __toString();
}
