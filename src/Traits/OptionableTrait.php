<?php
/**
 * @package Application Utils
 * @subpackage Traits
 * @see \AppUtils\Traits\OptionableTrait
 */

namespace AppUtils\Traits;

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\OptionableTrait\ArrayAdvancedOption;
use AppUtils\Traits\OptionableTrait\OptionableException;

/**
 * Trait for adding options to a class: allows setting
 * and getting options of all types.
 *
 * NOTE: To add this to a class, it must use the trait,
 * but also implement the interface.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see OptionableInterface
 */
trait OptionableTrait
{
    /**
     * @var array<string,mixed>|null
     */
    protected ?array $optionDefaults = null;

    /**
     * @var array<string,mixed>
     */
    protected array $options = array();

    /**
     * Sets an option to the specified value. This can be any
     * kind of variable type, including objects, as needed.
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setOption(string $name, $value) : self
    {
        $this->options[$name] = $value;
        return $this;
    }

    /**
     * Sets a collection of options at once, from an
     * associative array.
     *
     * @param array<string,mixed> $options
     * @return $this
     */
    public function setOptions(array $options) : self
    {
        foreach($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this;
    }

    /**
     * Retrieves an option's value.
     *
     * NOTE: Use the specialized type getters to ensure an option
     * contains the expected type (for ex. getArrayOption()).
     *
     * @param string $name
     * @param mixed $default The default value to return if the option does not exist.
     * @return mixed
     */
    public function getOption(string $name, $default=null)
    {
        return $this->options[$name] ?? $default ?? $this->getOptionDefault($name);
    }

    /**
     * Enforces that the option value is a string. Numbers are converted
     * to string, strings are passed through, and all other types will
     * return the default value. The default value is also returned if
     * the string is empty.
     *
     * @param string $name
     * @param string $default Used if the option does not exist, or is invalid, or empty.
     * @return string
     */
    public function getStringOption(string $name, string $default='') : string
    {
        $value = $this->getOption($name);

        if((is_string($value) || is_numeric($value)) && !empty($value)) {
            return (string)$value;
        }

        return $default;
    }

    /**
     * Like {@see self::getStringOption()}, but guarantees
     * that the returned string is non-empty. An exception
     * is thrown if the option is empty or invalid.
     *
     * @param string $name
     * @param string $default
     * @return non-empty-string
     *
     * @throws OptionableException {@see OptionableException::ERROR_INVALID_OPTION_VALUE}
     */
    public function getStringOptionNE(string $name, string $default='') : string
    {
        $value = $this->getStringOption($name, $default);
        if(!empty($value)) {
            return $value;
        }

        throw new OptionableException(
            'Option must be a non-empty string.',
            sprintf(
                'The option "%s" is not a non-empty string.',
                $name
            ),
            OptionableException::ERROR_INVALID_OPTION_VALUE
        );
    }

    /**
     * Treats the option value as a boolean value: will return
     * true if the value actually is a boolean true.
     *
     * NOTE: boolean string representations are not accepted.
     *
     * @param string $name
     * @param bool $default
     * @return bool
     */
    public function getBoolOption(string $name, bool $default=false) : bool
    {
        if($this->getOption($name) === true)
        {
            return true;
        }

        return $default;
    }

    /**
     * Treats the option value as an integer value: will return
     * valid integer values (also from integer strings), or the
     * default value otherwise.
     *
     * @param string $name
     * @param int $default
     * @return int
     */
    public function getIntOption(string $name, int $default=0) : int
    {
        $value = $this->getOption($name);

        if(is_numeric($value)) {
            return (int)$value;
        }

        return $default;
    }

    /**
     * Treats an option as an array, and returns its value
     * only if it contains an array - otherwise, an empty
     * array is returned.
     *
     * @param string $name
     * @return array<int|string,mixed>
     */
    public function getArrayOption(string $name) : array
    {
        $val = $this->getOption($name);
        if(is_array($val)) {
            return $val;
        }

        return array();
    }

    private ?ArrayAdvancedOption $arrayAdvancedOption = null;

    /**
     * Returns the advanced array option handler, which
     * has specialized methods for handling array options.
     *
     * @return ArrayAdvancedOption
     */
    public function getArrayAdvanced() : ArrayAdvancedOption
    {
        if(!isset($this->arrayAdvancedOption)) {
            $this->arrayAdvancedOption = new ArrayAdvancedOption($this);
        }

        return $this->arrayAdvancedOption;
    }

    /**
     * Checks whether the specified option exists - even
     * if it has a NULL value.
     *
     * @param string $name
     * @return bool
     */
    public function hasOption(string $name) : bool
    {
        if(array_key_exists($name, $this->options)) {
            return true;
        }

        if(!isset($this->optionDefaults)) {
            $this->optionDefaults = $this->getDefaultOptions();
        }

        return array_key_exists($name, $this->optionDefaults);
    }

    /**
     * Returns all options in one associative array.
     *
     * @return array<string,mixed>
     */
    public function getOptions() : array
    {
        if(!isset($this->optionDefaults)) {
            $this->optionDefaults = $this->getDefaultOptions();
        }

        return array_merge($this->optionDefaults, $this->options);
    }

    /**
     * Checks whether the option's value is the one specified.
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function isOption(string $name, $value) : bool
    {
        return $this->getOption($name) === $value;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setOptionDefault(string $name, $value) : self
    {
        if(!isset($this->optionDefaults)) {
            $this->optionDefaults = $this->getDefaultOptions();
        }

        $this->optionDefaults[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return mixed|NULL
     */
    public function getOptionDefault(string $name)
    {
        if(!isset($this->optionDefaults)) {
            $this->optionDefaults = $this->getDefaultOptions();
        }

        return $this->optionDefaults[$name] ?? null;
    }
}
