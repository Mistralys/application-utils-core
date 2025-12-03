<?php

declare(strict_types=1);

namespace AppUtils\Traits\OptionableTrait;

use AppUtils\BaseException;

class OptionableException extends BaseException
{
    public const int ERROR_INVALID_OPTION_VALUE = 188201;
}
