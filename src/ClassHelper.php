<?php
/**
 * @package Application Utils
 * @subpackage ClassFinder
 * @see \AppUtils\ClassHelper
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\ClassHelper\ClassLoaderNotFoundException;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ClassHelper\Repository\ClassRepository;
use AppUtils\ClassHelper\Repository\ClassRepositoryException;
use AppUtils\ClassHelper\Repository\ClassRepositoryManager;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\PathInfoInterface;
use Composer\Autoload\ClassLoader;
use SplFileInfo;
use Throwable;

/**
 * Helper class to simplify working with dynamic class loading,
 * in a static analysis-tool-friendly way. PHPStan and co will
 * recognize the correct class types given class strings.
 *
 * ## Setup
 *
 * Some features require a cache folder to be set using
 * {@see self::setCacheFolder()}. These methods say so
 * in their comments.
 *
 * @package Application Utils
 * @subpackage ClassFinder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ClassHelper
{
    private static ?ClassLoader $classLoader = null;

    public const ERROR_CANNOT_RESOLVE_CLASS_NAME = 111001;
    public const ERROR_THROWABLE_GIVEN_AS_OBJECT = 111002;
    public const ERROR_CACHE_FOLDER_NOT_SET = 111003;

    /**
     * Attempts to detect the name of a class, switching between
     * the older class naming scheme with underscores (Long_Class_Name)
     * and namespaces.
     *
     * @param string $legacyName
     * @param string $nsPrefix Optional namespace prefix, if the namespace contains
     *                         the vendor name, for example (Vendor\PackageName\Folder\Class).
     * @return string|null The detected class name, or NULL otherwise.
     */
    public static function resolveClassName(string $legacyName, string $nsPrefix='') : ?string
    {
        $names = array(
            str_replace('\\', '_', $legacyName),
            str_replace('_', '\\', $legacyName),
            $nsPrefix.'\\'.str_replace('_', '\\', $legacyName)
        );

        foreach($names as $name) {
            if (class_exists($name)) {
                return ltrim($name, '\\');
            }
        }

        return null;
    }

    /**
     * Like {@see ClassHelper::resolveClassName()}, but throws an exception
     * if the class cannot be found.
     *
     * @param string $legacyName
     * @param string $nsPrefix Optional namespace prefix, if the namespace contains
     *                         the vendor name, for example (Vendor\PackageName\Folder\Class).
     * @return string
     * @throws ClassNotExistsException
     */
    public static function requireResolvedClass(string $legacyName, string $nsPrefix='') : string
    {
        $class = self::resolveClassName($legacyName, $nsPrefix);

        if($class !== null)
        {
            return $class;
        }

        throw new ClassNotExistsException(
            $legacyName,
            self::ERROR_CANNOT_RESOLVE_CLASS_NAME
        );
    }

    /**
     * Throws an exception if the target class cannot be found.
     *
     * @param class-string $className
     * @return void
     * @throws ClassNotExistsException
     */
    public static function requireClassExists(string $className) : void
    {
        if(class_exists($className) || interface_exists($className) || trait_exists($className))
        {
            return;
        }

        throw new ClassNotExistsException($className);
    }

    /**
     * Requires the target class name to exist, and extend
     * or implement the specified class/interface. If it does
     * not, an exception is thrown.
     *
     * @param class-string $targetClass
     * @param class-string $expectedClass
     * @return void
     *
     * @throws ClassNotImplementsException
     * @throws ClassNotExistsException
     */
    public static function requireClassInstanceOf(string $targetClass, string $expectedClass) : void
    {
        if(self::isClassInstanceOf($targetClass, $expectedClass)) {
            return;
        }

        throw new ClassNotImplementsException($expectedClass, $targetClass);
    }

    /**
     * Checks whether the target class is an instance of the specified
     * class or interface.
     *
     * @param class-string $targetClass
     * @param class-string $instanceClass
     * @return bool
     * @throws ClassNotExistsException
     */
    public static function isClassInstanceOf(string $targetClass, string $instanceClass) : bool
    {
        self::requireClassExists($targetClass);
        self::requireClassExists($instanceClass);

        return is_a($targetClass, $instanceClass, true);
    }

    /**
     * If the target object is not an instance of the target class
     * or interface, throws an exception.
     *
     * > NOTE: If an exception is passed as an object, a class helper
     * > exception is thrown with the error code {@see ClassHelper::ERROR_THROWABLE_GIVEN_AS_OBJECT},
     * > and the original exception as previous exception.
     *
     * @template ClassInstanceType
     * @param class-string<ClassInstanceType> $class
     * @param object $object
     * @param int $errorCode
     * @return ClassInstanceType
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    public static function requireObjectInstanceOf(string $class, object $object, int $errorCode=0)
    {
        if($object instanceof Throwable)
        {
            throw new ClassNotExistsException(
                $class,
                self::ERROR_THROWABLE_GIVEN_AS_OBJECT,
                $object
            );
        }

        if(!class_exists($class) && !interface_exists($class) && !trait_exists($class))
        {
            throw new ClassNotExistsException($class, $errorCode);
        }

        if(is_a($object, $class, true))
        {
            return $object;
        }

        throw new ClassNotImplementsException($class, $object, $errorCode);
    }

    /**
     * Retrieves an instance of the Composer class loader for
     * the current project. This assumes the usual structure
     * with this library being stored in the `vendor` folder.
     *
     * > NOTE: Also works when working on a local copy of the
     * > Git package.
     *
     * @return ClassLoader
     * @throws ClassLoaderNotFoundException
     */
    public static function getClassLoader() : ClassLoader
    {
        if(isset(self::$classLoader)) {
            return self::$classLoader;
        }

        // Paths are either the folder structure when the
        // package has been installed as a dependency via
        // composer, or a local installation of the git package.
        $paths = array(
            __DIR__.'/../../../autoload.php',
            __DIR__.'/../vendor/autoload.php'
        );

        $autoloadFile = null;

        foreach($paths as $path)
        {
            if(file_exists($path)) {
                $autoloadFile = $path;
            }
        }

        if($autoloadFile === null) {
            throw new ClassLoaderNotFoundException($paths);
        }

        $loader = require $autoloadFile;

        if (!$loader instanceof ClassLoader)
        {
            throw new ClassLoaderNotFoundException($paths);
        }

        self::$classLoader = $loader;

        return self::$classLoader;
    }

    /**
     * Gets the last part in a class name, e.g.:
     *
     * - `Class_Name_With_Underscores` -> `Underscores`
     * - `Class\With\Namespace` -> `Namespace`
     *
     * @param class-string|string|object $subject
     * @return string
     */
    public static function getClassTypeName($subject) : string
    {
        $parts = self::splitClass($subject);
        return array_pop($parts);
    }

    /**
     * Retrieves the namespace part of a class name, if any.
     *
     * @param class-string|string|object $subject
     * @return string
     */
    public static function getClassNamespace($subject) : string
    {
        $parts = self::splitClass($subject);
        array_pop($parts);

        return ltrim(implode('\\', $parts), '\\');
    }

    /**
     * @param class-string|string|object $subject
     * @return string[]
     */
    private static function splitClass($subject) : array
    {
        if(is_object($subject)) {
            $class = get_class($subject);
        } else {
            $class = $subject;
        }

        $class = str_replace('\\', '_', $class);

        return explode('_', $class);
    }

    /**
     * Builds a class name within the same namespace or name with underscores
     * as the reference class, using the given class ID.
     *
     * This is handy to dynamically load classes from a folder, for example.
     * Given one of the classes as example, it can build the other class names
     * by using their base file names.
     *
     * Example: {@see \AppUtilsTests\ClassHelper\ClassHelperTests::test_example_loadClassesFromFolder()}.
     *
     * @param string $classID
     * @param class-string $referenceClass
     * @return class-string
     * @throws ClassNotExistsException
     */
    public static function resolveClassByReference(string $classID, string $referenceClass) : string
    {
        $referenceID = self::getClassTypeName($referenceClass);

        // Using explodeTrim removes all empty parts, including the
        // spot where the reference class ID was.
        $parts = ConvertHelper::explodeTrim($referenceID, $referenceClass);

        // Rebuild the string and add the new class ID.
        $class = implode($referenceID, $parts).$classID;

        if(class_exists($class) || interface_exists($class) || trait_exists($class)) {
            return $class;
        }

        throw new ClassNotExistsException($class);
    }

    /**
     * Generates a list of class names from the target folder, using
     * the given class name as reference to build the class names from,
     * inferred from the file names.
     *
     * > NOTE: This is very fast, but relies on all classes in the folder
     * > to use the same namespace and naming convention.
     * > Consider the alternative {@see self::findClassesInRepository()}
     * > if this is not the case.
     *
     * @param FolderInfo $folder
     * @param class-string $classReference
     * @return class-string[]
     *
     * @throws ClassNotExistsException
     * @throws FileHelper_Exception
     */
    public static function getClassesInFolder(FolderInfo $folder, string $classReference) : array
    {
        $fileIDs = FileHelper::createFileFinder($folder)
            ->getFiles()
            ->PHPClassNames();

        $classes = array();
        foreach($fileIDs as $fileID) {
            $classes[] = self::resolveClassByReference($fileID, $classReference);
        }

        sort($classes);

        return $classes;
    }

    /**
     * Goes through the target folder and analyzes the source code in
     * all PHP files to detect any PHP classes they may contain.
     * It will return an array of {@see FileHelper_PHPClassInfo_Class}
     * objects.
     *
     * > NOTE: This method is slow, as it looks into each of the PHP files' sourcecode.
     * > Prefer the cached class repository manager, which solves this issue.
     * > See {@see self::findClassesInRepository()}.
     *
     * It is recommended to cache the results if this is done regularly.
     *
     * @param FolderInfo $folder
     * @param bool $recursive Whether to recursively go through subfolders.
     * @param class-string|null $instanceOf Is set, only classes that are instances
     *        of the specified class/interface will be returned.
     * @return FileHelper_PHPClassInfo_Class[]
     * @throws ClassNotExistsException
     * @throws FileHelper_Exception
     */
    public static function findClassesInFolder(FolderInfo $folder, bool $recursive=false, ?string $instanceOf=null) : array
    {
        $phpFiles = FileHelper::createFileFinder($folder)
            ->makeRecursive($recursive)
            ->getFiles()
            ->typePHP();

        $result = array();

        foreach($phpFiles as $file) {
            array_push($result, ...$file->findClasses()->getClasses());
        }

        if($instanceOf !== null) {
            return self::filterByInstanceOf($result, $instanceOf);
        }

        return $result;
    }

    /**
     * Like {@see self::findClassesInFolder()}, but uses
     * a cached variant that vastly improves performance.
     *
     * **IMPORTANT**: Requires the cache folder to be set
     * using {@see self::setCacheFolder()}.
     *
     * > NOTE: See the {@see ClassRepositoryManager} for
     * > more information and additional features.
     *
     * @requiresCacheFolder
     *
     * @param FolderInfo $folder
     * @param bool $recursive
     * @param string|null $instanceOf Filter results by class/interface.
     * @return ClassRepository Use {@see ClassRepository::getClasses()} to retrieve the classes.
     *
     * @throws ClassRepositoryException
     */
    public static function findClassesInRepository(FolderInfo $folder, bool $recursive=false, ?string $instanceOf=null) : ClassRepository
    {
        return self::getRepositoryManager()->findClassesInFolder($folder, $recursive, $instanceOf);
    }

    /**
     * @param FileHelper_PHPClassInfo_Class[] $classes
     * @param class-string $instanceOf
     * @return FileHelper_PHPClassInfo_Class[]
     * @throws ClassNotExistsException
     */
    private static function filterByInstanceOf(array $classes, string $instanceOf) : array
    {
        $filtered = array();

        foreach($classes as $class) {
            if(self::isClassInstanceOf($class->getNameNS(), $instanceOf)) {
                $filtered[] = $class;
            }
        }

        return $filtered;
    }

    private static ?ClassRepositoryManager $classRepository = null;

    /**
     * @param string|PathInfoInterface|SplFileInfo $folder
     */
    public static function setCacheFolder($folder) : void
    {
        self::$classRepository = ClassRepositoryManager::create($folder);
    }

    public static function getRepositoryManager() : ClassRepositoryManager
    {
        if(isset(self::$classRepository)) {
            return self::$classRepository;
        }

        throw new ClassRepositoryException(
            'The cache folder has not been set.',
            sprintf(
                'Call %s first.',
                ConvertHelper::callback2string(array(self::class, 'setCacheFolder'))
            ),
            self::ERROR_CACHE_FOLDER_NOT_SET
        );
    }
}
