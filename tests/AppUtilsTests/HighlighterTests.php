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
        Highlighter::setUseInlineStyles(false);

        $result = Highlighter::parseString($this->exampleString, 'html');

        $this->assertEquals($this->exampleOutputClassBased, $result);
    }

    public function test_parseFile(): void
    {
        Highlighter::setUseInlineStyles(false);

        $result = Highlighter::parseFile($this->assetsFolder . '/example.html', 'html');

        $this->assertEquals($this->exampleOutputClassBased, $result);
    }

    // endregion

    // region: Theme Tests

    public function test_setDefaultTheme(): void
    {
        Highlighter::setDefaultTheme('atom-one-dark');

        $this->assertSame('atom-one-dark', Highlighter::getDefaultTheme());
    }

    public function test_getAvailableThemes(): void
    {
        $themes = Highlighter::getAvailableThemes();

        $this->assertNotEmpty($themes);
        $this->assertContains('github', $themes);
        $this->assertContains('default', $themes);
    }

    public function test_getStyleTag(): void
    {
        Highlighter::setDefaultTheme('github');

        $tag = Highlighter::getStyleTag();

        $this->assertStringStartsWith('<style>', $tag);
        $this->assertStringEndsWith('</style>', $tag);
        $this->assertStringContainsString('.hljs', $tag);
    }

    public function test_getStyleCSS(): void
    {
        Highlighter::setDefaultTheme('github');

        $css = Highlighter::getStyleCSS();

        $this->assertNotEmpty($css);
        $this->assertStringContainsString('.hljs', $css);
    }

    // endregion

    // region: Inline Style Tests

    public function test_inlineStyles_containsStyleAttribute(): void
    {
        $result = Highlighter::parseString($this->exampleString, 'html');

        // Must contain style= attributes instead of (or in addition to) class=
        $this->assertStringContainsString('style="', $result);
        // The wrapper should still have the hljs class for identification
        $this->assertStringContainsString('class="hljs xml"', $result);
    }

    public function test_inlineStyles_noHljsClassesOnSpans(): void
    {
        $result = Highlighter::parseString($this->exampleString, 'html');

        // The inner <span> elements should NOT have class="hljs-*" anymore
        $this->assertDoesNotMatchRegularExpression(
            '/<span class="hljs-/',
            $result,
            'Inline mode should replace hljs-* classes with style attributes.'
        );
    }

    public function test_inlineStyles_preHasStyle(): void
    {
        $result = Highlighter::parseString($this->exampleString, 'html');

        // The <pre> element should carry the base .hljs style
        $this->assertMatchesRegularExpression(
            '/<pre style="[^"]+">/',
            $result,
            'The <pre> wrapper should have inline styles from the theme.'
        );
    }

    public function test_inlineStyles_enabled_byDefault(): void
    {
        $this->assertTrue(Highlighter::isUseInlineStyles());
    }

    public function test_resetConfig(): void
    {
        Highlighter::setDefaultTheme('atom-one-dark');
        Highlighter::setUseInlineStyles(false);

        Highlighter::resetConfig();

        $this->assertSame(Highlighter::DEFAULT_THEME, Highlighter::getDefaultTheme());
        $this->assertTrue(Highlighter::isUseInlineStyles());
    }

    // endregion

    // region: Support methods

    private string $assetsFolder;
    private string $exampleString = '<p>Foobar</p>';

    private string $exampleOutputClassBased =
        '<pre><code class="hljs xml">' .
        '<span class="hljs-tag">&lt;<span class="hljs-name">p</span>&gt;</span>' .
        'Foobar' .
        '<span class="hljs-tag">&lt;/<span class="hljs-name">p</span>&gt;</span>' .
        '</code></pre>';

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetsFolder = $this->assetsRootFolder . '/Highlighter';
    }

    protected function tearDown(): void
    {
        Highlighter::resetConfig();

        parent::tearDown();
    }

    // endregion
}
