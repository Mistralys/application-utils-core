<?php
/**
 * @package Application Utils
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\Traits;

use AppUtils\OutputBuffering;
use AppUtilsTestClasses\BaseTestCase;
use AppUtilsTestClasses\RenderableBufferedTraitImpl;
use AppUtilsTestClasses\RenderableTraitImplException;
use AppUtilsTestClasses\RenderableTraitImpl;

/**
 * @package Application Utils
 * @subpackage UnitTests
 */
final class RenderableTests extends BaseTestCase
{
    public function test_render() : void
    {
        $renderable = new RenderableTraitImpl();

        $this->assertSame(RenderableTraitImpl::RENDERED_TEXT, $renderable->render());
        $this->assertSame(RenderableTraitImpl::RENDERED_TEXT, (string)$renderable);
    }

    public function test_display() : void
    {
        OutputBuffering::start();

        (new RenderableTraitImpl())->display();

        $this->assertSame(RenderableTraitImpl::RENDERED_TEXT, OutputBuffering::get());
    }

    /**
     * When using the magic method `__toString()`, no exceptions
     * may be called. The trait handles this with a try/catch block
     * and an error message returned instead.
     */
    public function test_exception() : void
    {
        $result = (string)(new RenderableTraitImplException());

        $this->assertStringContainsString(RenderableTraitImplException::EXCEPTION_MESSAGE, $result);
    }

    public function test_buffered() : void
    {
        $result = (string)(new RenderableBufferedTraitImpl());

        $this->assertSame(RenderableBufferedTraitImpl::RENDERED_TEXT, $result);
    }
}
