<?php

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\ConvertHelper_EOL;
use AppUtils\FileHelper\FileInfo\FileSender;
use AppUtils\FileHelper\FileInfo\LineReader;
use AppUtils\FileHelper_Exception;
use SplFileInfo;

interface FileInfoInterface extends PathInfoInterface
{
    public function removeExtension(bool $keepPath=false) : string;

    /**
     * Gets the file name without extension.
     * @return string
     *
     * @see FileInfo::removeExtension()
     */
    public function getBaseName() : string;

    public function getFolder() : FolderInfo;

    /**
     * @param string|PathInfoInterface|SplFileInfo $targetPath
     * @return FileInfo
     * @throws FileHelper_Exception
     */
    public function copyTo(string|PathInfoInterface|SplFileInfo $targetPath) : FileInfo;

    /**
     * Gets an instance of the line reader, which can
     * read the contents of the file, line by line.
     *
     * @return LineReader
     */
    public function getLineReader() : LineReader;

    public function getContents() : string;

    /**
     * @param string $content
     * @return $this
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_SAVE_FILE_WRITE_FAILED
     */
    public function putContents(string $content) : self;

    public function getDownloader() : FileSender;

    /**
     * Detects the end of line style used in the target file, if any.
     * Can be used with large files because it only reads part of it.
     *
     * @return NULL|ConvertHelper_EOL The end of line character information, or NULL if none is found.
     * @throws FileHelper_Exception
     */
    public function detectEOLCharacter() : ?ConvertHelper_EOL;

    public function countLines() : int;

    public function getLine(int $lineNumber) : ?string;

    /**
     * Attempts to detect the file's mime type by its extension.
     * @return string
     */
    public function getMimeType() : string;

    /**
     * Alias for using {@see self::getDownloader()} to send the file,
     * with the added benefit of being able to chain the method calls.
     *
     * @param string $fileName
     * @param bool|null $asAttachment
     * @return $this
     */
    public function send(string $fileName, ?bool $asAttachment=false) : self;
}
