<?php

declare(strict_types=1);

namespace AppUtils\URLInfo;

use AppUtils\BaseException;

/**
 * @package Application Utils
 * @subpackage URLInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class URLException extends BaseException
{
    public const int ERROR_EMPTY_URL = 187201;
}
