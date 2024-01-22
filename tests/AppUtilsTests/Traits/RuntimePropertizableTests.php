<?php

declare(strict_types=1);

namespace AppUtilsTests\Traits;

use AppUtilsTestClasses\BaseTestCase;
use AppUtilsTestClasses\RuntimePropertizableTraitImpl;

final class RuntimePropertizableTests extends BaseTestCase
{
    public function test_getSetProperties(): void
    {
        $obj = new RuntimePropertizableTraitImpl();

        $this->assertFalse($obj->hasRuntimeProperty('foo'));

        $obj->setRuntimeProperty('foo', 'bar');

        $this->assertTrue($obj->hasRuntimeProperty('foo'));
        $this->assertSame('bar', $obj->getRuntimePropertyRaw('foo'));
        $this->assertSame('bar', $obj->getRuntimeProperty('foo')->getString());
    }

    public function test_clearProperties(): void
    {
        $obj = new RuntimePropertizableTraitImpl();

        $this->assertFalse($obj->hasRuntimeProperty('foo'));

        $obj->setRuntimeProperty('foo', 'bar');

        $this->assertTrue($obj->hasRuntimeProperty('foo'));

        $obj->clearRuntimeProperties();

        $this->assertFalse($obj->hasRuntimeProperty('foo'));
    }
}
