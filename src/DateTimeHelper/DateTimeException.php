<?php
/**
 * @package Application Utils
 * @subpackage DateTime Helper
 */

declare(strict_types=1);

namespace AppUtils\DateTimeHelper;

use AppUtils\BaseException;

/**
 * @package Application Utils
 * @subpackage DateTime Helper
 */
class DateTimeException extends BaseException
{
    public const int ERROR_OPERATION_DENIED_ON_INVALID_DAYTIME = 171801;
    public const int ERROR_INVALID_STRING_TIME_VALUE = 171802;
}
