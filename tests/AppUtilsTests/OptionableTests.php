<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtilsTestClasses\BaseTestCase;
use AppUtilsTestClasses\OptionableTraitImpl;

class OptionableTests extends BaseTestCase
{
    public function test_optionExists() : void
    {
        $optionable = new OptionableTraitImpl();

        $this->assertTrue($optionable->hasOption(OptionableTraitImpl::OPTION_BOOL_TRUE));
        $this->assertFalse($optionable->hasOption(OptionableTraitImpl::OPTION_NOT_EXISTS));
    }

    public function test_getOptionBool() : void
    {
        $optionable = new OptionableTraitImpl();

        $this->assertTrue($optionable->getBoolOption(OptionableTraitImpl::OPTION_BOOL_TRUE));
        $this->assertFalse($optionable->getBoolOption(OptionableTraitImpl::OPTION_BOOL_FALSE));
    }

    public function test_getOptionInt() : void
    {
        $optionable = new OptionableTraitImpl();

        $this->assertSame(0, $optionable->getIntOption(OptionableTraitImpl::OPTION_INT_0));
        $this->assertSame(42, $optionable->getIntOption(OptionableTraitImpl::OPTION_INT_42));
    }

    public function test_getOptionString() : void
    {
        $optionable = new OptionableTraitImpl();

        $this->assertTrue($optionable->hasOption(OptionableTraitImpl::OPTION_STRING_EMPTY));
        $this->assertSame('', $optionable->getStringOption(OptionableTraitImpl::OPTION_STRING_EMPTY));
        $this->assertSame('foo', $optionable->getOption(OptionableTraitImpl::OPTION_STRING_FOO));
        $this->assertSame('foo', $optionable->getStringOption(OptionableTraitImpl::OPTION_STRING_FOO));
    }

    public function test_getArrayOption() : void
    {
        $optionable = new OptionableTraitImpl();

        $this->assertTrue($optionable->hasOption(OptionableTraitImpl::OPTION_ARRAY_EMPTY));
        $this->assertSame(array(), $optionable->getArrayOption(OptionableTraitImpl::OPTION_ARRAY_EMPTY));

        $this->assertTrue($optionable->hasOption(OptionableTraitImpl::OPTION_ARRAY_STRINGS));
        $this->assertSame(array('foo', 'bar', '', null), $optionable->getArrayOption(OptionableTraitImpl::OPTION_ARRAY_STRINGS));
        $this->assertSame(array('foo', 'bar'), $optionable->getArrayAdvanced()->getStringsIndexed(OptionableTraitImpl::OPTION_ARRAY_STRINGS));

        $this->assertTrue($optionable->hasOption(OptionableTraitImpl::OPTION_ARRAY_ASSOC));
        $this->assertSame(array('foo' => 'bar'), $optionable->getArrayOption(OptionableTraitImpl::OPTION_ARRAY_ASSOC));
    }
}
