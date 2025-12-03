<?php
/**
 * File containing the {@see AppUtils\ConvertHelper_TimeConverter} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see AppUtils\ConvertHelper_TimeConverter
 */

declare(strict_types=1);

namespace AppUtils\DateTimeHelper;

use function AppUtils\t;

/**
 * Converts seconds to a human-readable duration string,
 * like "5 minutes and 20 seconds".
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see ConvertHelper::time2string()
 */
class TimeConverter
{
    private float $seconds;

   /**
    * @var array<int,array<string,string|int>>|NULL
    */
    private static ?array $units = null;
    
   /**
    * @param float|int|string $seconds
    */
    public function __construct(float|int|string $seconds)
    {
        if(!is_numeric($seconds)) {
            throw new DateTimeException(
                'Invalid time value.',
                sprintf(
                    'The string [%s] is not a valid numeric value.',
                    $seconds
                ),
                DateTimeException::ERROR_INVALID_STRING_TIME_VALUE
            );
        }

        $this->seconds = (float)$seconds;
        
        $this->initUnits();
    }
    
   /**
    * Creates the list of units once per request as needed.
    */
    private function initUnits() : void
    {
        if(isset(self::$units))
        {
            return;
        }
        
        self::$units = array(
            array(
                'value' => 31 * 7 * 24 * 3600,
                'singular' => t('month'),
                'plural' => t('months')
            ),
            array(
                'value' => 7 * 24 * 3600,
                'singular' => t('week'),
                'plural' => t('weeks')
            ),
            array(
                'value' => 24 * 3600,
                'singular' => t('day'),
                'plural' => t('days')
            ),
            array(
                'value' => 3600,
                'singular' => t('hour'),
                'plural' => t('hours')
            ),
            array(
                'value' => 60,
                'singular' => t('minute'),
                'plural' => t('minutes')
            ),
            array(
                'value' => 1,
                'singular' => t('second'),
                'plural' => t('seconds')
            )
        );
    }
    
    public function toString() : string
    {
        // Handle zero explicitly
        if($this->seconds <= 0) 
        {
            return '0 ' . t('seconds');
        }
        
        if($this->seconds < 1) 
        {
            return t('less than a second');
        }
        
        $tokens = $this->resolveTokens();

        $last = array_pop($tokens);
        
        if(empty($tokens)) 
        {
            return $last;
        }
        
        return implode(', ', $tokens) . ' ' . t('and') . ' ' . $last;
    }
    
   /**
    * Resolves the list of converted units.
    * 
    * @return string[]
    */
    private function resolveTokens() : array
    {
        $seconds = $this->seconds;
        $tokens = array();
        
        foreach(self::$units as $def)
        {
            $unitValue = intval($seconds / (float)$def['value']);
            
            if($unitValue <= 0)
            {
                continue;
            }
            
            $item = $unitValue . ' ';
            
            if(abs($unitValue) > 1)
            {
                $item .= $def['plural'];
            }
            else
            {
                $item .= $def['singular'];
            }
            
            $tokens[] = $item;
            
            $seconds -= $unitValue * (float)$def['value'];
        }
        
        return $tokens;
    }
}
