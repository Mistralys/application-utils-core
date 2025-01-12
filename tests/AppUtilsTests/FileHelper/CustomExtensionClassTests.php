<?php

declare(strict_types=1);

namespace AppUtilsTests\FileHelper;

use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FileInfo\ExtensionClassRegistry;
use AppUtilsTestClasses\FileHelper\MockFooFileInfo;
use AppUtilsTestClasses\FileHelperTestCase;

final class CustomExtensionClassTests extends FileHelperTestCase
{
    public function test_registerCustomClass() : void
    {
        ExtensionClassRegistry::registerExtensionClass(MockFooFileInfo::EXTENSION, MockFooFileInfo::class);

        $this->assertSame(MockFooFileInfo::class, ExtensionClassRegistry::getExtensionClass('foo'));
    }

    public function test_registeredExtensionUsesExtensionClass() : void
    {
        ExtensionClassRegistry::registerExtensionClass(MockFooFileInfo::EXTENSION, MockFooFileInfo::class);

        $file = FileInfo::factory('file.foo');

        $this->assertInstanceOf(MockFooFileInfo::class, $file);
    }

    public function test_fileClassUseOwnFactoryMethod() : void
    {
        ExtensionClassRegistry::registerExtensionClass(MockFooFileInfo::EXTENSION, MockFooFileInfo::class);

        $file = MockFooFileInfo::factory('file.foo');

        $this->assertFalse($file->exists());
    }
}
