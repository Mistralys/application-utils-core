<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer\String;

use AppUtils\VariableInfo\Renderer\BaseStringRenderer;

class StringRendererUnknowntype extends BaseStringRenderer
{
    protected function _render() : string
    {
        return 'unknown type';
    }
}
