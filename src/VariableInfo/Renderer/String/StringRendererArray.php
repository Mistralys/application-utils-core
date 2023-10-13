<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer\String;

use AppUtils\VariableInfo\Renderer\BaseStringRenderer;
use function AppUtils\parseVariable;

class StringRendererArray extends BaseStringRenderer
{
    protected function _render() : string
    {
        $result = $this->traverseArray($this->value);
        
        return print_r($result, true);
    }

    /**
     * @param array<mixed> $array
     * @param int $level
     * @return array<mixed>
     */
    protected function traverseArray(array $array, int $level=0) : array
    {
        $result = array();
        
        foreach($array as $key => $val)
        {
            if(is_array($val))
            {
                $result[$key] = $this->traverseArray($val, ($level+1));
            }
            else
            {
                $result[$key] = parseVariable($val)->toString();
            }
        }
        
        return $result;
    }
}
