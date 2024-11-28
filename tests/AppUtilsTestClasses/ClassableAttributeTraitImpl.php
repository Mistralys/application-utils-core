<?php

declare(strict_types=1);

namespace AppUtilsTestClasses;

use AppUtils\AttributeCollection;
use AppUtils\Interfaces\ClassableAttributeInterface;
use AppUtils\Traits\ClassableAttributeTrait;

final class ClassableAttributeTraitImpl implements ClassableAttributeInterface
{
    use ClassableAttributeTrait;

    private AttributeCollection $attributes;

    public function __construct()
    {
        $this->attributes = AttributeCollection::create();
    }

    public function getAttributes(): AttributeCollection
    {
        return $this->attributes;
    }
}
