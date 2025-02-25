<?php
/**
 * @package AppUtils
 * @subpackage ClassHelper
 */

declare(strict_types=1);

namespace AppUtils\ClassHelper\Repository;

use AppUtils\ClassHelper\BaseClassHelperException;

/**
 * @package AppUtils
 * @subpackage ClassHelper
 */
class ClassRepositoryException extends BaseClassHelperException
{
    public const ERROR_REPOSITORY_EXISTS = 173501;
    public const ERROR_CLEAR_CACHE_FAILED = 173502;
    public const ERROR_LOADER_INVALID_RESULT = 173503;
    public const ERROR_REPOSITORY_NOT_FOUND = 173504;
}
