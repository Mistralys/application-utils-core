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

    public function test_singleToDoubleMixed() : void
    {
        $source = <<<'EOT'
alert('Some "single" text');
EOT;

        $expected = <<<'EOT'
alert("Some \"single\" text");
EOT;

        $this->assertSame($expected, JSHelper::quoteStyle($source)->singleToDouble());
    }

    public function test_singleToDoubleMixedPreserved() : void
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

    public function test_singleToDoubleEscaped() : void
    {
        $source = <<<'EOT'
alert('Some \'single\' text');
EOT;

        $expected = <<<'EOT'
alert("Some \"single\" text");
EOT;

        $this->assertSame(
            $expected,
            JSHelper::quoteStyle($source)
                ->singleToDouble()
        );

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

    public function test_doubleToSingleMixed() : void
    {
        $source = <<<'EOT'
alert("Some 'single' text");
EOT;

        $expected = <<<'EOT'
alert('Some \'single\' text');
EOT;

        $this->assertSame($expected, JSHelper::quoteStyle($source)->doubleToSingle());
    }

    public function test_doubleToSingleMixedPreserved() : void
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

    public function test_doubleToSingleEscaped() : void
    {
        $source = <<<'EOT'
alert("Some \"single\" text");
EOT;

        $expected = <<<'EOT'
alert('Some \'single\' text');
EOT;

        $this->assertSame(
            $expected,
            JSHelper::quoteStyle($source)
                ->doubleToSingle()
        );

        $this->assertSame(
            $expected,
            JSHelper::quoteStyle($source)
                ->setPreserveMixed(true)
                ->doubleToSingle()
        );
    }

    public function test_doubleToSingleWithHTML() : void
    {
        $source = <<<'EOT'
alert("Some <span title=\"single\">text</span> and <strong>bold</strong> and <a href=\"#\">Link</a>.");
EOT;

        $expected = <<<'EOT'
alert('Some <span title="single">text</span> and <strong>bold</strong> and <a href="#">Link</a>.');
EOT;

        $this->assertSame(
            $expected,
            JSHelper::quoteStyle($source)
                ->doubleToSingle()
        );
    }

    public function test_singleToDoubleWithHTML() : void
    {
        $source = <<<'EOT'
alert('Some <span title="single">text</span> and <strong>bold</strong> and <a href="#">Link</a>.');
EOT;

        $expected = <<<'EOT'
alert("Some <span title=\"single\">text</span> and <strong>bold</strong> and <a href=\"#\">Link</a>.");
EOT;

        $this->assertSame(
            $expected,
            JSHelper::quoteStyle($source)
                ->singleToDouble()
        );
    }

    /**
     * With HTML compatibility turned off, the HTML attribute quotes are
     * converted as if they were part of the JS string.
     */
    public function test_singleToDoubleNoHTMLCompatibility() : void
    {
        $source = <<<'EOT'
alert("Some <span title=\"single\">text</span> and <strong>bold</strong> and <a href=\"#\">Link</a>.");
EOT;

        $expected = <<<'EOT'
alert('Some <span title=\'single\'>text</span> and <strong>bold</strong> and <a href=\'#\'>Link</a>.');
EOT;

        $this->assertSame(
            $expected,
            JSHelper::quoteStyle($source)
                ->setHTMLCompatible(false)
                ->doubleToSingle()
        );
    }
}
