<?php

declare(strict_types=1);

namespace AppUtilsTests\ClassHelper;

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;
use AppUtilsTestClasses\BaseTestCase;
use AppUtilsTestClasses\ClassHelper\ReferenceClasses\BaseFooClass;
use AppUtilsTestClasses\ClassHelper\ReferenceClasses\Foo\Argh;
use AppUtilsTestClasses\ClassHelper\ReferenceClasses\Foo\Bar;
use AppUtilsTestClasses\ClassHelper\ReferenceClasses\Foo\Foo;
use AppUtilsTestClasses\ClassHelper\ReferenceClasses\Foo\SillyName;
use stdClass;
use AppUtilsTestClasses\ClassHelper\TestStubClassInstanceOf;
use AppUtilsTestClasses\ClassHelper\Namespaced\TestStubNamespacedClass;
use AppUtilsTestClasses_ClassHelper_LegacyNaming_TestStubLegacyNamedClass;

final class ClassHelperTests extends BaseTestCase
{
    const PATH_REFERENCE_CLASSES = __DIR__ . '/../../AppUtilsTestClasses/ClassHelper/ReferenceClasses/Foo';

    public function test_getAutoLoader() : void
    {
        ClassHelper::getClassLoader();

        // No exception = all good
        $this->addToAssertionCount(1);
    }

    public function test_resolveClassName() : void
    {
        $this->assertTrue(class_exists(AppUtilsTestClasses_ClassHelper_LegacyNaming_TestStubLegacyNamedClass::class));
        $this->assertTrue(class_exists(TestStubNamespacedClass::class));

        $this->assertSame(
            AppUtilsTestClasses_ClassHelper_LegacyNaming_TestStubLegacyNamedClass::class,
            ClassHelper::resolveClassName('AppUtilsTestClasses_ClassHelper_LegacyNaming_TestStubLegacyNamedClass')
        );

        $this->assertSame(
            TestStubNamespacedClass::class,
            ClassHelper::resolveClassName('AppUtilsTestClasses\ClassHelper\Namespaced\TestStubNamespacedClass')
        );

        $this->assertSame(
            TestStubNamespacedClass::class,
            ClassHelper::resolveClassName('\AppUtilsTestClasses\ClassHelper\Namespaced\TestStubNamespacedClass')
        );

        $this->assertSame(
            stdClass::class,
            ClassHelper::resolveClassName('\stdClass')
        );

        $this->assertSame(
            BaseClassHelperException::class,
            ClassHelper::resolveClassName('ClassHelper\BaseClassHelperException', 'AppUtils')
        );
    }

    public function test_requireResolvedClass() : void
    {
        $this->expectExceptionCode(ClassHelper::ERROR_CANNOT_RESOLVE_CLASS_NAME);

        ClassHelper::requireResolvedClass('Unknown_Class_Name');
    }

    public function test_requireClassExists() : void
    {
        ClassHelper::requireClassExists(TestStubNamespacedClass::class);

        $this->addToAssertionCount(1);
    }

    public function test_requireClassExistsException() : void
    {
        $this->expectException(ClassNotExistsException::class);

        ClassHelper::requireClassExists('Unknown_Class_Name');
    }

    public function test_requireClassInstanceOf() : void
    {
        ClassHelper::requireClassInstanceOf(
            TestStubClassInstanceOf::class,
            TestStubNamespacedClass::class
        );

        $this->addToAssertionCount(1);
    }

    public function test_requireClassInstanceOfException() : void
    {
        $this->expectException(ClassNotImplementsException::class);

        ClassHelper::requireClassInstanceOf(
            TestStubNamespacedClass::class,
            TestStubClassInstanceOf::class
        );
    }

    public function test_requireObjectInstanceOf() : void
    {
        ClassHelper::requireObjectInstanceOf(
            TestStubNamespacedClass::class,
            new TestStubClassInstanceOf()
        );

        $this->addToAssertionCount(1);
    }

    public function test_requireObjectInstanceOfException() : void
    {
        $this->expectException(ClassNotImplementsException::class);

        ClassHelper::requireObjectInstanceOf(
            TestStubClassInstanceOf::class,
            new TestStubNamespacedClass()
        );
    }

    public function test_getClassTypeName() : void
    {
        $this->assertSame(
            'Namespace',
            ClassHelper::getClassTypeName('Class\With\Namespace')
        );

        $this->assertSame(
            'Underscores',
            ClassHelper::getClassTypeName('Class_Name_With_Underscores')
        );

        $this->assertSame(
            'ClassName',
            ClassHelper::getClassTypeName('ClassName')
        );
    }

    public function test_getClassNamespace() : void
    {
        $this->assertSame(
            '',
            ClassHelper::getClassNamespace('ClassName')
        );

        $this->assertSame(
            'AppUtils',
            ClassHelper::getClassNamespace('AppUtils\ClassName')
        );

        $this->assertSame(
            'AppUtils',
            ClassHelper::getClassNamespace('\AppUtils\ClassName')
        );

        $this->assertSame(
            'Mistralys\AppUtils',
            ClassHelper::getClassNamespace('Mistralys\AppUtils\ClassName')
        );
    }

    public function test_resolveClassByTemplate() : void
    {
        // This also tests that "Foo" being the class name and
        // in the namespace path do not conflict with each other
        // to build the target class name.
        $this->assertSame(
            Foo::class,
            ClassHelper::resolveClassByReference('Foo', Bar::class)
        );
    }

    public function test_getClassesInFolder() : void
    {
        $classes = ClassHelper::getClassesInFolder(
            FolderInfo::factory(self::PATH_REFERENCE_CLASSES),
            Foo::class
        );

        $this->assertSame(
            array(
                Argh::class,
                Bar::class,
                Foo::class,
                SillyName::class
            ),
            $classes
        );
    }

    /**
     * This test showcases a typical use case for loading
     * classes dynamically from a folder, using the file
     * names and one example class name to divine their names.
     */
    public function test_example_loadClassesFromFolder() : void
    {
        $fileIDs = FileHelper::createFileFinder(self::PATH_REFERENCE_CLASSES)
            ->getPHPClassNames();

        $classReference = Foo::class;

        echo 'Dynamically loaded classes:'.PHP_EOL;

        foreach($fileIDs as $fileID)
        {
            $class = ClassHelper::resolveClassByReference($fileID, $classReference);

            $instance = ClassHelper::requireObjectInstanceOf(
                BaseFooClass::class,
                new $class()
            );

            echo '- '.get_class($instance).PHP_EOL;
        }

        $this->addToAssertionCount(1);
    }
}
