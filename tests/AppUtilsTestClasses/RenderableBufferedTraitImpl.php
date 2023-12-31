<?php
/**
 * @package Application Utils
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace AppUtilsTestClasses;

use AppUtils\Interfaces\RenderableInterface;
use AppUtils\Traits\RenderableBufferedTrait;

/**
 * @package Application Utils
 * @subpackage UnitTests
 */
class RenderableBufferedTraitImpl implements RenderableInterface
{
    use RenderableBufferedTrait;

    public const RENDERED_TEXT = 'This is buffered output';

    protected function generateOutput() : void
    {
        echo self::RENDERED_TEXT;
    }
}
