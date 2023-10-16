<?php
/**
 * File containing the {@link StringBuilder_Interface} interface.
 *
 * @package Application Utils
 * @subpackage StringBuilder
 * @see StringBuilder_Interface
 */

namespace AppUtils;

use AppUtils\Interfaces\StringableInterface;

/**
 * Interface for the StringBuilder class.
 *
 * @package Application Utils
 * @subpackage StringBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see StringBuilder
 */
interface StringBuilder_Interface extends StringableInterface
{
    /**
     * Renders the string builder to a string.
     * 
     * @return string
     */
     function render() : string;
     
    /**
     * Renders the string and echos it.
     */
     function display() : void;
}
