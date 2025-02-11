<?php
/**
 * File containing the class {@see HTMLHelper}.
 *
 * @package Application Utils
 * @subpackage HTMLHelper
 * @see HTMLHelper
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\HTMLHelper\HTMLHelperException;
use DOMDocument;

/**
 * Helper for common HTML-related tasks.
 *
 * @package Application Utils
 * @subpackage HTML
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTMLHelper
{
    /**
     * Removes all HTML comments from the string.
     *
     * @param string $html
     * @return string
     */
    public static function stripComments(string $html) : string
    {
        return preg_replace('/<!--(?!<!)[^\[>].*?-->/s', '', $html);
    }

    /**
     * @var string[]
     */
    private static array $newParaTags = array(
        'ul',
        'ol',
        'iframe',
        'table'
    );

    /**
     * Injects the target text at the end of an HTML snippet,
     * either in an existing <p> tag or in a new <p> tag if
     * the last block tag cannot be used (<ul> for example).
     *
     * NOTE: Assumes that it is not a whole HTML document.
     *
     * @param string $text
     * @param string $html
     * @return string
     * @throws HTMLHelperException {@see HTMLHelperException::ERROR_CANNOT_FIND_CLOSING_TAG}
     */
    public static function injectAtEnd(string $text, string $html) : string
    {
        preg_match_all('%<([A-Z][A-Z0-9]*)\b[^>]*>(.*?)</\1>%si', $html, $result, PREG_PATTERN_ORDER);

        if(empty($result[1])) {
            return '<p>'.$text.'</p>';
        }

        $tagName = array_pop($result[1]);
        $pos = strrpos($html, '</'.$tagName.'>');

        if($pos === false) {
            throw new HTMLHelperException(
                'Could not find closing tag for ['.$tagName.'].',
                '',
                HTMLHelperException::ERROR_CANNOT_FIND_CLOSING_TAG
            );
        }

        if(in_array(strtolower($tagName), self::$newParaTags)) {
            $replace = '</'.$tagName.'><p>'.$text.'</p>';
        } else {
            $replace = $text.'</'.$tagName.'>';
        }

        return (string)substr_replace($html, $replace, $pos, strlen($html));
    }

    /**
     * Formats the HTML to make it readable.
     *
     * @param string $html
     * @return string
     * @throws HTMLHelperException {@see HTMLHelperException::ERROR_FORMAT_HTML_FAILED}
     */
    public static function formatHTML(string $html) : string
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $dom->loadHTML($html);

        $html = $dom->saveHTML();

        if(is_string($html)) {
            return $html;
        }

        throw new HTMLHelperException(
            'Failed to format the HTML.',
            '',
            HTMLHelperException::ERROR_FORMAT_HTML_FAILED
        );
    }
}
