<?php

declare(strict_types=1);

namespace AppUtilsTests\FileHelper;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\JSONFile;
use AppUtils\FileHelper\JSONFile\JSONFileOptions;
use AppUtilsTestClasses\FileHelperTestCase;

class JSONFileTest extends FileHelperTestCase
{
    protected const TEST_FILE_VALID = 'json.json';
    protected const TEST_FILE_INVALID = 'json-broken.json';
    protected const TEST_FILE_VALID_KEY = 'test';
    protected const TEST_FILE_VALID_VALUE = 'okay';
    protected const TEST_FILE_WRITE = 'json-write.json';

    protected function registerFilesToDelete() : void
    {
        $this->registerFileToDelete(self::TEST_FILE_WRITE);
    }

    public function test_parseValid() : void
    {
        $targetFile = $this->assetsFolder.'/'.self::TEST_FILE_VALID;
        $data = FileHelper::parseJSONFile($targetFile);

        $this->assertArrayHasKey( self::TEST_FILE_VALID_KEY, $data);
        $this->assertSame(self::TEST_FILE_VALID_VALUE, $data[self::TEST_FILE_VALID_KEY]);
    }

    public function test_extensionCreatesJSONInfoInstance() : void
    {
        $targetFile = $this->assetsFolder.'/'.self::TEST_FILE_VALID;
        $this->assertInstanceOf(JSONFile::class, FileInfo::factory($targetFile));
    }

    public function test_parseInvalid() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_CANNOT_DECODE_JSON_FILE);

        FileHelper::parseJSONFile($this->assetsFolder.'/'.self::TEST_FILE_INVALID);
    }

    public function test_fileNotExists() : void
    {
        $this->expectExceptionCode(FileHelper::ERROR_FILE_DOES_NOT_EXIST);

        FileHelper::parseJSONFile('unknown/path/to/file.json');
    }

    public function test_putData() : void
    {
        $targetFile = $this->assetsFolder . '/' . self::TEST_FILE_WRITE;
        $data = array(
            self::TEST_FILE_VALID_KEY => self::TEST_FILE_VALID_VALUE
        );

        FileHelper::saveAsJSON($data, $targetFile);

        $this->assertSame($data, FileHelper::parseJSONFile($targetFile));
    }

    public function test_escapingSlashesEnabledByDefault() : void
    {
        $targetFile = JSONFile::factory($this->assetsFolder . '/' . self::TEST_FILE_WRITE)
            ->putData(array(
                'test' => 'https://example.com'
            ));

        $json = $targetFile->getContents();
        $this->assertStringContainsString('\\/', $json);
    }

    public function test_turnOffEscapingSlashes() : void
    {
        $targetFile = JSONFile::factory($this->assetsFolder . '/' . self::TEST_FILE_WRITE)
            ->setEscapeSlashes(false)
            ->putData(array(
                'test' => 'https://example.com'
            ));

        $json = $targetFile->getContents();
        $this->assertStringNotContainsString('\\/', $json);
    }

    public function test_prettyPrintDisabledByDefault() : void
    {
        $targetFile = JSONFile::factory($this->assetsFolder . '/' . self::TEST_FILE_WRITE)
            ->setTrailingNewline(false)
            ->putData(array(
                'test' => 'okay'
            ));

        $json = $targetFile->getContents();
        $this->assertStringNotContainsString("\n", $json);
    }

    public function test_setPrettyPrint() : void
    {
        $targetFile = JSONFile::factory($this->assetsFolder . '/' . self::TEST_FILE_WRITE)
            ->setTrailingNewline(false)
            ->setPrettyPrint(true)
            ->putData(array(
                'test' => 'okay'
            ));

        $json = $targetFile->getContents();
        $this->assertStringContainsString("\n", $json);
    }

    public function test_globalOptionDefaults() : void
    {
        $defaultOptions = $this->createTestJSONFile()->options();

        $this->assertSame($defaultOptions->isEscapeSlashesEnabled(), true);
        $this->assertSame($defaultOptions->isPrettyPrintEnabled(), false);
        $this->assertSame($defaultOptions->isTrailingNewlineEnabled(), false);
    }

    public function test_globalOptionOverwriting() : void
    {
        JSONFileOptions::setGlobalOption(JSONFileOptions::OPTION_ESCAPE_SLASHES, false);
        JSONFileOptions::setGlobalOption(JSONFileOptions::OPTION_PRETTY_PRINT, true);
        JSONFileOptions::setGlobalOption(JSONFileOptions::OPTION_TRAILING_NEWLINE, true);

        $defaultOptions = $this->createTestJSONFile()->options();

        $this->assertSame(false, $defaultOptions->isEscapeSlashesEnabled());
        $this->assertSame(true, $defaultOptions->isPrettyPrintEnabled());
        $this->assertSame(true, $defaultOptions->isTrailingNewlineEnabled());
    }

    public function test_localOptionTakesPrecedence() : void
    {
        JSONFileOptions::setGlobalOption(JSONFileOptions::OPTION_ESCAPE_SLASHES, false);

        $file = $this->createTestJSONFile();
        $options = $file->options();

        $this->assertSame(false, $options->isEscapeSlashesEnabled());

        $file->setEscapeSlashes(true);

        $this->assertSame(true, $options->isEscapeSlashesEnabled());
    }

    protected function setUp(): void
    {
        parent::setUp();

        JSONFileOptions::resetGlobalOptions();
    }

    public function createTestJSONFile() : JSONFile
    {
        return JSONFile::factory('somefile'.$this->getTestCounter().'.json');
    }
}
