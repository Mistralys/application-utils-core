<?php

declare(strict_types=1);

/**
 * @package Application Utils
 * @subpackage Highlighter
 */

namespace AppUtils;

use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\Highlighter\HighlighterException;
use AppUtils\Highlighter\StyleInliner;
use DomainException;
use Highlight\Highlighter as HighlightLib;
use function HighlightUtilities\getAvailableStyleSheets;
use function HighlightUtilities\getStyleSheet;

/**
 * Syntax highlighter helper: Uses highlight.php and other ways to add syntax
 * highlighting to a range of formats.
 *
 * Usage:
 *
 * Parsing source code from a string or file:
 *
 * <pre>
 * $highlighted = Highlighter::parseString($xml, 'xml');
 * $highlighted = Highlighter::parseFile('/path/to/file.xml', 'xml');
 * </pre>
 *
 * Retrieving a raw HighlightedCode result object:
 *
 * <pre>
 * $result = Highlighter::fromString($xml, 'xml');
 * $result = Highlighter::fromFile('/path/to/file.xml', 'xml');
 * </pre>
 *
 * Theme / stylesheet configuration:
 *
 * <pre>
 * // Use an external stylesheet (default behaviour)
 * Highlighter::setDefaultTheme('github');
 * $styleTag = Highlighter::getStyleTag(); // include once per page
 *
 * // Or enable inline styles (self-contained output for emails/PDFs)
 * Highlighter::setUseInlineStyles(true);
 * $html = Highlighter::parseString($code, 'php'); // no stylesheet needed
 * </pre>
 *
 * Other, more specialized formats are available in the
 * according format methods, e.g. <code>json()</code>, <code>url()</code>.
 *
 * @package Application Utils
 * @subpackage Highlighter
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Highlighter
{
    public const DEFAULT_THEME = 'github';

    private static string $theme = self::DEFAULT_THEME;
    private static bool $useInlineStyles = true;
    private static ?StyleInliner $inliner = null;

    // region: Theme & Style Configuration

    /**
     * Sets the highlight.js theme to use.
     *
     * Available themes are listed by {@see Highlighter::getAvailableThemes()}.
     *
     * @param string $themeName Theme name without the `.css` extension (e.g. 'github', 'atom-one-dark').
     * @return void
     */
    public static function setDefaultTheme(string $themeName) : void
    {
        if(self::$theme !== $themeName)
        {
            self::$theme = $themeName;
            self::$inliner = null; // invalidate cached inliner on theme change
        }
    }

    /**
     * Returns the currently configured theme name.
     *
     * @return string
     */
    public static function getDefaultTheme() : string
    {
        return self::$theme;
    }

    /**
     * Returns a list of all available theme names.
     *
     * @return string[]
     */
    public static function getAvailableThemes() : array
    {
        return getAvailableStyleSheets();
    }

    /**
     * Enables or disables inline style mode.
     *
     * When enabled, all `parseString()`, `parseFile()`, and convenience
     * method output will have `style` attributes injected directly instead
     * of CSS classes. This produces self-contained HTML suitable for
     * emails, PDFs, or contexts where external stylesheets are unavailable.
     *
     * @param bool $enabled
     * @return void
     */
    public static function setUseInlineStyles(bool $enabled) : void
    {
        self::$useInlineStyles = $enabled;
    }

    /**
     * Whether inline style mode is currently enabled.
     *
     * @return bool
     */
    public static function isUseInlineStyles() : bool
    {
        return self::$useInlineStyles;
    }

    /**
     * Returns an HTML `<style>` tag containing the CSS for the current theme.
     *
     * Include this once per page when using class-based output (the default).
     * Not needed when inline styles are enabled via {@see setUseInlineStyles()}.
     *
     * @return string
     */
    public static function getStyleTag() : string
    {
        $css = getStyleSheet(self::$theme);

        if($css === false)
        {
            return '';
        }

        return '<style>' . $css . '</style>';
    }

    /**
     * Returns the raw CSS string for the current theme.
     *
     * @return string Empty string if the theme cannot be read.
     */
    public static function getStyleCSS() : string
    {
        $css = getStyleSheet(self::$theme);

        return $css !== false ? $css : '';
    }

    /**
     * Resets all static configuration to defaults.
     *
     * Intended for use in test teardown to avoid leaking state between tests.
     *
     * @return void
     */
    public static function resetConfig() : void
    {
        self::$theme = self::DEFAULT_THEME;
        self::$useInlineStyles = true;
        self::$inliner = null;
    }

    // endregion

    // region: Core Highlighting
    /**
     * Highlights the source code string and returns the raw result object.
     *
     * @param string $sourceCode
     * @param string $language A highlight.php language identifier (e.g. 'xml', 'php', 'sql').
     * @return \Highlight\HighlightResult|\stdClass Result object with ->language (string) and ->value (string) properties.
     * @throws HighlighterException If the specified language is not supported.
     */
    public static function fromString(string $sourceCode, string $language) : object
    {
        try
        {
            return (new HighlightLib())->highlight($language, $sourceCode);
        }
        catch(DomainException $e)
        {
            throw new HighlighterException(
                sprintf('Unknown highlight language [%s].', $language),
                '',
                HighlighterException::ERROR_UNKNOWN_LANGUAGE,
                $e
            );
        }
    }

    /**
     * Highlights the contents of a file and returns the raw result object.
     *
     * @param string $path
     * @param string $language A highlight.php language identifier (e.g. 'xml', 'php', 'sql').
     * @return \Highlight\HighlightResult|\stdClass Result object with ->language (string) and ->value (string) properties.
     * @throws HighlighterException If the specified language is not supported.
     */
    public static function fromFile(string $path, string $language) : object
    {
        return self::fromString(FileHelper::readContents($path), $language);
    }

    /**
     * Parses and highlights the source code string, returning an HTML snippet.
     *
     * When inline styles are disabled (default), the returned HTML is wrapped in
     * `<pre><code class="hljs {language}">...</code></pre>` and requires a
     * highlight.js CSS stylesheet (see {@see getStyleTag()}).
     *
     * When inline styles are enabled, all `hljs-*` classes are replaced by
     * `style` attributes, producing self-contained HTML.
     *
     * @param string $sourceCode
     * @param string $language A highlight.php language identifier (e.g. 'xml', 'php', 'sql').
     * @return string
     * @throws HighlighterException If the specified language is not supported.
     */
    public static function parseString(string $sourceCode, string $language) : string
    {
        $result = self::fromString($sourceCode, $language);

        return self::wrapResult($result->value, $result->language);
    }

    /**
     * Parses and highlights the contents of a file, returning an HTML snippet.
     *
     * @param string $path
     * @param string $language A highlight.php language identifier (e.g. 'xml', 'php', 'sql').
     * @return string
     * @throws HighlighterException If the specified language is not supported.
     */
    public static function parseFile(string $path, string $language) : string
    {
        $result = self::fromFile($path, $language);

        return self::wrapResult($result->value, $result->language);
    }

    /**
     * Wraps highlighted HTML in `<pre><code>` tags, optionally applying inline styles.
     *
     * @param string $html The highlighted inner HTML from highlight.php.
     * @param string $language The detected language name.
     * @return string
     */
    private static function wrapResult(string $html, string $language) : string
    {
        if(self::$useInlineStyles)
        {
            return self::getInliner()->apply($html, $language);
        }

        return
            '<pre><code class="hljs ' . $language . '">' .
            $html .
            '</code></pre>';
    }

    /**
     * Returns (and lazily creates) the {@see StyleInliner} for the current theme.
     *
     * @return StyleInliner
     */
    private static function getInliner() : StyleInliner
    {
        if(self::$inliner === null)
        {
            self::$inliner = new StyleInliner(self::$theme);
        }

        return self::$inliner;
    }

    // endregion

    // region: Convenience Methods

    /**
     * Adds HTML syntax highlighting to the specified SQL string.
     *
     * @param string $sql
     * @return string
     * @throws HighlighterException
     */
    public static function sql(string $sql) : string
    {
        return self::parseString($sql, 'sql');
    }

    /**
     * Adds HTML syntax highlighting to a JSON string, or a data array/object.
     *
     * @param array<int|string,mixed>|object|string $subject A JSON string, or data array/object to convert to JSON to highlight.
     * @return string
     *
     * @throws JSONConverterException
     * @throws HighlighterException
     */
    public static function json(array|object|string $subject) : string
    {
        if(!is_string($subject))
        {
            $subject = JSONConverter::var2json($subject, JSON_PRETTY_PRINT);
        }

        $subject = str_replace('\/', '/', $subject);

        return self::parseString($subject, 'json');
    }

    /**
     * Adds HTML syntax highlighting to the specified XML code.
     *
     * @param string $xml The XML to highlight.
     * @param bool $formatSource Whether to format the source with indentation to make it readable.
     * @return string
     * @throws HighlighterException
     */
    public static function xml(string $xml, bool $formatSource=false) : string
    {
        if($formatSource) {
            $xml = XMLHelper::formatXML($xml);
        }

        return self::parseString($xml, 'xml');
    }

    /**
     * Adds HTML syntax highlighting to the specified HTML code.
     *
     * @param string $html
     * @param bool $formatSource
     * @return string
     * @throws HighlighterException
     */
    public static function html(string $html, bool $formatSource=false) : string
    {
        if($formatSource) {
            $html = HTMLHelper::formatHTML($html);
        }

        return self::parseString($html, 'xml');
    }

    /**
     * Adds HTML syntax highlighting to a bit of PHP code.
     *
     * @param string $phpCode
     * @return string
     * @throws HighlighterException
     */
    public static function php(string $phpCode) : string
    {
        return self::parseString($phpCode, 'php');
    }

    /**
     * Adds HTML syntax highlighting to an URL.
     *
     * NOTE: Includes the necessary CSS styles. When
     * highlighting several URLs in the same page,
     * prefer using the `parseURL` function instead.
     *
     * @param string $url
     * @return string
     */
    public static function url(string $url) : string
    {
        $info = parseURL($url);

        return
            '<style>'.$info->getHighlightCSS().'</style>'.
            $info->getHighlighted();
    }
}
