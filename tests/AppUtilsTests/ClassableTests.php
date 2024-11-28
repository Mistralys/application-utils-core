<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtilsTestClasses\BaseTestCase;
use AppUtilsTestClasses\ClassableAttributeTraitImpl;
use AppUtilsTestClasses\ClassableTraitImpl;

final class ClassableTests extends BaseTestCase
{
    public function test_classableTrait() : void
    {
        $classable = new ClassableTraitImpl();

        $classable->addClass('foo');
        $classable->addClasses(array('bar', 'baz'));

        $this->assertSame('bar baz foo', $classable->classesToString());
    }

    public function test_classableAttributeTrait() : void
    {
        $classable = new ClassableAttributeTraitImpl();

        $classable->addClass('foo');
        $classable->addClasses(array('bar', 'baz'));

        $this->assertSame('bar baz foo', $classable->classesToString());
    }
}
