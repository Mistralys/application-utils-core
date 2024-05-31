<?php
/**
 * @package Application Utils
 * @subpackage StringHelper
 */

declare(strict_types=1);

namespace AppUtils\StringHelper;

/**
 * Container for an individual occurrence of a string
 * that was found in a haystack using the method
 * {@link ConvertHelper::findString()}.
 *  
 * @package Application Utils
 * @subpackage StringHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see ConvertHelper::findString()
 */
class StringMatch
{
    protected int $position;
    protected string $match;
    
    public function __construct(int $position, string $matchedString)
    {
        $this->position = $position;
        $this->match = $matchedString;
    }
    
   /**
    * The zero-based start position of the string in the haystack.
    * @return int
    */
    public function getPosition() : int
    {
        return $this->position;
    }
    
   /**
    * The exact string that was matched, respecting the case as found in needle.
    * @return string
    */
    public function getMatchedString() : string
    {
        return $this->match;
    }
}
