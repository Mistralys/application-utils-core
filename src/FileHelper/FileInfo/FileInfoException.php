<?php
/**
 * @package FileHelper
 * @subpackage FileInfo
 */

declare(strict_types=1);

namespace AppUtils\FileHelper\FileInfo;

use AppUtils\FileHelper_Exception;

/**
 * @package FileHelper
 * @subpackage FileInfo
 */
class FileInfoException extends FileHelper_Exception
{
    public const int ERROR_INVALID_OVERRIDE_CLASS = 170401;
    public const int ERROR_CANNOT_RENAME_TARGET_EXISTS = 170402;
    public const int ERROR_FAILED_TO_RENAME_PATH = 170403;
    public const int ERROR_CANNOT_RENAME_WITH_PATH = 170404;
}
