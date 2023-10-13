<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo;

use AppUtils\VariableInfo;

abstract class VariableRenderer
{
   /**
    * @var mixed|NULL
    */
    protected $value;
    
    protected VariableInfo $info;
    protected string $type;
    
    public function __construct(VariableInfo $info)
    {
        $this->info = $info;
        $this->type = $info->getType();
        
        $this->init();
    }
    
    abstract protected function init() : void;

   /**
    * Renders the value to the target format.
    * 
    * @return string
    */
    public function render() : string
    {
        return $this->_render();
    }
    
    abstract protected function _render() : string;

    protected function cutString(string $string) : string
    {
        $cutAt = $this->info->getIntOption('cut-length', 1000);

        if(mb_strlen($string) >= $cutAt) {
            return mb_substr($string, 0, $cutAt).' [...]';
        }

        return $string;
    }
}
