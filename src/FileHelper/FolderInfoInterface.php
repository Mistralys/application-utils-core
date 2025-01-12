<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\FileHelper_Exception;
use DirectoryIterator;

interface FolderInfoInterface extends PathInfoInterface
{
    /**
     * Creates the folder if it does not exist yet.
     * Has no effect if it already exists.
     *
     * @return $this
     */
    public function create() : self;

    public function getRelativeTo(FolderInfo $folder) : string;

    public function createFolderFinder() : FolderFinder;

    public function getIterator() : DirectoryIterator;

    /**
     * @return int The size of all files in the folder (recursive), in bytes.
     */
    public function getSize(bool $recursive=true): int;

    public function createSubFolder(string $name) : FolderInfo;

    public function saveFile(string $fileName, string $content='') : FileInfo;

    /**
     * Creates a new JSON file, or overwrites an existing file in the
     * folder, using the specified data.
     *
     * @param array<mixed> $data
     * @param string $fileName
     * @param bool $pretty
     * @return JSONFile
     * @throws FileHelper_Exception
     */
    public function saveJSONFile(array $data, string $fileName, bool $pretty=false) : JSONFile;

    /**
     * Helper method that uses the folder finder to
     * fetch all subfolders of this folder.
     *
     * See {@see self::createFolderFinder()}.
     *
     * @return FolderInfo[]
     */
    public function getSubFolders(bool $recursive=false) : array;

    /**
     * Fetches a file from this folder given the specified filename.
     *
     * > NOTE: This does not check if the file actually exists.
     * > Use {@see FileInfo::exists()} to check if it does.
     *
     * @param string $nameOrRelativePath A filename or relative path from the folder's root.
     * @return FileInfo
     * @throws FileHelper_Exception
     */
    public function getSubFile(string $nameOrRelativePath) : FileInfo;

    /**
     * Checks if the target folder exists and is empty.
     *
     * > NOTE: A folder that does not exist is considered empty.
     *
     * @return bool
     */
    public function isEmpty() : bool;

    /**
     * Creates a file finder instance for this folder,
     * to find specific subfiles.
     *
     * @return FileFinder
     */
    public function createFileFinder() : FileFinder;

    /**
     * Gets all files in the folder (non-recursive),
     * sorted by file name.
     *
     * Use {@see self::createFileFinder()} for more advanced
     * file finding options.
     *
     * @return FileInfo[]
     */
    public function getSubFiles() : array;
}
