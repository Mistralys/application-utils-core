<?php

declare(strict_types=1);

namespace AppUtilsTestClasses\FileHelper;

use AppUtils\BaseException;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\PathInfoInterface;
use AppUtils\FileHelper_Exception;
use SplFileInfo;

final class MockFooFileInfo extends FileInfo
{
    public const EXTENSION = 'foo';

    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return MockFooFileInfo
     *
     * @throws BaseClassHelperException
     * @throws FileHelper_Exception
     * @throws BaseException
     */
    public static function factory($path) : MockFooFileInfo
    {
        return ClassHelper::requireObjectInstanceOf(
            self::class,
            self::createInstance($path)
        );
    }
}
