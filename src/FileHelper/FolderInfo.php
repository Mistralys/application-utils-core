<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\BaseException;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use DirectoryIterator;
use FilesystemIterator;
use SplFileInfo;

/**
 * @method FolderInfo requireReadable(?int $errorCode = null)
 * @method FolderInfo requireExists(?int $errorCode = null)
 * @method FolderInfo requireWritable(?int $errorCode = null)
 */
class FolderInfo extends AbstractPathInfo implements FolderInfoInterface
{
    /**
     * @var array<string,FolderInfo>
     */
    private static array $infoCache = array();

    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return FolderInfo
     * @throws FileHelper_Exception
     */
    public static function factory($path) : FolderInfo
    {
        $pathString = AbstractPathInfo::type2string($path);

        if(empty($path)) {
            throw new FileHelper_Exception(
                'Invalid',
                '',
                FileHelper::ERROR_PATH_INVALID
            );
        }

        if($path instanceof FileInfo || FileInfo::is_file($pathString))
        {
            throw new FileHelper_Exception(
                'Cannot use a file',
                sprintf(
                    'The path [%s] seems to be a file, not a folder.',
                    $pathString
                ),
                FileHelper::ERROR_PATH_IS_NOT_A_FOLDER
            );
        }

        if(!isset(self::$infoCache[$pathString]))
        {
            self::$infoCache[$pathString] = new FolderInfo($pathString);
        }

        return self::$infoCache[$pathString];
    }

    /**
     * Clears the folder cache that keeps track of any folders
     * created via {@see FolderInfo::factory()} for performance
     * reasons.
     *
     * @return void
     */
    public static function clearCache() : void
    {
        self::$infoCache = array();
    }

    /**
     * Detects if the target path is a folder.
     *
     * NOTE: If the folder does not exist on disk, this will
     * return true under the following conditions:
     *
     * - The path does not contain a file extension
     * - The path ends with a slash
     *
     * @param string $path
     * @return bool
     */
    public static function is_dir(string $path) : bool
    {
        $path = trim($path);
        $test = trim($path, '/\\');

        if($path === '' || $test === '.' || $test === '..')
        {
            return false;
        }

        return is_dir($path) || AbstractPathInfo::pathHasEndingSlash($path);
    }

    /**
     * @return $this
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_CANNOT_DELETE_FOLDER
     */
    public function delete() : FolderInfo
    {
        if(!$this->exists())
        {
            return $this;
        }

        if(rmdir($this->path))
        {
            return $this;
        }

        throw new FileHelper_Exception(
            sprintf(
                'Cannot delete folder [%s].',
                $this->getName()
            ),
            sprintf(
                'The folder could not be deleted at path: [%s]',
                $this->getPath()
            ),
            FileHelper::ERROR_CANNOT_DELETE_FOLDER
        );
    }

    /**
     * @inheritDoc
     * @return $this
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_CANNOT_CREATE_FOLDER
     */
    public function create() : self
    {
        if(is_dir($this->path) || mkdir($this->path, 0777, true) || is_dir($this->path))
        {
            return $this;
        }

        throw new FileHelper_Exception(
            sprintf(
                'Could not create target folder [%s].',
                $this->getName()
            ),
            sprintf(
                'Tried to create the folder in path [%s].',
                $this->getPath()
            ),
            FileHelper::ERROR_CANNOT_CREATE_FOLDER
        );
    }

    public function getRelativeTo(FolderInfo $folder) : string
    {
        return FileHelper::relativizePath($this->getPath(), $folder->getPath());
    }

    public function createFolderFinder() : FolderFinder
    {
        return new FolderFinder($this);
    }

    public function getIterator() : DirectoryIterator
    {
        $this->requireExists()->requireIsFolder();

        return new DirectoryIterator($this->getPath());
    }

    public function getExtension(bool $lowercase = true) : string
    {
        return '';
    }

    /**
     * @return int The size of all files in the folder (recursive), in bytes.
     */
    public function getSize(bool $recursive=true): int
    {
        return $this->walkSize($this->getPath(), $recursive);
    }

    private function walkSize(string $path, bool $recursive) : int
    {
        $this->requireExists();

        $size = 0;
        $list = glob(rtrim($path, '/').'/*', GLOB_NOSORT);
        if($list === false) {
            return 0;
        }

        foreach ($list as $item)
        {
            if(is_file($item)) {
                $bytes = filesize($item);
                if($bytes !== false) {
                    $size += $bytes;
                }
            } else if($recursive) {
                $size += $this->walkSize($item, $recursive);
            }
        }

        return $size;
    }

    public function getFolderPath() : string
    {
        return $this->getPath();
    }

    public function createSubFolder(string $name) : FolderInfo
    {
        return FileHelper::createFolder($this->getPath().'/'.$name);
    }

    public function saveFile(string $fileName, string $content='') : FileInfo
    {
        return FileHelper::saveFile($this.'/'.$fileName, $content);
    }

    public function saveJSONFile(array $data, string $fileName, bool $pretty=false) : JSONFile
    {
        return FileHelper::saveAsJSON($data, $this.'/'.$fileName, $pretty);
    }

    public function getSubFolders(bool $recursive=false) : array
    {
        return $this->createFolderFinder()
            ->makeRecursive($recursive)
            ->getFolderInfos();
    }

    public function getSubFile(string $nameOrRelativePath) : FileInfo
    {
        return FileInfo::factory($this->getPath().'/'.$nameOrRelativePath);
    }

    public function isEmpty() : bool
    {
        if(!$this->exists()) {
            return true;
        }

        $iterator = new FilesystemIterator($this->getPath());
        return !$iterator->valid();
    }

    /**
     * @inheritDoc
     * @throws FileHelper_Exception
     */
    public function createFileFinder() : FileFinder
    {
        return FileHelper::createFileFinder($this->getPath());
    }

    /**
     * @inheritDoc
     * @throws FileHelper_Exception
     * @throws BaseException
     */
    public function getSubFiles() : array
    {
        $files = array();

        foreach($this->getIterator() as $item) {
            if($item->isFile()) {
                $files[] = FileInfo::factory($item);
            }
        }

        usort($files, function (FileInfo $a, FileInfo $b) : int {
            return strnatcasecmp($a->getName(), $b->getName());
        });

        return $files;
    }

    /**
     * Gets the parent folder of this folder.
     *
     * **This does not check if the parent folder exists.**
     *
     * @return FolderInfo
     */
    public function getParentFolder() : FolderInfo
    {
        $path = $this->getPath();
        $folder = rtrim(dirname(rtrim(FileHelper::resolvePathDots($path), '/')), '/');

        if(substr($path, -1) === '/') {
            $folder .= '/';
        }

        return FolderInfo::factory($folder);
    }
}
