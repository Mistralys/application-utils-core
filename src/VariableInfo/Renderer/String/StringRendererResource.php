<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer\String;

use AppUtils\VariableInfo\Renderer\BaseStringRenderer;

class StringRendererResource extends BaseStringRenderer
{
    protected function _render() : string
    {
        $string = (string)$this->value;
        $string = substr($string, strpos($string, '#'));
        
        return $string;
    }
}
