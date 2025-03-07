<?php
/**
 * @package AppUtils
 * @subpackage ClassHelper
 */

declare(strict_types=1);

namespace AppUtils\ClassHelper\Repository;

use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\PathInfoInterface;
use AppUtils\FileHelper\PHPFile;
use AppUtils\FileHelper_Exception;
use AppUtils\FileHelper_PHPClassInfo_Class;
use AppUtils\Microtime;
use AppUtils\VariableInfo;
use Closure;
use SplFileInfo;
use function AppUtils\parseVariable;

/**
 * Utility class used to manage a unified collection of class repositories.
 *
 * It allows caching the results of the {@see ClassHelper::findClassesInFolder()}
 * method to solve performance issues when scanning many and/or large folders
 * for classes.
 *
 * ## Usages
 *
 * ### As direct findClassesInFolder() replacement
 *
 * 1. Create an instance of the class using the {@see self::create()} method.
 * 2. Use {@see self::findClassesInFolder()}.
 *
 * ### As an advanced class locations manager
 *
 * 1. Create an instance of the class using the {@see self::create()} method.
 * 2. Register cache locations using {@see self::registerClassLoader()}.
 * 3. Fetch them using {@see self::getByID()}.
 *
 * ## Saving changes
 *
 * By default, the cache file is written to disk when the script ends
 * using a shutdown handler. You can also manually call {@see self::writeCache()},
 * which will write the cache file only if changes were made.
 *
 * ## How it works
 *
 * All class repositories are stored in a single PHP cache file.
 * This file is generated and updated automatically.
 *
 * Like the Composer AutoLoader, this cache file makes use of PHP's
 * native Byte Cache to further improve performance as compared with
 * serialized or JSON files that need additional parsing overhead.
 *
 * @package AppUtils
 * @subpackage ClassHelper
 */
class ClassRepositoryManager
{
    /**
     * Used to identify the version of the cache file format.
     * If this changes, the cache will be invalidated.
     */
    public const SYSTEM_VERSION = 1;

    /**
     * @var array<string,class-string[]> Cache ID => Class name pairs.
     */
    private array $index = array();

    /**
     * @var array<string,ClassRepository> Cache ID => ClassRepository pairs.
     */
    private array $repositories = array();
    private PHPFile $indexFile;
    private bool $modified = false;

    /**
     * @var array<string,Closure> Cache ID => Loader callback pairs.
     */
    private array $idLoaders = array();

    /**
     * Creates a new instance for the target cache folder.
     *
     * @param string|PathInfoInterface|SplFileInfo $cacheFolder Folder in which to store the cache files.
     * @return ClassRepositoryManager
     */
    public static function create($cacheFolder): ClassRepositoryManager
    {
        return new ClassRepositoryManager(FolderInfo::factory($cacheFolder));
    }

    protected function __construct(FolderInfo $cacheFolder)
    {
        $this->indexFile = PHPFile::factory($cacheFolder->getPath() . '/class-repository-v'.self::SYSTEM_VERSION.'.php');

        $this->loadIndex();

        register_shutdown_function(Closure::fromCallable(array($this, 'writeCache')));
    }

    public function getCacheFolder() : FolderInfo
    {
        return $this->indexFile->getFolder();
    }

    private function loadIndex() : void
    {
        if($this->indexFile->exists()) {
            $this->index = include $this->indexFile->getPath();
        }
    }

    /**
     * Scans the target folder for classes and returns a repository instance
     * with the class list.
     *
     * @param FolderInfo $folder
     * @param bool $recursive Whether to search recursively in subfolders.
     * @param string|null $instanceOf Optional interface or class name to
     *                filter the results.
     * @param string|null $id Optional ID to identify this class cache and
     *                get it later using {@see self::getByID()}. If not provided,
     *                a unique ID is generated automatically to identify this
     *                combination of folder and options.
     * @return ClassRepository
     */
    public function findClassesInFolder(FolderInfo $folder, bool $recursive=false, ?string $instanceOf=null, ?string $id=null) : ClassRepository
    {
        if(empty($id)) {
            $id = md5('auto-generated-hash-'.$folder->getRealPath() . '-' . ConvertHelper::bool2string($recursive) . '-' . $instanceOf);
        }

        return $this->getByID($id) ?? $this->initializeCache($id, ClassHelper::findClassesInFolder($folder, $recursive, $instanceOf));
    }

    /**
     * Fetches an existing repository by its ID if it exists.
     *
     * @param string $id
     * @return class-string[]|null
     */
    public function getByID(string $id) : ?ClassRepository
    {
        if(isset($this->repositories[$id])) {
            return $this->repositories[$id];
        }

        if(isset($this->index[$id])) {
            $this->repositories[$id] = new ClassRepository($id, $this->index[$id]);
            return $this->repositories[$id];
        }

        if(isset($this->idLoaders[$id])) {
            return $this->autoLoad($id);
        }

        return null;
    }

    /**
     * Like {@see self::getByID()}, but throws an exception if the cache ID is not found.
     *
     * @param string $id
     * @return ClassRepository
     * @throws ClassRepositoryException
     */
    public function requireByID(string $id) : ClassRepository
    {
        $repository = $this->getByID($id);

        if($repository !== null) {
            return $repository;
        }

        throw new ClassRepositoryException(
            'Cache ID not found',
            sprintf(
                'The cache ID [%s] was not found in the class repository cache. '.PHP_EOL.
                'Use the method [%s] to check beforehand.',
                $id,
                VariableInfo::callback2string(array($this, 'idExists'))
            ),
            ClassRepositoryException::ERROR_REPOSITORY_NOT_FOUND
        );
    }

