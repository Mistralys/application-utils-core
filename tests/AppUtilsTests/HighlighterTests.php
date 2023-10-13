<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtilsTestClasses\BaseTestCase;
use AppUtils\Highlighter;

final class HighlighterTests extends BaseTestCase
{
    // region: _Tests

    public function test_fromString(): void
    {
        Highlighter::fromString($this->exampleString, 'html');

        $this->addToAssertionCount(1);
    }

    public function test_fromFile(): void
    {
        Highlighter::fromFile($this->assetsFolder . '/example.html', 'html');

        $this->addToAssertionCount(1);
    }

    public function test_parseString(): void
    {
        $result = Highlighter::parseString($this->exampleString, 'html');

        $this->assertEquals($this->exampleOutput, $result);
    }

    public function test_parseFile(): void
    {
        $result = Highlighter::parseFile($this->assetsFolder . '/example.html', 'html');

        $this->assertEquals($this->exampleOutput, $result);
    }

    // endregion

    // region: Support methods

    private string $assetsFolder;
    private string $exampleString = '<p>Foobar</p>';

    private string $exampleOutput =
        '<pre class="html" style="font-family:monospace;">' .
        '&lt;p&gt;Foobar&lt;/p&gt;' .
        '</pre>';

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetsFolder = $this->assetsRootFolder . '/Highlighter';
    }

    // endregion
}
