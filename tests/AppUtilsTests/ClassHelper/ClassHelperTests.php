<?php

declare(strict_types=1);

namespace AppUtilsTests\ClassHelper;

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtilsTestClasses\BaseTestCase;
use stdClass;
use AppUtilsTestClasses\ClassHelper\TestStubClassInstanceOf;
use AppUtilsTestClasses\ClassHelper\Namespaced\TestStubNamespacedClass;
use AppUtilsTestClasses_ClassHelper_LegacyNaming_TestStubLegacyNamedClass;

final class ClassHelperTests extends BaseTestCase
{
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
}
