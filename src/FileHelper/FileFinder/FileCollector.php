<?php
/**
 * @package Application Utils
 * @subpackage FileHelper
 */

declare(strict_types=1);

namespace AppUtils\FileHelper\FileFinder;

use AppUtils\FileHelper\FileFinder;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\JSONFile;
use AppUtils\FileHelper\PHPFile;
use AppUtils\FileHelper\SerializedFile;

/**
 * Utility class to simplify working with file paths.
 * Offers a range of methods to retrieve file paths
 * or file type instances.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class FileCollector
{
    private FileFinder $fileFinder;

    public function __construct(FileFinder $fileFinder)
    {
        $this->fileFinder = $fileFinder;
    }

    /**
     * Retrieves a list of all matching file names/paths,
     * (depending on the selected options).
     *
     * @return string[]
     */
    public function paths() : array
    {
        return $this->fileFinder->getMatches();
    }

    /**
     * Like {@see self::getAll()}, but returns {@see FileInfo}
     * instances for each file.
     *
     * @return FileInfo[]
     */
    public function typeANY() : array
    {
        $this->fileFinder->setPathmodeAbsolute();

        $result = array();
        foreach($this->paths() as $path) {
            $result[] = FileInfo::factory($path);
        }

        return $result;
    }

    /**
     * @return JSONFile[]
     */
    public function typeJSON() : array
    {
        $result = array();

        foreach($this->typeANY() as $info) {
            if($info instanceof JSONFile) {
                $result[] = $info;
            }
        }

        return $result;
    }

    /**
     * @return PHPFile[]
     */
    public function typePHP() : array
    {
        $result = array();

        foreach($this->typeANY() as $info) {
            if($info instanceof PHPFile) {
                $result[] = $info;
            }
        }

        return $result;
    }

    /**
     * @return SerializedFile[]
     */
    public function typeSerialized() : array
    {
        $result = array();

        foreach($this->typeANY() as $info) {
            if($info instanceof SerializedFile) {
                $result[] = $info;
            }
        }

        return $result;
    }

    /**
     * Retrieves only PHP files. Can be combined with other
     * options like enabling recursion into sub-folders.
     *
     * @return string[]
     */
    public function PHPPaths() : array
    {
        $this->fileFinder->includeExtensions(array('php'));
        return $this->paths();
    }

    /**
     * Generates PHP class names from file paths: it replaces
     * slashes with underscores, and removes file extensions.
     *
     * @return string[] An array of PHP file names without extension.
     */
    public function PHPClassNames() : array
    {
        $this->fileFinder->includeExtensions(array('php'));
        $this->fileFinder->stripExtensions();
        $this->fileFinder->setSlashReplacement('_');
        $this->fileFinder->setPathmodeRelative();

        return $this->paths();
    }
}
