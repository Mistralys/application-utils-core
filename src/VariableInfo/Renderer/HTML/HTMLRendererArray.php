<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer\HTML;

use AppUtils\VariableInfo\Renderer\BaseHTMLRenderer;

class HTMLRendererArray extends BaseHTMLRenderer
{
    protected function _render() : string
    {
        $json = $this->info->toString();
        $json = $this->cutString($json);
        $json = nl2br($json);
        
        return $json;
    }
}