    public function getCacheFile() : PHPFile
    {
        return $this->indexFile;
    }

    /**
     * Registers a callback that is used to auto-initialize a
     * class cache. Whenever the cache is missing, this callback
     * will be called to generate the cache.
     *
     * Callback signature:
     *
     * ```php
     * function(ClassRepositoryManager $manager) : ClassRepository
     * ```
     *
     * @param string $id
     * @param Closure $callback
     * @return $this
     */
    public function registerClassLoader(string $id, Closure $callback) : self
    {
        $this->idLoaders[$id] = $callback;

        return $this;
    }

    /**
     * Removes a class loader callback by its ID if it exists.
     * Has no effect otherwise.
     *
     * @param string $repositoryID
     * @return $this
     */
    public function unregisterClassLoader(string $repositoryID) : self
    {
        if(isset($this->idLoaders[$repositoryID])) {
            unset($this->idLoaders[$repositoryID]);
        }

        return $this;
    }

    /**
     * Whether a class loader callback exists for the specified repository ID.
     * @param string $repositoryID
     * @return bool
     */
    public function hasClassLoader(string $repositoryID) : bool
    {
        return isset($this->idLoaders[$repositoryID]);
    }

    /**
     * Clears a specific class cache by ID if it exists.
     * Has no effect otherwise.
     *
     * @param string $id
     * @return $this
     */
    public function clearID(string $id) : self
    {
        if(isset($this->index[$id])) {
            unset($this->index[$id]);
            $this->modified = true;
        }

        if(isset($this->repositories[$id])) {
            unset($this->repositories[$id]);
        }

        if(isset($this->idLoaders[$id])) {
            unset($this->idLoaders[$id]);
        }

        return $this;
    }

    /**
     * Clears the class cache by deleting the cache file if it exists.
     *
     * @return $this
     * @throws ClassRepositoryException
     */
    public function clearCache() : self
    {
        try
        {
            $this->index = array();
            $this->repositories = array();
            $this->indexFile->delete();
        }
        catch (FileHelper_Exception $e)
        {
            throw new ClassRepositoryException(
                'Failed to clear cache',
                sprintf(
                    'Failed to delete the class repository cache file [%s].',
                    $this->indexFile->getPath()
                ),
                ClassRepositoryException::ERROR_CLEAR_CACHE_FAILED,
                $e
            );
        }

        return $this;
    }

    public function idExists(string $id) : bool
    {
        return isset($this->index[$id]) || isset($this->idLoaders[$id]);
    }

    /**
     * @param string $id ID to identify this class cache and get it later using {@see self::getByID()}.
     * @param FileHelper_PHPClassInfo_Class[] $classInfos List of classes to include in the cache.
     * @return ClassRepository The cached class repository.
     */
    public function initializeCache(string $id, array $classInfos) : ClassRepository
    {
        if(isset($this->index[$id])) {
            throw new ClassRepositoryException(
                'Cache ID already exists',
                sprintf(
                    'The cache ID [%s] already exists in the class repository cache. '.PHP_EOL.
                    'Use the method [%s] to fetch the existing cache, or use [%s] to check beforehand.',
                    $id,
                    VariableInfo::callback2string(array($this, 'getByID')),
                    VariableInfo::callback2string(array($this, 'idExists'))
                ),
                ClassRepositoryException::ERROR_REPOSITORY_EXISTS
            );
        }

        $this->modified = true;

        $this->index[$id] = array();
        foreach($classInfos as $classInfo) {
            $this->index[$id][] = $classInfo->getNameNS();
        }

        sort($this->index[$id]);

        return $this->getByID($id);
    }

    private function autoLoad(string $id) : ClassRepository
    {
        $result = $this->idLoaders[$id]($this);

        if($result instanceof ClassRepository) {
            return $result;
        }

        throw new ClassRepositoryException(
            'Invalid loader result',
            sprintf(
                'The loader callback for cache ID [%s] returned an invalid result. '.PHP_EOL.
                'Expected an instance of [%s], but got [%s].',
                $id,
                ClassRepository::class,
                parseVariable($result)->enableType()->toString()
            ),
            ClassRepositoryException::ERROR_LOADER_INVALID_RESULT
        );
    }

    public function writeCache() : self
    {
        if($this->modified) {
            $this->indexFile->putContents($this->renderCode());
            $this->modified = false;
        }

        return $this;
    }

    private function renderCode() : string
    {
        $vars = array(
            '{DATE_GENERATED}' => Microtime::createNow()->getISODate(true),
            '{REGISTRY}' => var_export($this->index, true)
        );

        return str_replace(
            array_keys($vars),
            array_values($vars),
            <<<'PHP'
<?php 
/**
 * Class registry cache file - do not edit.
 * Changes will be overwritten.
 *
 * @package AppUtils
 * @subpackage ClassHelper
 * @generated {DATE_GENERATED}
 * @see \AppUtils\ClassHelper\Repository\ClassRepositoryManager 
 */
 
return {REGISTRY};
PHP
        );
    }
}
