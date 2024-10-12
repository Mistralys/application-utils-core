<?php

declare(strict_types=1);

namespace AppUtilsTestClasses;

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\OptionableTrait;

class OptionableTraitImpl implements OptionableInterface
{
    use OptionableTrait;

    public const OPTION_BOOL_TRUE = 'optionBoolTrue';
    public const OPTION_BOOL_FALSE = 'optionBoolFalse';
    public const OPTION_INT_0 = 'optionInt0';
    public const OPTION_INT_42 = 'optionInt42';
    public const OPTION_NULL = 'optionNull';
    public const OPTION_STRING_EMPTY = 'optionStringEmpty';
    public const OPTION_STRING_FOO = 'optionStringFoo';
    public const OPTION_NOT_EXISTS = 'optionNotExists';
    public const OPTION_ARRAY_EMPTY = 'optionArrayEmpty';
    public const OPTION_ARRAY_STRINGS = 'optionArrayStrings';
    public const OPTION_ARRAY_ASSOC = 'optionArrayAssoc';
    public const VALUE_ARRAY_STRINGS = array('foo', 'bar', '', null);

    public function getDefaultOptions(): array
    {
        return array(
            self::OPTION_BOOL_TRUE => true,
            self::OPTION_BOOL_FALSE => false,
            self::OPTION_INT_0 => 0,
            self::OPTION_INT_42 => 42,
            self::OPTION_NULL => null,
            self::OPTION_STRING_EMPTY => '',
            self::OPTION_STRING_FOO => 'foo',
            self::OPTION_ARRAY_EMPTY => array(),
            self::OPTION_ARRAY_STRINGS => self::VALUE_ARRAY_STRINGS,
            self::OPTION_ARRAY_ASSOC => array('foo' => 'bar')
        );
    }
}
