<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer\String;

use AppUtils\VariableInfo\Renderer\BaseStringRenderer;

class StringRendererObject extends BaseStringRenderer
{
    protected function _render() : string
    {
        if($this->value !== null) {
            return get_class($this->value);
        }

        return '';
    }
}
