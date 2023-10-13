<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer\String;

use AppUtils\VariableInfo\Renderer\BaseStringRenderer;

class StringRendererInteger extends BaseStringRenderer
{
    protected function _render() : string
    {
        return (string)$this->value;
    }
}
