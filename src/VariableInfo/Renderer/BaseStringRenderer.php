<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer;

use AppUtils\VariableInfo\VariableRenderer;

abstract class BaseStringRenderer extends VariableRenderer
{
    protected function init() : void
    {
        $this->value = $this->info->getValue();
    }
}
