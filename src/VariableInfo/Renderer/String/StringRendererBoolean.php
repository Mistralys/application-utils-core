<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer\String;

use AppUtils\VariableInfo\Renderer\BaseStringRenderer;

class StringRendererBoolean extends BaseStringRenderer
{
    private array $values = array(
        '0' => 'false',
        '1' => 'true',
        true => 'true',
        false => 'false',
        'true' => 'true',
        'false' => 'false',
        'yes' => 'true',
        'no' => 'false'
    );

    protected function _render() : string
    {
        if(is_string($this->value)) {
            $this->value = strtolower($this->value);
        }

        return $this->values[$this->value] ?? 'false';
    }
}
