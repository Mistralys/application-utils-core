<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer\HTML;

use AppUtils\VariableInfo\Renderer\BaseHTMLRenderer;

class HTMLRendererString extends BaseHTMLRenderer
{
    protected function _render() : string
    {
        $string = $this->info->toString();
        $string = $this->cutString($string);
        $string = nl2br(htmlspecialchars($string));
        
        return '&quot;'.$string.'&quot;';
    }
}
