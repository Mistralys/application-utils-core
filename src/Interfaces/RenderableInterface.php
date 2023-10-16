<?php
/**
 * @package Application Utils
 * @subpackage Traits
 * @see \AppUtils\Interfaces\RenderableInterface
 */

declare(strict_types=1);

namespace AppUtils\Interfaces;

use AppUtils\Traits\RenderableTrait;

/**
 * Interface for classes that can be rendered to string.
 *
 * @package Application Utils
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see RenderableTrait
 */
interface RenderableInterface extends StringableInterface
{
    public function render() : string;

    public function display() : void;
}
