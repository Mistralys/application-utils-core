<?php

declare(strict_types=1);

/**
 * @package Application Utils
 * @subpackage Highlighter
 */

namespace AppUtils\Highlighter;

use function HighlightUtilities\getStyleSheet;

/**
 * Converts class-based highlight.php HTML output to inline-styled HTML
 * by parsing a highlight.js CSS theme and applying the declarations
 * directly as `style` attributes on each element.
 *
 * This produces self-contained HTML suitable for emails, PDFs,
 * and other contexts where external stylesheets are not available.
 *
 * @package Application Utils
 * @subpackage Highlighter
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class StyleInliner
{
    /**
     * @var array<string, string> Map of CSS selectors (e.g. ".hljs-keyword") to inline style strings.
     */
    private array $styleMap = array();

    /**
     * The base style for the `.hljs` wrapper element.
     */
    private string $baseStyle = '';

    public function __construct(string $themeName)
    {
        $css = getStyleSheet($themeName);

        if($css !== false)
        {
            $this->parseCss($css);
        }
    }

    /**
     * Applies inline styles to the given highlighted HTML string.
     *
     * Replaces all `class="hljs-*"` attributes with `style="..."` attributes
     * and applies the base `.hljs` style to the wrapper `<code>` element.
     *
     * @param string $html The highlighted HTML (the inner value from highlight.php).
     * @param string $language The language identifier used for the wrapper.
     * @return string The fully inline-styled HTML snippet.
     */
    public function apply(string $html, string $language) : string
    {
        // Replace <span class="hljs-xxx"> with <span style="...">
        $html = preg_replace_callback(
            '/<span class="(hljs[\w -]*)">/u',
            function(array $matches) : string {
                $classes = explode(' ', $matches[1]);
                $styles = $this->resolveStyles($classes);

                if($styles !== '')
                {
                    return '<span style="' . $styles . '">';
                }

                return '<span>';
            },
            $html
        ) ?? $html;

        // Build the wrapper with inline base style
        $preStyle = $this->baseStyle;

        return
            '<pre' . ($preStyle !== '' ? ' style="' . $preStyle . '"' : '') . '>' .
            '<code class="hljs ' . $language . '">' .
            $html .
            '</code></pre>';
    }

    /**
     * Resolves the inline style string for a list of CSS classes.
     *
     * @param string[] $classes
     * @return string The combined inline style string.
     */
    private function resolveStyles(array $classes) : string
    {
        $declarations = array();

        foreach($classes as $class)
        {
            $selector = '.' . $class;

            if(isset($this->styleMap[$selector]))
            {
                $declarations[] = $this->styleMap[$selector];
            }
        }

        return implode('; ', $declarations);
    }

    /**
     * Parses a highlight.js CSS stylesheet into a map of selectors to inline style declarations.
     *
     * Only processes single-class `.hljs*` selectors. Complex selectors
     * (descendant combinators, pseudo-classes) are applied as best-effort
     * by mapping the last class in the chain.
     *
     * @param string $css Raw CSS content.
     */
    private function parseCss(string $css) : void
    {
        // Remove comments
        $css = (string)preg_replace('/\/\*.*?\*\//s', '', $css);

        // Match rule blocks: selectors { declarations }
        preg_match_all('/([^{}]+)\{([^{}]+)\}/s', $css, $matches, PREG_SET_ORDER);

        foreach($matches as $match)
        {
            $declarations = trim($match[2]);
            $declarations = $this->normalizeDeclarations($declarations);

            if($declarations === '')
            {
                continue;
            }

            $selectors = explode(',', $match[1]);

            foreach($selectors as $selector)
            {
                $selector = trim($selector);

                if($selector === '.hljs')
                {
                    $this->baseStyle = $declarations;
                    continue;
                }

                // Handle compound selectors like ".hljs-tag .hljs-attr"
                // by mapping the last class in the chain.
                if(str_contains($selector, '.hljs'))
                {
                    $parts = preg_split('/\s+/', $selector);

                    if($parts !== false)
                    {
                        $lastPart = $parts[count($parts) - 1];

                        // Only map selectors that target an hljs class
                        if(str_starts_with($lastPart, '.hljs'))
                        {
                            if(isset($this->styleMap[$lastPart]))
                            {
                                // Merge: later rules override earlier ones
                                $this->styleMap[$lastPart] .= '; ' . $declarations;
                            }
                            else
                            {
                                $this->styleMap[$lastPart] = $declarations;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Normalizes CSS declarations into a single-line semicolon-separated string
     * suitable for use in a `style` attribute.
     *
     * @param string $declarations
     * @return string
     */
    private function normalizeDeclarations(string $declarations) : string
    {
        // Collapse whitespace and trim
        $declarations = (string)preg_replace('/\s+/', ' ', $declarations);
        $declarations = trim($declarations, " \t\n\r\0\x0B;");

        return $declarations;
    }
}
