<?php

declare(strict_types=1);

namespace AppUtilsTests\JSHelper;

use AppUtils\JSHelper;
use AppUtilsTestClasses\BaseTestCase;

final class QuoteConversionTests extends BaseTestCase
{
    public function test_singleToDouble() : void
    {
        $source = <<<'EOT'
alert('Some text');
EOT;

        $expected = <<<'EOT'
alert("Some text");
EOT;

        $this->assertSame($expected, JSHelper::quoteStyle($source)->singleToDouble());
    }

    public function test_singleToDoubleNested() : void
    {
        $source = <<<'EOT'
alert('Some "single" text');
EOT;

        $expected = <<<'EOT'
alert("Some \"single\" text");
EOT;

        $this->assertSame($expected, JSHelper::quoteStyle($source)->singleToDouble());
    }

    public function test_singleToDoubleNestedPreserved() : void
    {
        $source = <<<'EOT'
alert('Some "single" text');
EOT;

        $expected = <<<'EOT'
alert("Some 'single' text");
EOT;

        $this->assertSame(
            $expected,
            JSHelper::quoteStyle($source)
                ->setPreserveMixed(true)
                ->singleToDouble()
        );
    }

    public function test_doubleToSingle() : void
    {
        $source = <<<'EOT'
alert("Some text");
EOT;

        $expected = <<<'EOT'
alert('Some text');
EOT;

        $this->assertSame($expected, JSHelper::quoteStyle($source)->doubleToSingle());
    }

    public function test_doubleToSingleNested() : void
    {
        $source = <<<'EOT'
alert("Some 'single' text");
EOT;

        $expected = <<<'EOT'
alert('Some \'single\' text');
EOT;

        $this->assertSame($expected, JSHelper::quoteStyle($source)->doubleToSingle());
    }

    public function test_doubleToSingleNestedPreserved() : void
    {
        $source = <<<'EOT'
alert("Some 'single' text");
EOT;

        $expected = <<<'EOT'
alert('Some "single" text');
EOT;

        $this->assertSame(
            $expected,
            JSHelper::quoteStyle($source)
                ->setPreserveMixed(true)
                ->doubleToSingle()
        );
    }
}
