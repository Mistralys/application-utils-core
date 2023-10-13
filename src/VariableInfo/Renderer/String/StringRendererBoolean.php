<?php

declare(strict_types=1);

namespace AppUtils\VariableInfo\Renderer\String;

use AppUtils\VariableInfo\Renderer\BaseStringRenderer;

class StringRendererBoolean extends BaseStringRenderer
{
    /**
     * @var array<int|bool|string,string>
     */
    private array $values = array(
        true => 'true', // Matches "0" and 0 as well
        false => 'false', // Matches "1" and 1 as well
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
