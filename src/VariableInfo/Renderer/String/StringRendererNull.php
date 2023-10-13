<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer\String;

use AppUtils\VariableInfo\Renderer\BaseStringRenderer;

class StringRendererNull extends BaseStringRenderer
{
    protected function _render() : string
    {
        return 'null';
    }
}
