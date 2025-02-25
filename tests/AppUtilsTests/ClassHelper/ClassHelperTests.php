<?php

declare(strict_types=1);

namespace AppUtilsTests\ClassHelper;

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper_PHPClassInfo_Class;
use AppUtilsTestClasses\BaseTestCase;
use AppUtilsTestClasses\ClassHelper\ClassFinder\ExtendsBase\FinderSubclassExtendsBaseClass;
use AppUtilsTestClasses\ClassHelper\ClassFinder\FinderBaseClass;
use AppUtilsTestClasses\ClassHelper\ClassFinder\FinderExtendsBaseClass;
use AppUtilsTestClasses\ClassHelper\ClassFinder\FinderImplementsInterface;
use AppUtilsTestClasses\ClassHelper\ClassFinder\FinderInterface;
use AppUtilsTestClasses\ClassHelper\ClassFinder\FinderNoExtends;
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
    private const PATH_REFERENCE_CLASSES = __DIR__ . '/../../AppUtilsTestClasses/ClassHelper/ReferenceClasses/Foo';
    private const FINDER_CLASSES_FOLDER = __DIR__ . '/../../AppUtilsTestClasses/ClassHelper/ClassFinder';

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
            ->getFiles()
            ->PHPClassNames();

        $classReference = Foo::class;

        foreach($fileIDs as $fileID)
        {
            $class = ClassHelper::resolveClassByReference($fileID, $classReference);

            ClassHelper::requireObjectInstanceOf(
                BaseFooClass::class,
                new $class()
            );

            $this->addToAssertionCount(1);
        }
    }

    public function test_findAllClassesInFolder() : void
    {
        $classes = ClassHelper::findClassesInFolder(FolderInfo::factory(self::FINDER_CLASSES_FOLDER));

        $this->assertClassListContainsClasses(
            $classes,
            FinderBaseClass::class,
            FinderExtendsBaseClass::class,
            FinderImplementsInterface::class,
            FinderInterface::class,
            FinderNoExtends::class
        );
    }

    public function test_findAllClassesRecursive() : void
    {
        $classes = ClassHelper::findClassesInFolder(FolderInfo::factory(self::FINDER_CLASSES_FOLDER), true);

        $this->assertClassListContainsClass($classes, FinderSubclassExtendsBaseClass::class);
    }

    public function test_findClassesInstanceOf() : void
    {
        $classes = ClassHelper::findClassesInFolder(
            FolderInfo::factory(self::FINDER_CLASSES_FOLDER),
            true,
            FinderInterface::class
        );

        $this->assertCount(2, $classes);
        $this->assertClassListContainsClass($classes, FinderInterface::class);
        $this->assertClassListContainsClass($classes, FinderImplementsInterface::class);
    }

    public function test_findClassesCached() : void
    {
        ClassHelper::setCacheFolder(__DIR__.'/../../assets/ClassHelper/ClassRepository');

        $classes = ClassHelper::findClassesInRepository(
            FolderInfo::factory(self::FINDER_CLASSES_FOLDER),
            true,
            FinderInterface::class
        )->getClasses();

        $this->assertCount(2, $classes);
        $this->assertContains(FinderInterface::class, $classes);
        $this->assertContains(FinderImplementsInterface::class, $classes);
    }

    /**
     * @param FileHelper_PHPClassInfo_Class[] $list
     * @param class-string ...$classes
     * @return void
     */
    private function assertClassListContainsClasses(array $list, ...$classes) : void
    {
        foreach($classes as $class) {
            $this->assertClassListContainsClass($list, $class);
        }
    }

    /**
     * @param FileHelper_PHPClassInfo_Class[] $list
     * @param class-string $class
     * @return void
     */
    private function assertClassListContainsClass(array $list, string $class) : void
    {
        $names = array();
        foreach($list as $classInfo) {
            $name = $classInfo->getNameNS();
            $names[] = $name;
            if($name === $class) {
                $this->addToAssertionCount(1);
                return;
            }
        }

        $this->fail(sprintf(
            'Class [%s] not found in list. '.PHP_EOL.
            'Classes present are: '.PHP_EOL.
            '- %s',
            $class,
            implode(PHP_EOL.'- ', $names)
        ));
    }
}
