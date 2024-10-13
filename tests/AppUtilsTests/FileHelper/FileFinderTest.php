<?php

declare(strict_types=1);

namespace AppUtilsTests\FileHelper;

use AppUtils\FileHelper\FolderInfo;
use AppUtilsTestClasses\FileHelperTestCase;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

final class FileFinderTest extends FileHelperTestCase
{
    // region: _Tests

    public function test_findFiles_default() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolderInfo);

        $files = $finder->getMatches();

        $expected = array(
            $this->assetsFolderInfo . '/.extension',
            $this->assetsFolderInfo . '/README.txt',
            $this->assetsFolderInfo . '/test-png.png'
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_recursive() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolderInfo);

        $finder->makeRecursive();

        $files = $finder->getMatches();

        $expected = array(
            $this->assetsFolderInfo . '/.extension',
            $this->assetsFolderInfo . '/README.txt',
            $this->assetsFolderInfo . '/test-png.png',
            $this->assetsFolderInfo . '/Subfolder/script.php',
            $this->assetsFolderInfo . '/Classmap/Class.php',
            $this->assetsFolderInfo . '/Classmap/Class/Subclass.php',
            $this->assetsFolderInfo . '/Classmap/Class/Subclass/Subsubclass.php'
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_relative() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolderInfo);

        $finder->makeRecursive();
        $finder->setPathmodeRelative();

        $files = $finder->getMatches();

        $expected = array(
            '.extension',
            'README.txt',
            'test-png.png',
            'Subfolder/script.php',
            'Classmap/Class.php',
            'Classmap/Class/Subclass.php',
            'Classmap/Class/Subclass/Subsubclass.php'
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_stripExtensions() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolderInfo);

        $finder->makeRecursive();
        $finder->setPathmodeRelative();
        $finder->stripExtensions();

        $files = $finder->getMatches();

        $expected = array(
            'README',
            'test-png',
            'Subfolder/script',
            'Classmap/Class',
            'Classmap/Class/Subclass',
            'Classmap/Class/Subclass/Subsubclass'
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_excludeExtensions() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolderInfo);

        $finder->setPathmodeRelative();
        $finder->excludeExtensions(array('txt'));

        $files = $finder->getMatches();

        $expected = array(
            '.extension',
            'test-png.png',
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_includeExtensions() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolderInfo);

        $finder->setPathmodeRelative();
        $finder->includeExtensions(array('txt'));

        $files = $finder->getMatches();

        $expected = array(
            'README.txt',
        );

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_pathSeparator() : void
    {
        $finder = FileHelper::createFileFinder($this->assetsFolderInfo);

        $finder->makeRecursive();
        $finder->stripExtensions();
        $finder->setSlashReplacement('-');
        $finder->setPathmodeRelative();
        $finder->includeExtensions(array('php'));

        $files = $finder->getMatches();

        $expected = array(
            'Subfolder-script',
            'Classmap-Class',
            'Classmap-Class-Subclass',
            'Classmap-Class-Subclass-Subsubclass'
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_findFiles_getPHPClassNames() : void
    {
        $files = FileHelper::createFileFinder($this->assetsFolderInfo)
            ->makeRecursive()
            ->getFiles()
            ->PHPClassNames();

        $expected = array(
            'Subfolder_script',
            'Classmap_Class',
            'Classmap_Class_Subclass',
            'Classmap_Class_Subclass_Subsubclass'
        );

        // ensure the same order for the comparison
        sort($files);
        sort($expected);

        $this->assertEquals($expected, $files);
    }

    public function test_pathNotExists() : void
    {
        $this->expectException(FileHelper_Exception::class);

        FileHelper::createFileFinder(md5('/path/that/does/not/exist'));
    }

    // endregion

    // region: Support methods

    protected FolderInfo $assetsFolderInfo;

    protected function setUp() : void
    {
        parent::setUp();

        $folder = FileHelper::getFolderInfo($this->assetsFolder . '/FileFinder');

        $folder->requireExists()->requireIsFolder();

        $this->assetsFolderInfo = $folder;
    }

    // endregion
}
