<?php

declare(strict_types=1);

namespace AppUtilsTests\JSHelper;

use AppUtils\JSHelper;
use AppUtils\JSHelper\QuoteConverter;
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

    public function test_multipleStatements() : void
    {
        $source = <<<'EOT'
alert('Some text');alert("Another text");
EOT;

        $expected = <<<'EOT'
alert("Some text");alert("Another text");
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

    public function test_doubleToSingleIfAlreadySingle() : void
    {
        $source = <<<'EOT'
alert('Some "quoted" text');
EOT;

        $expected = <<<'EOT'
alert('Some \'quoted\' text');
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

        $this->assertSame(
            $expected,
            JSHelper::quoteStyle($source)
                ->setPreserveMixed(true)
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

        $this->assertSame(
            $expected,
            JSHelper::quoteStyle($source)
                ->setPreserveMixed(true)
                ->singleToDouble()
        );
    }

    /**
     * With HTML compatibility turned off, the HTML attribute quotes are
     * converted as if they were part of the JS string.
     */
    public function test_doubleToSingleNoHTMLCompatibility() : void
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

    public function test_brokenQuotes() : void
    {
        $source = <<<'EOT'
alert('Broken");
EOT;

        $this->expectExceptionCode(QuoteConverter::ERROR_BROKEN_QUOTES);

        JSHelper::quoteStyle($source)->singleToDouble();
    }

    public function test_validSingleQuoteInAttribute() : void
    {
        $source = <<<'EOT'
alert("Broken <span title=\"Title's bad\">attribute</span>.");
EOT;

        $expected = <<<'EOT'
alert('Broken <span title="Title\'s bad">attribute</span>.');
EOT;

        $this->assertSame(
            $expected,
            JSHelper::quoteStyle($source)
                ->doubleToSingle()
        );
    }

    public function test_validSingleToDoubleQuoteInAttribute() : void
    {
        $source = <<<'EOT'
alert('Broken <span title="Title\'s bad">attribute</span>.');
EOT;

        $expected = <<<'EOT'
alert("Broken <span title=\"Title's bad\">attribute</span>.");
EOT;

        $this->assertSame(
            $expected,
            JSHelper::quoteStyle($source)
                ->singleToDouble()
        );
    }

    public function test_brokenHTMLAttributeQuote() : void
    {
        $source = <<<'EOT'
alert('Broken <span title="Title\'s bad\">attribute</span>');
EOT;

        $this->expectExceptionCode(QuoteConverter::ERROR_MISMATCHED_ATTRIBUTE_QUOTE);

        JSHelper::quoteStyle($source)->singleToDouble();
    }
}
