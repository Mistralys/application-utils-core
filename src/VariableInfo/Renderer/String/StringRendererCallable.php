<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer\String;

use AppUtils\NamedClosure;
use AppUtils\VariableInfo\Renderer\BaseStringRenderer;
use Closure;

class StringRendererCallable extends BaseStringRenderer
{
    protected function _render() : string
    {
        $string = '';

        // Simple function call
        if(is_string($this->value))
        {
            return $this->value.'()';
        }

        if(is_array($this->value)) {
            return $this->renderArray();
        }

        if($this->value instanceof NamedClosure) {
            return 'Closure:'.$this->value->getOrigin();
        }

        if($this->value instanceof Closure) {
            return 'Closure';
        }

        return $string;
    }

    private function renderArray() : string
    {
        $string = '';

        if (is_string($this->value[0])) {
            $string .= $this->value[0] . '::';
        } else {
            $string .= get_class($this->value[0]) . '->';
        }

        $string .= $this->value[1].'()';

        return $string;
    }
}
