<?php
/**
 * File containing the {@see \AppUtils\StringHelper\WordWrapper} class.
 * 
 * @package Application Utils
 * @subpackage StringHelper
 * @see \AppUtils\StringHelper\WordWrapper
 */

declare(strict_types=1);

namespace AppUtils\StringHelper;

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\OptionableTrait;

/**
 * Wordwrap class that is used to wordwrap texts.
 * 
 * @package Application Utils
 * @subpackage StringHelper
 * @see https://stackoverflow.com/a/4988494/2298192
 */
class WordWrapper implements OptionableInterface
{
    use OptionableTrait;
    
    public function __construct()
    {
        
    }
    
    public function getDefaultOptions() : array
    {
        return array(
            'width' => 75,
            'break' => "\n",
            'cut' => false
        );
    }
    
    public function setLineWidth(int $width) : WordWrapper
    {
        $this->setOption('width', $width);
        return $this;
    }
    
    public function getLineWidth() : int
    {
        return $this->getIntOption('width');
    }
    
    public function setBreakCharacter(string $char) : WordWrapper
    {
        $this->setOption('break', $char);
        return $this;
    }
    
    public function getBreakCharacter() : string
    {
        return $this->getStringOption('break');
    }
    
    public function isCuttingEnabled() : bool
    {
        return $this->getBoolOption('cut');
    }
    
    public function setCuttingEnabled(bool $enabled=true) : WordWrapper
    {
        $this->setOption('cut', $enabled);
        return $this;
    }
    
    public function wrapText(string $text) : string
    {
        $break = $this->getBreakCharacter();
        $width = $this->getLineWidth();
        $cut = $this->isCuttingEnabled();
        
        $lines = explode($break, $text);
        
        foreach ($lines as &$line)
        {
            $line = rtrim($line);
            if (mb_strlen($line) <= $width) {
                continue;
            }
            
            $words = explode(' ', $line);
            $line = '';
            $actual = '';
            foreach ($words as $word)
            {
                if (mb_strlen($actual.$word) <= $width)
                {
                    $actual .= $word.' ';
                }
                else
                {
                    if ($actual != '') {
                        $line .= rtrim($actual).$break;
                    }
                    
                    $actual = $word;
                    if ($cut)
                    {
                        while (mb_strlen($actual) > $width) {
                            $line .= mb_substr($actual, 0, $width).$break;
                            $actual = mb_substr($actual, $width);
                        }
                    }
                    
                    $actual .= ' ';
                }
            }
            
            $line .= trim($actual);
        }
        
        return implode($break, $lines);
    }
}
