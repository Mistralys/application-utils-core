<?php
/**
 * File containing the class {@see \AppUtils\FileHelper\FileInfo}.
 *
 * @package FileHelper
 * @subpackage FileInfo
 * @see \AppUtils\FileHelper\FileInfo
 */

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\BaseException;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_EOL;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo\ExtensionClassRegistry;
use AppUtils\FileHelper\FileInfo\FileSender;
use AppUtils\FileHelper\FileInfo\LineReader;
use AppUtils\FileHelper_Exception;
use AppUtils\FileHelper_MimeTypes;
use SplFileInfo;
use function AppUtils\parseVariable;

/**
 * Specialized class used to access information on a file path,
 * and do file-related operations: reading contents, deleting
 * or copying and the like.
 *
 * Create an instance with {@see FileInfo::factory()}.
 *
 * Some specialized file type classes exist:
 *
 * - {@see JSONFile}
 * - {@see SerializedFile}
 * - {@see PHPFile}
 *
 * These each have their own factory method, e.g. {@see JSONFile::factory()}.
 *
 * > NOTE: Additional classes can be registered via the
 * > {@see ExtensionClassRegistry} class.
 *
 * @package FileHelper
 * @subpackage FileInfo
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class FileInfo extends AbstractPathInfo implements FileInfoInterface
{
    public const ERROR_INVALID_INSTANCE_CREATED = 115601;

    /**
     * @var array<string,FileInfo>
     */
    protected static array $infoCache = array();

    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return FileInfo|JSONFile|PHPFile|SerializedFile Will return the specialized info class for known types.
     * @throws FileHelper_Exception
     * @throws BaseException
     */
    public static function factory($path) : FileInfo
    {
        return self::createInstance($path);
    }

    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return FileInfo|JSONFile|PHPFile|SerializedFile Will return the specialized info class for known types.
     *
     * @throws FileHelper_Exception
     * @throws BaseException
     */
    protected static function createInstance($path) : FileInfo
    {
        $pathString = AbstractPathInfo::type2string($path);
        $endingChar = $pathString[strlen($pathString) - 1];

        if(empty($path)) {
            throw new FileHelper_Exception(
                'Invalid',
                '',
                FileHelper::ERROR_PATH_INVALID
            );
        }

        if($path instanceof FolderInfo || $endingChar === '/' || $endingChar === '\\')
        {
            throw new FileHelper_Exception(
                'Cannot use a folder as a file',
                sprintf(
                    'This looks like a folder path: [%s].',
                    $pathString
                ),
                FileHelper::ERROR_PATH_IS_NOT_A_FILE
            );
        }

        $class = ExtensionClassRegistry::getExtensionClass(FileHelper::getExtension($pathString));

        $key = $pathString.';'.$class;

        if(isset(self::$infoCache[$key])) {
            return self::$infoCache[$key];
        }

        $instance = ClassHelper::requireObjectInstanceOf(
            FileInfo::class,
            new $class($pathString)
        );

        self::$infoCache[$key] = $instance;

        return $instance;
    }

    /**
     * List of file extensions and dedicated file classes
     * that can handle them.
     *
     * @var array<string,class-string>
     */
    public const EXTENSION_CLASSES = array(
        JSONFile::EXTENSION => JSONFile::class,
        PHPFile::EXTENSION => PHPFile::class,
        SerializedFile::EXTENSION => SerializedFile::class,
    );

    /**
     * Clears the file cache that keeps track of any files
     * created via {@see FileInfo::factory()} for performance
     * reasons.
     *
     * @return void
     */
    public static function clearCache() : void
    {
        self::$infoCache = array();
    }

    public static function is_file(string $path) : bool
    {
        $path = trim($path);

        if(empty($path) || FolderInfo::is_dir($path))
        {
            return false;
        }

        return is_file($path) || pathinfo($path, PATHINFO_EXTENSION) !== '';
    }

    public function removeExtension(bool $keepPath=false) : string
    {
        if(!$keepPath)
        {
            return (string)pathinfo($this->getName(), PATHINFO_FILENAME);
        }

        $parts = explode('/', $this->path);

        $file = pathinfo(array_pop($parts), PATHINFO_FILENAME);

        $parts[] = $file;

        return implode('/', $parts);
    }

    /**
     * Gets the file name without extension.
     * @return string
     *
     * @see FileInfo::removeExtension()
     */
    public function getBaseName() : string
    {
        return $this->removeExtension();
    }

    /**
     * @return int The size of the file, in bytes.
     * @throws FileHelper_Exception {@see FileHelper::ERROR_CANNOT_GET_SIZE} or {@see FileHelper::ERROR_FILE_DOES_NOT_EXIST}
     */
    public function getSize(): int
    {
        $this->requireExists();

        $size = filesize($this->getPath());
        if($size !== false) {
            return $size;
        }

        throw new FileHelper_Exception(
            'Cannot get file size.',
            sprintf(
                'Tried to get size of file: '.PHP_EOL.
                '[%s]',
                $this->getPath()
            ),
            FileHelper::ERROR_CANNOT_GET_SIZE
        );
    }

    public function getExtension(bool $lowercase=true) : string
    {
        return FileHelper::getExtension($this->path, $lowercase);
    }

    public function getFolder() : FolderInfo
    {
        return FolderInfo::factory($this->getFolderPath());
    }

    public function getFolderPath() : string
    {
        return dirname($this->path);
    }

    /**
     * @return $this
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_CANNOT_DELETE_FILE
     */
    public function delete() : FileInfo
    {
        if(!$this->exists())
        {
            return $this;
        }

        if(unlink($this->path))
        {
            return $this;
        }

        throw new FileHelper_Exception(
            sprintf(
                'Cannot delete file [%s].',
                $this->getName()
            ),
            sprintf(
                'The file [%s] cannot be deleted.',
                $this->getPath()
            ),
            FileHelper::ERROR_CANNOT_DELETE_FILE
        );
    }

    /**
     * @param string|PathInfoInterface|SplFileInfo $targetPath
     * @return FileInfo
     * @throws FileHelper_Exception
     */
    public function copyTo($targetPath) : FileInfo
    {
        $target = $this->checkCopyPrerequisites($targetPath);

        if(copy($this->path, (string)$target))
        {
            return $target;
        }

        throw new FileHelper_Exception(
            sprintf(
                'Cannot copy file [%s].',
                $this->getName()
            ),
            sprintf(
                'The file [%s] could not be copied from [%s] to [%s].',
                $this->getName(),
                $this->path,
                $targetPath
            ),
            FileHelper::ERROR_CANNOT_COPY_FILE
        );
    }

    /**
     * @param string|PathInfoInterface|SplFileInfo $targetPath
     * @return FileInfo
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_SOURCE_FILE_NOT_FOUND
     * @see FileHelper::ERROR_SOURCE_FILE_NOT_READABLE
     * @see FileHelper::ERROR_TARGET_COPY_FOLDER_NOT_WRITABLE
     */
    private function checkCopyPrerequisites($targetPath) : FileInfo
    {
        $this->requireExists(FileHelper::ERROR_SOURCE_FILE_NOT_FOUND);
        $this->requireReadable(FileHelper::ERROR_SOURCE_FILE_NOT_READABLE);

        $target = FileHelper::getPathInfo($targetPath);

        // It's a file? Then we can use it as-is.
        if($target instanceof self) {
            return $target
                ->requireIsFile()
                ->createFolder();
        }

        // The target is a path that cannot be recognized as a file,
        // but is not a folder: very likely a file without extension.
        // In this case, we create an empty file to be able to return
        // a FileInfo instance.
        if($target instanceof IndeterminatePath)
        {
            return $target->convertToFile();
        }

        throw new FileHelper_Exception(
            'Cannot copy a file to a folder.',
            sprintf(
                'Tried to copy file [%s] to folder [%s].',
                $this,
                $target
            ),
            FileHelper::ERROR_CANNOT_COPY_FILE_TO_FOLDER
        );
    }

    /**
     * @var LineReader|NULL
     */
    private ?LineReader $lineReader = null;

    public function getLineReader() : LineReader
    {
        if($this->lineReader === null)
        {
            $this->lineReader = new LineReader($this);
        }

        return $this->lineReader;
    }

    /**
     * @return string
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_CANNOT_READ_FILE_CONTENTS
     */
    public function getContents() : string
    {
        $this->requireExists();

        $result = file_get_contents($this->getPath());

        if($result !== false) {
            return $result;
        }

        throw new FileHelper_Exception(
            sprintf('Cannot read contents of file [%s].', $this->getName()),
            sprintf(
                'Tried opening file for reading at: [%s].',
                $this->getPath()
            ),
            FileHelper::ERROR_CANNOT_READ_FILE_CONTENTS
        );
    }

    /**
     * @inheritDoc
     * @return $this
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_SAVE_FILE_WRITE_FAILED
     */
    public function putContents(string $content) : self
    {
        if($this->exists())
        {
            $this->requireWritable();
        }
        else
        {
            FolderInfo::factory(dirname($this->path))
                ->create()
                ->requireWritable();
        }

        if(file_put_contents($this->path, $content) !== false)
        {
            return $this;
        }

        throw new FileHelper_Exception(
            sprintf('Cannot save file: writing content to the file [%s] failed.', $this->getName()),
            sprintf(
                'Tried saving content to file in path [%s].',
                $this->getPath()
            ),
            FileHelper::ERROR_SAVE_FILE_WRITE_FAILED
        );
    }

    public function getDownloader() : FileSender
    {
        return new FileSender($this);
    }

    /**
     * Attempts to create the folder of the file if it
     * does not exist yet. Use this with files that do
     * not exist in the file system yet.
     *
     * @return $this
     * @throws FileHelper_Exception
     */
    private function createFolder() : FileInfo
    {
        if(!$this->exists())
        {
            FolderInfo::factory($this->getFolderPath())
                ->create()
                ->requireWritable(FileHelper::ERROR_TARGET_COPY_FOLDER_NOT_WRITABLE);
        }

        return $this;
    }

    public function detectEOLCharacter() : ?ConvertHelper_EOL
    {
        // 20 lines is enough to get a good picture of the newline style in the file.
        $string = implode('', $this->getLineReader()->getLines(20));

        return ConvertHelper::detectEOLCharacter($string);
    }

    public function countLines() : int
    {
        return $this->getLineReader()->countLines();
    }

    public function getLine(int $lineNumber) : ?string
    {
        return $this->getLineReader()->getLine($lineNumber);
    }

    public function getMimeType() : string
    {
        return FileHelper_MimeTypes::getMime($this->getExtension());
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function send(string $fileName, ?bool $asAttachment=false) : self
    {
        $this->getDownloader()->send($fileName, $asAttachment);
        return $this;
    }
}
