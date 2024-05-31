<?php
/**
 * @package Application Utils
 * @subpackage StringHelper
 */

declare(strict_types=1);

namespace AppUtils\StringHelper;

/**
 * Query parser that works as a drop-in for the native
 * PHP function parse_str, and which overcomes this function's
 * limitations.
 *
 * @package Application Utils
 * @subpackage StringHelper
 * @see https://www.php.net/manual/en/function.parse-str.php
 */
class QueryParser
{
   /**
    * We parse the query string ourselves, because the PHP implementation
    * of parse_str has limitations that do not apply to query strings. This
    * is because <code>parse_str</code> has to create PHP-compatible variable
    * names from the parameters. URL parameters simply allow way more things
    * than PHP variable names.
    * 
    * @param string $queryString
    * @return array<string,string>
    */
    public function parse(string $queryString) : array
    {
        // allow HTML entities notation
        $queryString = str_replace('&amp;', '&', $queryString);
        
        $parts = explode('&', $queryString);
        
        $result = array();
        
        foreach($parts as $part)
        {
            $tokens = explode('=', $part);
            
            $name = urldecode(array_shift($tokens));
            $value = urldecode(implode('=', $tokens));

            $trimmed = trim($name);
            
            if(empty($trimmed))
            {
                continue;
            }
            
            $result[$name] = $value;
        }
        
        return $result;
    }
}
