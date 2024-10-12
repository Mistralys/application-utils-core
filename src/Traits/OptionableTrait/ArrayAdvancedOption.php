<?php
/**
 * @package Application Utils
 * @subpackage Traits
 */

declare(strict_types=1);

namespace AppUtils\Traits\OptionableTrait;

use AppUtils\Interfaces\OptionableInterface;

/**
 * Type-specific option handling for array option values.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ArrayAdvancedOption
{
    private OptionableInterface $optionable;

    public function __construct(OptionableInterface $optionable)
    {
        $this->optionable = $optionable;
    }

    /**
     * @param string $name
     * @param bool $pruneEmpty Whether to remove empty strings from the list.
     * @return string[]
     */
    public function getStringsIndexed(string $name, bool $pruneEmpty=true) : array
    {
        $list = array();

        foreach($this->optionable->getArrayOption($name) as $value) {
            $string = $this->toString($value);

            if($pruneEmpty === true && empty($string)) {
                continue;
            }

            $list[] = $string;
        }

        return $list;
    }

    private function toString($subject) : ?string
    {
        $string = '';
        if(is_scalar($subject)) {
            $string = (string)$subject;
        }

        if(!empty($string)) {
            return $string;
        }

        return null;
    }

    /**
     * @param string $name
     * @return int[]
     */
    public function getIntegersIndexed(string $name) : array
    {
        $list = array();

        foreach($this->optionable->getArrayOption($name) as $value) {
            $string = 0;
            if(is_numeric($value)) {
                $string = (int)$value;
            }

            $list[] = $string;
        }

        return $list;
    }

    /**
     * @param string $name
     * @param bool $pruneEmpty Whether to remove empty values from the list.
     * @return array<string,string>
     */
    public function getStringAndString(string $name, bool $pruneEmpty=false) : array
    {
        $list = array();

        foreach($this->optionable->getArrayOption($name) as $key => $value) {
            $string = $this->toString($value);

            if($pruneEmpty === true && empty($string)) {
                continue;
            }

            $list[(string)$key] = $string;
        }

        return $list;
    }
}
