<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer\HTML;

use AppUtils\VariableInfo\Renderer\BaseHTMLRenderer;

class HTMLRendererObject extends BaseHTMLRenderer
{
    protected function _render() : string
    {
        return $this->info->toString();
    }
}
