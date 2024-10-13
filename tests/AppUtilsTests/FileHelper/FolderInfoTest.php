<?php

declare(strict_types=1);

namespace AppUtilsTests\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;
use AppUtilsTestClasses\FileHelperTestCase;

class FolderInfoTest extends FileHelperTestCase
{
    const PATH_FOLDER_TREE = __DIR__ . '/../../assets/FileHelper/FolderTree';
    const PATH_FOLDER_WITH_FILES = __DIR__ . '/../../assets/FileHelper/PathInfo/FolderWithFiles';

    public function test_isFolderExists() : void
    {
        $info = FolderInfo::factory($this->assetsFolder.'/FileFinder');

        $this->assertTrue($info->exists());
        $this->assertTrue($info->isFolder());
        $this->assertSame('FileFinder', $info->getName());
    }

    public function test_isFolderNotExists() : void
    {
        $info = FolderInfo::factory('UnknownFolder');

        $this->assertTrue($info->isFolder());
        $this->assertFalse($info->exists());
        $this->assertSame('UnknownFolder', $info->getName());
    }

    public function test_filePath() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_PATH_IS_NOT_A_FOLDER);

        FolderInfo::factory('UnknownFolder/File.ext');
    }

    public function test_emptyFolder() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_PATH_INVALID);

        FolderInfo::factory('');
    }

    public function test_is_dir() : void
    {
        $this->assertFalse(FolderInfo::is_dir('file-name.ext'));
        $this->assertFalse(FolderInfo::is_dir('path/to/folder/file-name.ext'));
        $this->assertFalse(FolderInfo::is_dir(''));
        $this->assertTrue(FolderInfo::is_dir('path/to/folder/'));
        $this->assertTrue(FolderInfo::is_dir('path/'));
        $this->assertTrue(FolderInfo::is_dir('path./'));
        $this->assertFalse(FolderInfo::is_dir('.'));
        $this->assertFalse(FolderInfo::is_dir('..'));
        $this->assertFalse(FolderInfo::is_dir('./'));
        $this->assertFalse(FolderInfo::is_dir('../'));
    }

    public function test_saveJSONFile() : void
    {
        $info = FolderInfo::factory(__DIR__.'/../../assets/FileHelper/PathInfo');

        $jsonFile = $info->saveJSONFile(array('foo' => 'bar'), 'TestJSON.json');

        $this->assertFileExists($jsonFile->getPath());

        FileHelper::deleteFile($jsonFile);
    }

    public function test_getSize() : void
    {
        $info = FolderInfo::factory(self::PATH_FOLDER_WITH_FILES);

        $this->assertSame(14, $info->getSize());
    }

    public function test_getSubFolders() : void
    {
        $info = FolderInfo::factory(self::PATH_FOLDER_TREE);

        $this->assertTrue($info->exists());

        $subFolders = $info->getSubFolders();

        $this->assertCount(2, $subFolders);
        $this->assertSame('SubFolderA', $subFolders[0]->getName());
        $this->assertSame('SubFolderB', $subFolders[1]->getName());
    }

    public function test_getSubFile() : void
    {
        $info = FolderInfo::factory(self::PATH_FOLDER_WITH_FILES);

        $this->assertTrue($info->exists());

        $subFile = $info->getSubFile('fileA.txt');

        $this->assertTrue($subFile->exists());
    }

    public function test_isEmpty_nonEmptyFolderIsNotEmpty() : void
    {
        $this->assertFalse(FolderInfo::factory(self::PATH_FOLDER_TREE)->isEmpty());
    }

    public function test_isEmpty_emptyFolderIsEmpty() : void
    {
        $folder = FolderInfo::factory(__DIR__.'/../../assets/FileHelper/EmptyFolder')->create();

        $this->assertTrue($folder->isEmpty());

        $folder->delete();
    }

    public function test_isEmpty_nonExistentFolderIsEmpty() : void
    {
        $folder = FolderInfo::factory(__DIR__.'/../../assets/FileHelper/NonExistentFolder');

        $this->assertFalse($folder->exists());
        $this->assertTrue($folder->isEmpty());
    }

    public function test_getSubFiles() : void
    {
        $files = FolderInfo::factory(self::PATH_FOLDER_WITH_FILES)->getSubFiles();

        $this->assertCount(2, $files);
        $this->assertSame('fileA.txt', $files[0]->getName());
        $this->assertSame('fileB.txt', $files[1]->getName());
    }

    public function test_createFileFinder() : void
    {
        $files = FolderInfo::factory(self::PATH_FOLDER_WITH_FILES)
            ->createFileFinder()
            ->includeExtension('txt')
            ->getFiles()
            ->typeANY();

        $this->assertCount(2, $files);
    }
}
