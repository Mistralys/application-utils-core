<?php
/**
 * @package FileHelper
 * @subpackage FileInfo
 */

declare(strict_types=1);

namespace AppUtils\FileHelper\FileInfo;

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\JSONFile;
use AppUtils\FileHelper\PHPFile;

/**
 * Registry of classes that handle specific file extensions.
 * This includes the vanilla classes like {@see JSONFile} or
 * {@see PHPFile} for example.
 *
 * It allows registering and overriding classes for any
 * file extension.
 *
 * @package FileHelper
 * @subpackage FileInfo
 */
class ExtensionClassRegistry
{
    /**
     * @var array<string,class-string<FileInfo>>|null
     */
    private static ?array $extensionClasses = null;

    /**
     * @param string $extension
     * @return class-string<FileInfo>
     */
    public static function getExtensionClass(string $extension) : string
    {
        $classes = self::getExtensionClasses();

        if(isset($classes[$extension])) {
            return $classes[$extension];
        }

        return FileInfo::class;
    }

    /**
     * @return array<string, class-string<FileInfo>>
     */
    public static function getExtensionClasses() : array
    {
        if(!isset(self::$extensionClasses)) {
            self::$extensionClasses = FileInfo::EXTENSION_CLASSES;
        }

        return self::$extensionClasses;
    }

    /**
     * Allows registering a custom class to handle a specific file extension,
     * or to override one of the vanilla classes like {@see JSONFile}.
     * Whenever {@see FileInfo::createInstance()} is called, the custom class
     * will be used for any files with the specified extension.
     *
     * > NOTE: When overriding a vanilla class, the custom class must be an
     * > instance of the vanilla class.
     *
     * @param string $extension
     * @param class-string<FileInfo> $class Must be a class that extends {@see FileInfo}.
     * @return void
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws FileInfoException {@see FileInfoException::ERROR_INVALID_OVERRIDE_CLASS}
     */
    public static function registerExtensionClass(string $extension, string $class) : void
    {
        ClassHelper::requireClassExists($class);
        ClassHelper::requireClassInstanceOf($class, FileInfo::class);

        $vanillaClass = FileInfo::EXTENSION_CLASSES[$extension] ?? null;

        // If the specified extension is part of the vanilla file type
        // classes, the override class must be an instance of the vanilla
        // class.
        if(
            $vanillaClass !== null
            &&
            !ClassHelper::isClassInstanceOf($class, $vanillaClass)
        ) {
            throw new FileInfoException(
                'Invalid file type override class',
                sprintf(
                    'The class [%s] is not an instance of [%s] for the file extension [%s].',
                    $class,
                    $vanillaClass,
                    $extension
                ),
                FileInfoException::ERROR_INVALID_OVERRIDE_CLASS
            );
        }

        $classes = self::getExtensionClasses();

        $classes[$extension] = $class;

        self::$extensionClasses = $classes;
    }

    /**
     * Resets all custom extension classes, reverting to the vanilla classes.
     *
     * @return void
     */
    public static function resetExtensions() : void
    {
        self::$extensionClasses = null;
    }
}
