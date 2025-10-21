<?php

declare(strict_types=1);

namespace AppUtils\ArrayDataCollection;

use AppUtils\BaseException;

class ArrayDataCollectionException extends BaseException
{
    public const CODE_MISSING_MICROTIME = 185001;
    public const CODE_MISSING_DATETIME = 185002;
}
