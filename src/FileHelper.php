<?php
/**
 * File containing the {@see AppUtils\FileHelper} class.
 * 
 * @package Application Utils
 * @subpackage FileHelper
 * @see FileHelper
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\FileHelper\AbstractPathInfo;
use AppUtils\FileHelper\CLICommandChecker;
use AppUtils\FileHelper\FileDownloader;
use AppUtils\FileHelper\FileFinder;
use AppUtils\FileHelper\FileInfo\NameFixer;
use AppUtils\FileHelper\PathRelativizer;
use AppUtils\FileHelper\PathsReducer;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\FolderTree;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\JSONFile;
use AppUtils\FileHelper\PathInfoInterface;
use AppUtils\FileHelper\PHPFile;
use AppUtils\FileHelper\SerializedFile;
use AppUtils\FileHelper\UnicodeHandling;
use AppUtils\FileHelper\UploadFileSizeInfo;
use DateTime;
use JsonException;
use SplFileInfo;

/**
 * Collection of file system related methods.
 * 
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class FileHelper
{
    public const int ERROR_CANNOT_FIND_JSON_FILE = 340001;
    public const int ERROR_CANNOT_DECODE_JSON_FILE = 340003;
    public const int ERROR_JSON_ENCODE_ERROR = 340005;
    public const int ERROR_CANNOT_OPEN_URL = 340008;
    public const int ERROR_CANNOT_CREATE_FOLDER = 340009;
    public const int ERROR_FILE_NOT_READABLE = 340010;
    public const int ERROR_CANNOT_COPY_FILE = 340011;
    public const int ERROR_CANNOT_DELETE_FILE = 340012;
    public const int ERROR_FIND_SUBFOLDERS_FOLDER_DOES_NOT_EXIST = 340014;
    public const int ERROR_UNKNOWN_FILE_MIME_TYPE = 340015;
    public const int ERROR_SERIALIZED_FILE_CANNOT_BE_READ = 340017;
    public const int ERROR_SERIALIZED_FILE_UNSERIALZE_FAILED = 340018;
    public const int ERROR_UNSUPPORTED_OS_CLI_COMMAND = 340019;
    public const int ERROR_SOURCE_FILE_NOT_FOUND = 340020;
    public const int ERROR_SOURCE_FILE_NOT_READABLE = 340021;
    public const int ERROR_TARGET_COPY_FOLDER_NOT_WRITABLE = 340022;
    public const int ERROR_SAVE_FOLDER_NOT_WRITABLE = 340023;
    public const int ERROR_SAVE_FILE_NOT_WRITABLE = 340024;
    public const int ERROR_SAVE_FILE_WRITE_FAILED = 340025;
    public const int ERROR_FILE_DOES_NOT_EXIST = 340026;
    public const int ERROR_CANNOT_OPEN_FILE_TO_READ_LINES = 340027;
    public const int ERROR_CANNOT_READ_FILE_CONTENTS = 340028;
    public const int ERROR_CURL_OUTPUT_NOT_STRING = 340031;
    public const int ERROR_CANNOT_OPEN_FILE_TO_DETECT_BOM = 340032;
    public const int ERROR_FOLDER_DOES_NOT_EXIST = 340033;
    public const int ERROR_PATH_IS_NOT_A_FOLDER = 340034;
    public const int ERROR_CANNOT_DELETE_FOLDER = 340036;
    public const int ERROR_REAL_PATH_NOT_FOUND = 340037;
    public const int ERROR_PATH_IS_NOT_A_FILE = 340038;
    public const int ERROR_PATH_NOT_WRITABLE = 340039;
    public const int ERROR_PATH_INVALID = 340040;
    public const int ERROR_CANNOT_COPY_FILE_TO_FOLDER = 340041;
    public const int ERROR_CANNOT_GET_SIZE = 340042;
    public const int ERROR_NO_MODIFIED_DATE_AVAILABLE = 340043;

    /**
    * Opens a serialized file and returns the unserialized data.
    *
    * @param string|PathInfoInterface|SplFileInfo $file
    * @throws FileHelper_Exception
    * @return array<int|string,mixed>
    * @see SerializedFile::parse()
    * 
    * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
    * @see FileHelper::ERROR_SERIALIZED_FILE_CANNOT_BE_READ
    * @see FileHelper::ERROR_SERIALIZED_FILE_UNSERIALZE_FAILED
    */
    public static function parseSerializedFile(string|PathInfoInterface|SplFileInfo$file) : array
    {
        return SerializedFile::factory($file)->parse();
    }

    /**
     * Deletes a folder tree with all files therein, including
     * the specified folder itself.
     *
     * @param string|PathInfoInterface|SplFileInfo $rootFolder
     * @return bool
     * @throws FileHelper_Exception
     */
    public static function deleteTree(string|PathInfoInterface|SplFileInfo $rootFolder) : bool
    {
        return FolderTree::delete($rootFolder);
    }

    /**
     * Create a folder if it does not exist yet.
     *
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return FolderInfo
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_CANNOT_CREATE_FOLDER
     */
    public static function createFolder(string|PathInfoInterface|SplFileInfo $path) : FolderInfo
    {
        return self::getFolderInfo($path)->create();
    }

    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return FolderInfo
     * @throws FileHelper_Exception
     */
    public static function getFolderInfo(string|PathInfoInterface|SplFileInfo $path) : FolderInfo
    {
        return FolderInfo::factory($path);
    }

    /**
     * Copies a folder tree to the target folder.
     *
     * @param string|PathInfoInterface|SplFileInfo $source
     * @param string|PathInfoInterface|SplFileInfo $target
     * @throws FileHelper_Exception
     * @see FolderTree
     */
    public static function copyTree(string|PathInfoInterface|SplFileInfo $source, string|PathInfoInterface|SplFileInfo $target) : void
    {
        FolderTree::copy($source, $target);
    }
    
   /**
    * Copies a file to the target location. Includes checks
    * for most error sources, like the source file not being
    * readable. Automatically creates the target folder if it
    * does not exist yet.
    * 
    * @param string|PathInfoInterface|SplFileInfo $sourcePath
    * @param string|PathInfoInterface|SplFileInfo $targetPath
    * @throws FileHelper_Exception
    * 
    * @see FileHelper::ERROR_CANNOT_CREATE_FOLDER
    * @see FileHelper::ERROR_SOURCE_FILE_NOT_FOUND
    * @see FileHelper::ERROR_SOURCE_FILE_NOT_READABLE
    * @see FileHelper::ERROR_TARGET_COPY_FOLDER_NOT_WRITABLE
    * @see FileHelper::ERROR_CANNOT_COPY_FILE
    */
    public static function copyFile(string|PathInfoInterface|SplFileInfo $sourcePath, string|PathInfoInterface|SplFileInfo $targetPath) : void
    {
        self::getFileInfo($sourcePath)->copyTo($targetPath);
    }
    
   /**
    * Deletes the target file. Ignored if it cannot be found,
    * and throws an exception if it fails.
    * 
    * @param string|PathInfoInterface|SplFileInfo $filePath
    * @throws FileHelper_Exception
    * 
    * @see FileHelper::ERROR_CANNOT_DELETE_FILE
    */
    public static function deleteFile(string|PathInfoInterface|SplFileInfo $filePath) : void
    {
        self::getFileInfo($filePath)->delete();
    }

    /**
     * Retrieves an instance of the file info class, which
     * allows file operations and accessing information on
     * the file.
     *
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return FileInfo
     * @throws FileHelper_Exception
     */
    public static function getFileInfo(string|PathInfoInterface|SplFileInfo $path) : FileInfo
    {
        return FileInfo::factory($path);
    }

    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return PathInfoInterface
     * @throws FileHelper_Exception
     */
    public static function getPathInfo(string|PathInfoInterface|SplFileInfo $path) : PathInfoInterface
    {
        return AbstractPathInfo::resolveType($path);
    }

    /**
     * Detects the mime type for the specified file name/path.
     * Returns null if it is not a known file extension.
     *
     * @param string|PathInfoInterface|SplFileInfo $fileName
     * @return string|NULL
     */
    public static function detectMimeType(string|PathInfoInterface|SplFileInfo $fileName) : ?string
    {
        $ext = self::getExtension($fileName);
        if(empty($ext)) {
            return null;
        }

        return FileHelper_MimeTypes::getMime($ext);
    }

    /**
     * Like `sendFile()`, but automatically determines whether
     * the browser can open the target file type, to either
     * send it directly to the browser, or force downloading
     * it instead.
     *
     * @param string|PathInfoInterface|SplFileInfo $filePath
     * @param string $fileName
     * @throws FileHelper_Exception
     */
    public static function sendFileAuto(string|PathInfoInterface|SplFileInfo $filePath, string $fileName = '') : void
    {
        $file = FileInfo::factory($filePath)
            ->requireExists()
            ->requireReadable();

        self::sendFile(
            $file,
            $fileName,
            !FileHelper_MimeTypes::canBrowserDisplay($file->getExtension())
        );
    }

    /**
     * Detects the mime type of the target file automatically,
     * sends the required headers to trigger a download and
     * outputs the file. Returns false if the mime type could
     * not be determined.
     * 
     * @param string|PathInfoInterface|SplFileInfo $filePath
     * @param string|null $fileName The name of the file for the client.
     * @param bool $asAttachment Whether to force the client to download the file.
     * @throws FileHelper_Exception
     * 
     * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
     * @see FileHelper::ERROR_UNKNOWN_FILE_MIME_TYPE
     */
    public static function sendFile(string|PathInfoInterface|SplFileInfo $filePath, ?string $fileName = null, bool $asAttachment=true) : void
    {
        self::getFileInfo($filePath)->getDownloader()->send($fileName, $asAttachment);
    }

    /**
     * Uses cURL to download the contents of the specified URL,
     * returns the content.
     *
     * @param string $url
     * @param int $timeout In seconds. Set to 0 to use the default.
     * @param bool $SSLEnabled Whether to enable HTTPs host verification.
     * @return string
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_CANNOT_OPEN_URL
     */
    public static function downloadFile(string $url, int $timeout=0, bool $SSLEnabled=false) : string
    {
        return FileDownloader::factory($url)
            ->setTimeout($timeout)
            ->setSSLEnabled($SSLEnabled)
            ->download();
    }

    /**
     * Verifies whether the target file is a PHP file. The path
     * to the file can be a path to a file as a string, or a
     * {@see SplFileInfo} object instance.
     *
     * @param string|PathInfoInterface|SplFileInfo $filePath
     * @return boolean
     */
    public static function isPHPFile(string|PathInfoInterface|SplFileInfo $filePath) : bool
    {
    	return self::getExtension($filePath) === 'php';
    }

    /**
     * Retrieves the extension of the specified file. Can be a path
     * to a file as a string, or a {@see SplFileInfo} object instance.
     *
     * > NOTE: A folder will return an empty string.
     *
     * @param string|PathInfoInterface|SplFileInfo $fileName
     * @param bool $lowercase
     * @return string
     */
    public static function getExtension(string|PathInfoInterface|SplFileInfo $fileName, bool $lowercase = true) : string
    {
        if($fileName instanceof PathInfoInterface) {
            return $fileName->getExtension($lowercase);
        }

        if($fileName instanceof SplFileInfo) {
            $fileName = $fileName->getFilename();
        }

        $ext = (string)pathinfo($fileName, PATHINFO_EXTENSION);

        if($lowercase) {
            $ext = mb_strtolower($ext);
        }

        return $ext;
    }

    /**
     * Retrieves the file name from a path, with or without extension.
     * The path to the file can be a string, or a {@see SplFileInfo}
     * object instance.
     *
     * In the case of folders, it behaves like the "pathinfo" function: returns
     * the name of the folder.
     *
     * @param string|PathInfoInterface|SplFileInfo $pathOrDirIterator
     * @param bool $extension
     * @return string
     * @throws FileHelper_Exception
     */
    public static function getFilename(string|PathInfoInterface|SplFileInfo $pathOrDirIterator, bool $extension = true) : string
    {
        $info = self::getPathInfo($pathOrDirIterator);

        if($extension === true || $info instanceof FolderInfo)
        {
            return $info->getName();
        }

        return $info->requireIsFile()->removeExtension();
    }

    /**
     * Tries to read the contents of the target file and
     * treat it as JSON to return the decoded JSON data.
     *
     * @param string|PathInfoInterface|SplFileInfo $file
     * @param string $targetEncoding
     * @param string|string[]|null $sourceEncoding
     * @return array<int|string,mixed>
     *
     * @throws FileHelper_Exception
     * @throws JsonException
     * @see FileHelper::ERROR_CANNOT_FIND_JSON_FILE
     * @see FileHelper::ERROR_CANNOT_DECODE_JSON_FILE
     */
    public static function parseJSONFile(string|PathInfoInterface|SplFileInfo $file, string $targetEncoding='', string|array|null $sourceEncoding=null) : array
    {
        return JSONFile::factory($file)
            ->setTargetEncoding($targetEncoding)
            ->setSourceEncodings($sourceEncoding)
            ->parse();
    }
    
   /**
    * Corrects common formatting mistakes when users enter
    * file names, like too many spaces, dots, and the like.
    * 
    * NOTE: if the file name contains a path, the path is
    * stripped, leaving only the file name.
    * 
    * @param string $name
    * @return string
    */
    public static function fixFileName(string $name) : string
    {
        return NameFixer::fixName($name);
    }

    /**
     * Creates an instance of the file finder, which is an easier
     * alternative to the other manual findFile methods, since chaining
     * can set all options.
     *
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return FileFinder
     * @throws FileHelper_Exception
     *
     * @see FileFinder::ERROR_PATH_DOES_NOT_EXIST
     */
    public static function createFileFinder(string|PathInfoInterface|SplFileInfo $path) : FileFinder
    {
        return new FileFinder($path);
    }

    /**
     * Searches for all HTML files in the target folder.
     *
     * > NOTE: This method only exists for backwards compatibility.
     * > Use the {@see FileHelper::createFileFinder()} method instead,
     * > which offers an object-oriented interface that is much easier
     * > to use.
     *
     * @param string|PathInfoInterface|SplFileInfo $targetFolder
     * @param array<string,mixed> $options
     * @return string[] An indexed array with files.
     * @throws FileHelper_Exception
     * @see FileHelper::createFileFinder()
     * @deprecated Use the {@see FileHelper::createFileFinder()} instead.
     */
    public static function findHTMLFiles(string|PathInfoInterface|SplFileInfo $targetFolder, array $options=array()) : array
    {
        return self::findFiles($targetFolder, array('html'), $options);
    }

    /**
     * Searches for all PHP files in the target folder.
     *
     * NOTE: This method only exists for backwards compatibility.
     * Use the {@see FileHelper::createFileFinder()} method instead,
     * which offers an object-oriented interface that is much easier
     * to use.
     *
     * @param string|PathInfoInterface|SplFileInfo $targetFolder
     * @param array<string,mixed> $options
     * @return string[] An indexed array of PHP files.
     * @throws FileHelper_Exception
     * @see FileHelper::createFileFinder()
     */
    public static function findPHPFiles(string|PathInfoInterface|SplFileInfo $targetFolder, array $options=array()) : array
    {
        return self::findFiles($targetFolder, array('php'), $options);
    }
    
   /**
    * Finds files according to the specified options.
    * 
    * NOTE: This method only exists for backwards compatibility.
    * Use the {@see FileHelper::createFileFinder()} method instead,
    * which offers an object-oriented interface that is much easier
    * to use.
    *  
    * @param string|PathInfoInterface|SplFileInfo $targetFolder
    * @param string[] $extensions
    * @param array<string,mixed> $options
    * @throws FileHelper_Exception
    * @return string[]
    *
    * @see FileHelper::createFileFinder()
    * @deprecated Use the file finder instead.
    */
    public static function findFiles(string|PathInfoInterface|SplFileInfo $targetFolder, array $extensions=array(), array $options=array()) : array
    {
        $finder = self::createFileFinder($targetFolder);

        foreach ($extensions as $extension) {
            $finder->includeExtension($extension);
        }

        $finder->setPathmodeStrip();
        
        if(isset($options['relative-path']) && $options['relative-path'] === true) 
        {
            $finder->setPathmodeRelative();
        } 
        else if(isset($options['absolute-path']) && $options['absolute-path'] === true)
        {
            $finder->setPathmodeAbsolute();
        }
        
        if(isset($options['strip-extension'])) 
        {
            $finder->stripExtensions();
        }
        
        $finder->setOptions($options);
        
        return $finder->getMatches();
    }

    /**
     * Removes the extension from the specified path or file name,
     * if any, and returns the name without the extension.
     *
     * @param string|PathInfoInterface|SplFileInfo $filename
     * @param bool $keepPath Whether to keep the path component, if any. Default PHP pathinfo behavior is no.
     * @return string
     * @throws FileHelper_Exception
     */
    public static function removeExtension(string|PathInfoInterface|SplFileInfo $filename, bool $keepPath=false) : string
    {
        $path = self::getPathInfo($filename);

        if($path instanceof FileInfo)
        {
            return $path->removeExtension($keepPath);
        }

        if($keepPath)
        {
            return (string)$filename;
        }

        return basename((string)$filename);
    }

    /**
     * Adds the specified extension to the file name, replacing
     * any existing extension.
     *
     * @param string $fileName
     * @param string $extension
     * @return string
     */
    public static function addExtension(string $fileName, string $extension) : string
    {
        $extension = ltrim($extension, '.');

        if(str_contains($fileName, '.')) {
            $fileName = self::removeExtension($fileName, true);
        }

        if(!str_ends_with(strtolower($fileName), strtolower($extension))) {
            $fileName .= '.'.$extension;
        }

        return $fileName;
    }

    /**
     * @var UnicodeHandling|NULL
     */
    private static ?UnicodeHandling $unicodeHandling = null;

    public static function createUnicodeHandling() : UnicodeHandling
    {
        if(!isset(self::$unicodeHandling))
        {
            self::$unicodeHandling = new UnicodeHandling();
        }

        return self::$unicodeHandling;
    }
    
   /**
    * Normalizes the slash style in a file or folder path,
    * by replacing any anti-slashes with forward slashes.
    * 
    * @param string $path
    * @return string
    */
    public static function normalizePath(string $path) : string
    {
        return str_replace(array('\\', '//'), array('/', '/'), $path);
    }

    /**
     * Saves the specified data to a file, JSON encoded.
     *
     * @param array<int|string,mixed> $data
     * @param string|PathInfoInterface|SplFileInfo $file
     * @param bool $pretty
     * @return JSONFile
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_JSON_ENCODE_ERROR
     * @see FileHelper::ERROR_SAVE_FOLDER_NOT_WRITABLE
     * @see FileHelper::ERROR_SAVE_FILE_NOT_WRITABLE
     * @see FileHelper::ERROR_SAVE_FILE_WRITE_FAILED
     */
    public static function saveAsJSON(array $data, string|PathInfoInterface|SplFileInfo $file, bool $pretty=false) : JSONFile
    {
        return JSONFile::factory($file)->putData($data, $pretty);
    }

    /**
     * Saves the specified content to the target file, creating
     * the file and the folder as necessary.
     *
     * @param string|PathInfoInterface|SplFileInfo $filePath
     * @param string $content
     * @return FileInfo
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_SAVE_FOLDER_NOT_WRITABLE
     * @see FileHelper::ERROR_SAVE_FILE_NOT_WRITABLE
     * @see FileHelper::ERROR_SAVE_FILE_WRITE_FAILED
     */
    public static function saveFile(string|PathInfoInterface|SplFileInfo $filePath, string $content='') : FileInfo
    {
        return self::getFileInfo($filePath)->putContents($content);
    }

    /**
     * Checks whether it is possible to run PHP command
     * line commands.
     *
     * @return boolean
     * @throws FileHelper_Exception
     */
    public static function canMakePHPCalls() : bool
    {
        return self::cliCommandExists('php');
    }
    
    /**
     * Determines if a command exists on the current environment's command line interface.
     *
     * @param string $command The name of the command to check, e.g. "php"
     * @return bool True if the command has been found, false otherwise.
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_UNSUPPORTED_OS_CLI_COMMAND
     */
    public static function cliCommandExists(string $command) : bool
    {
        return CLICommandChecker::factory()->exists($command);
    }

    /**
     * Validates a PHP file's syntax.
     *
     * > NOTE: This will fail silently if the PHP command line
     * > is not available. Use {@link FileHelper::canMakePHPCalls()}
     * > to check this beforehand as needed.
     *
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return true|string[] A boolean true if the file is valid, an array with validation messages otherwise.
     * @throws FileHelper_Exception
     * @deprecated Use {@see PHPFile::checkSyntax()} instead.
     */
    public static function checkPHPFileSyntax(string|PathInfoInterface|SplFileInfo $path) : true|array
    {
        return PHPFile::factory($path)->checkSyntax();
    }

    /**
     * Retrieves the last modified date for the specified file or folder.
     *
     * > Note: If the target does not exist, returns null.
     *
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return DateTime|NULL
     * @throws FileHelper_Exception
     */
    public static function getModifiedDate(string|PathInfoInterface|SplFileInfo $path) : ?DateTime
    {
        return self::getFileInfo($path)->getModifiedDate();
    }

    /**
     * Retrieves the names of all sub-folders in the specified path.
     *
     * Available options:
     *
     * - recursive: true/false
     *   Whether to search for sub-folders recursively.
     *
     * - absolute-paths: true/false
     *   Whether to return a list of absolute paths.
     *
     * @param string|PathInfoInterface|SplFileInfo $targetFolder
     * @param array<string,mixed> $options
     * @return string[]
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_FIND_SUBFOLDERS_FOLDER_DOES_NOT_EXIST
     */
    public static function getSubfolders(string|PathInfoInterface|SplFileInfo $targetFolder, array $options = array()) : array
    {
        return FolderInfo::factory($targetFolder)
            ->createFolderFinder()
            ->setOptions($options)
            ->getPaths();
    }

   /**
    * Retrieves the maximum allowed upload file size, in bytes.
    * Takes into account the PHP ini settings <code>post_max_size</code>
    * and <code>upload_max_filesize</code>. Since these cannot
    * be modified at runtime, they are the hard limits for uploads.
    * 
    * NOTE: Based on binary values, where 1KB = 1024 Bytes.
    * 
    * @return int Will return <code>-1</code> if no limit.
    */
    public static function getMaxUploadFilesize() : int
    {
        return UploadFileSizeInfo::getFileSize();
    }
   
   /**
    * Makes a path relative using a folder depth: will reduce the
    * length of the path so that only the amount of folders defined
    * in the <code>$depth</code> attribute are shown below the actual
    * folder or file in the path.
    *  
    * @param string  $path The absolute or relative path
    * @param int $depth The folder depth to reduce the path to
    * @return string
    */
    public static function relativizePathByDepth(string $path, int $depth=2) : string
    {
        return PathRelativizer::relativizeByDepth($path, $depth);
    }
    
   /**
    * Makes the specified path relative to another path,
    * by removing one from the other if found. Also 
    * normalizes the path to use forward slashes. 
    * 
    * Example:
    * 
    * <pre>
    * relativizePath('c:\some\folder\to\file.txt', 'c:\some\folder');
    * </pre>
    * 
    * Result: <code>to/file.txt</code>
    * 
    * @param string $path
    * @param string $relativeTo
    * @return string
    */
    public static function relativizePath(string $path, string $relativeTo) : string
    {
        return PathRelativizer::relativize($path, $relativeTo);
    }
    
   /**
    * Checks that the target file exists, and throws an exception
    * if it does not. 
    * 
    * @param string|PathInfoInterface|SplFileInfo $path
    * @param int|NULL $errorCode Optional custom error code
    * @throws FileHelper_Exception
    * @return string The real path to the file
    * 
    * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
    * @see FileHelper::ERROR_REAL_PATH_NOT_FOUND
    */
    public static function requireFileExists(string|PathInfoInterface|SplFileInfo $path, ?int $errorCode=null) : string
    {
        return self::getPathInfo($path)
            ->requireIsFile()
            ->requireExists($errorCode)
            ->getRealPath();
    }

    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @param int|NULL $errorCode
     * @return string
     * @throws FileHelper_Exception
     */
    public static function requireFileReadable(string|PathInfoInterface|SplFileInfo $path, ?int $errorCode=null) : string
    {
        return self::getPathInfo($path)
            ->requireIsFile()
            ->requireReadable($errorCode)
            ->getPath();
    }
    
   /**
    * Reads a specific line number from the target file and returns its
    * contents, if the file has such a line. Does so with little memory
    * usage, as the file is not read entirely into memory.
    * 
    * @param string|PathInfoInterface|SplFileInfo $path
    * @param int $lineNumber Note: 1-based; the first line is number 1.
    * @return string|NULL Will return null if the requested line does not exist.
    * @throws FileHelper_Exception
    * 
    * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
    */
    public static function getLineFromFile(string|PathInfoInterface|SplFileInfo $path, int $lineNumber) : ?string
    {
        return self::getFileInfo($path)->getLine($lineNumber);
    }

    /**
     * Retrieves the total amount of lines in the file, without
     * reading the whole file into memory.
     *
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return int
     * @throws FileHelper_Exception
     */
    public static function countFileLines(string|PathInfoInterface|SplFileInfo $path) : int
    {
        return self::getFileInfo($path)->countLines();
    }

    /**
     * Parses the target file to detect any PHP classes contained
     * within, and retrieve information on them. Does not use the
     * PHP reflection API.
     *
     * @param string|PathInfoInterface|SplFileInfo $filePath
     * @return FileHelper_PHPClassInfo
     * @throws FileHelper_Exception
     */
    public static function findPHPClasses(string|PathInfoInterface|SplFileInfo $filePath) : FileHelper_PHPClassInfo
    {
        return PHPFile::factory($filePath)->findClasses();
    }

    /**
     * Detects the end of line style used in the target file, if any.
     * Can be used with large files because it only reads part of it.
     *
     * @param string|PathInfoInterface|SplFileInfo $filePath The path to the file.
     * @return NULL|ConvertHelper_EOL The end of line character information, or NULL if none is found.
     * @throws FileHelper_Exception
     */
    public static function detectEOLCharacter(string|PathInfoInterface|SplFileInfo $filePath) : ?ConvertHelper_EOL
    {
        return self::getFileInfo($filePath)->detectEOLCharacter();
    }

    /**
     * Reads the specified amount of lines from the target file.
     * Unicode BOM compatible: any byte order marker is stripped
     * from the resulting lines.
     *
     * @param string|PathInfoInterface|SplFileInfo $filePath
     * @param int $amount Set to 0 to read all lines.
     * @return string[]
     *
     * @throws FileHelper_Exception
     * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
     * @see FileHelper::ERROR_CANNOT_OPEN_FILE_TO_READ_LINES
     */
    public static function readLines(string|PathInfoInterface|SplFileInfo $filePath, int $amount=0) : array
    {
        return self::getFileInfo($filePath)
            ->getLineReader()
            ->getLines($amount);
    }
    
   /**
    * Reads all content from a file.
    * 
    * @param string|PathInfoInterface|SplFileInfo $filePath
    * @throws FileHelper_Exception
    * @return string
    * 
    * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
    * @see FileHelper::ERROR_CANNOT_READ_FILE_CONTENTS
    */
    public static function readContents(string|PathInfoInterface|SplFileInfo $filePath) : string
    {
        return self::getFileInfo($filePath)->getContents();
    }

   /**
    * Ensures that the target path exists on disk, and is a folder.
    * 
    * @param string|PathInfoInterface|SplFileInfo $path
    * @return string The real path, with normalized slashes.
    * @throws FileHelper_Exception
    * 
    * @see FileHelper::normalizePath()
    * 
    * @see FileHelper::ERROR_FOLDER_DOES_NOT_EXIST
    * @see FileHelper::ERROR_PATH_IS_NOT_A_FOLDER
    */
    public static function requireFolderExists(string|PathInfoInterface|SplFileInfo $path) : string
    {
        return self::getFolderInfo($path)
            ->requireExists(self::ERROR_FOLDER_DOES_NOT_EXIST)
            ->getRealPath();
    }

    /**
     * Creates an instance of the path reducer tool, which can reduce
     * a list of paths to the closest common root folder.
     *
     * @param string[] $paths
     * @return PathsReducer
     *
     * @throws FileHelper_Exception
     */
    public static function createPathsReducer(array $paths=array()) : PathsReducer
    {
        return new PathsReducer($paths);
    }

    /**
     * Normalizes a path by resolving dots (`.`) and double dots (`..`)
     * in the path, without accessing the file system.
     *
     * > NOTE: Check if the target folder exists.
     *
     * @param string $path
     * @return string
     */
    public static function resolvePathDots(string $path): string
    {
        if(empty($path)) {
            return '';
        }

        $path = self::normalizePath($path);
        $drive = self::detectWindowsDriveLetter($path);
        if($drive !== null) {
            $path = self::removeWindowsDriveLetter($path, $drive);
        }

        $segments = explode('/', $path);

        $parts = array();
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($parts);
            } else {
                $parts[] = $segment;
            }
        }

        $normalized = ($path[0] === '/' ? '/' : '');
        $normalized .= implode('/', $parts);

        // Keep the ending slash if there was one
        if ($normalized !== '' && str_ends_with($path, '/')) {
            $normalized = rtrim($normalized, '/').'/';
        }

        if($drive !== null) {
            $normalized = $drive.':'.$normalized;
        }

        return $normalized;
    }

    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return string|null
     */
    public static function detectWindowsDriveLetter(string|PathInfoInterface|SplFileInfo $path) : ?string
    {
        $path = (string)$path;

        if(!str_contains($path, ':')) {
            return null;
        }

        $parts = ConvertHelper::explodeTrim(':', $path);

        $drive = strtolower(array_shift($parts));
        if(strlen($drive) === 1 && ctype_alpha($drive)) {
            return $drive;
        }

        return null;
    }

    public static function removeWindowsDriveLetter(string|PathInfoInterface|SplFileInfo $path, ?string $driveLetter=null) : string
    {
        if($driveLetter === null) {
            $driveLetter = self::detectWindowsDriveLetter($path);
        }

        if ($driveLetter === null) {
            return (string)$path;
        }

        $pathString = self::normalizePath(trim((string)$path));

        // Account for the drive letter being lower- or uppercase
        return substr($pathString, 2);
    }
}
