<?php

declare(strict_types=1);

namespace AppUtils\FileHelper\FolderInfo;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\JSONFile;
use AppUtils\FileHelper\PHPFile;
use AppUtils\FileHelper\SerializedFile;
use AppUtils\FileHelper_Exception;
use AppUtils\Interfaces\StringableInterface;

class FileCreator
{
    private FolderInfo $folder;

    public function __construct(FolderInfo $folder)
    {
        $this->folder = $folder;
    }

    /**
     * Creates a new JSON file in the folder.
     * @param string $name The name of the JSON file, e.g. `foo.json`. You may omit the `.json` extension.
     * @param array<int|string,mixed> $data Optional data to write to the JSON file.
     * @param bool $pretty Whether to format the JSON data in a pretty way.
     * @return JSONFile
     * @throws FileHelper_Exception
     */
    public function json(string $name, array $data=array(), bool $pretty=false) : JSONFile
    {
        return JSONFile::factory($this->folder.'/'.FileHelper::addExtension($name, JSONFile::EXTENSION))
            ->putData($data, $pretty);
    }

    /**
     * Creates a new Serialized file in the folder.
     * @param string $name The name of the Serialized file, e.g. `foo.ser`. You may omit the `.ser` extension.
     * @param array<int|string,mixed> $data Optional data to write to the Serialized file.
     * @return SerializedFile
     * @throws FileHelper_Exception
     */
    public function serialized(string $name, array $data=array()) : SerializedFile
    {
        return SerializedFile::factory($this->folder.'/'.FileHelper::addExtension($name, SerializedFile::EXTENSION))
            ->putData($data);
    }

    /**
     * Creates a new PHP file in the folder.
     * @param string $name The name of the PHP file, e.g. ``foo.php`. You may omit the `.php` extension.
     * @param string|StringableInterface|string[] $statements Optional PHP statements to write to the file.
     * @param bool $strictTyping Whether to include strict typing declaration.
     * @param string|null $namespace Optional namespace for the PHP file.
     * @return PHPFile
     * @throws FileHelper_Exception
     */
    public function php(string $name, string|StringableInterface|array $statements='', bool $strictTyping=true, ?string $namespace=null) : PHPFile
    {
        return PHPFile::factory($this->folder.'/'.FileHelper::addExtension($name, PHPFile::EXTENSION))
            ->putStatements(
                $statements,
                $strictTyping,
                $namespace
            );
    }

    public function byName(string $name) : FileInfo
    {
        return FileInfo::factory($this->folder.'/'.$name)->putContents('');
    }
}
