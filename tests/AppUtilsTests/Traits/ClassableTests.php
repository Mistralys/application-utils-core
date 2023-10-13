<?php

declare(strict_types=1);

namespace AppUtilsTests\Traits;

use PHPUnit\Framework\TestCase;
use AppUtilsTestClasses\ClassableTraitImpl;

final class ClassableTests extends TestCase
{
    protected ClassableTraitImpl $subject;

    protected function setUp(): void
    {
        $this->subject = new ClassableTraitImpl();
    }

    public function test_addClass(): void
    {
        $this->subject->addClass('foo');

        $this->assertEquals(array('foo'), $this->subject->getClasses());
    }

    public function test_addClasses(): void
    {
        $this->subject->addClasses(array('foo', 'bar'));

        $this->assertEquals(array('foo', 'bar'), $this->subject->getClasses());
    }

    public function test_removeClass(): void
    {
        $this->subject->addClasses(array('foo', 'bar'));

        $this->subject->removeClass('foo');

        $this->assertEquals(array('bar'), $this->subject->getClasses());
    }

    public function test_hasClass(): void
    {
        $this->subject->addClass('foo');

        $this->assertTrue($this->subject->hasClass('foo'));
    }

    public function test_classesToString(): void
    {
        $this->subject->addClasses(array('foo', 'bar'));

        $this->assertEquals('foo bar', $this->subject->classesToString());
    }

    public function test_classesToAttribute(): void
    {
        $this->subject->addClasses(array('foo', 'bar'));

        $this->assertEquals(' class="foo bar" ', $this->subject->classesToAttribute());
    }
}
