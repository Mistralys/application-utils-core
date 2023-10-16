<?php

declare(strict_types=1);

namespace AppUtilsTestClasses;

use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Traits\ClassableTrait;

class ClassableTraitImpl implements ClassableInterface
{
    use ClassableTrait;
}
