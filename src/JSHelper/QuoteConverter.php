<?php
/**
 * @package AppUtils
 * @subpackage JSHelper
 */

declare(strict_types=1);

namespace AppUtils\JSHelper;

use AppUtils\JSHelper;

/**
 * JavaScript quote style converter: Allows switching between
 * single and double quote style of a JavaScript statement.
 *
 * This is useful for escaping JavaScript statements for use
 * in HTML attributes.
 *
 * @package AppUtils
 * @subpackage JSHelper
 *
 * @see JSHelper::quoteStyle()
 * @see \AppUtilsTests\JSHelper\QuoteConversionTests
 */
class QuoteConverter
{
    private string $statement;
    private bool $preserveMixed = false;
    private bool $htmlCompatible = true;

    /**
     * @var array<string,string>
     */
    private array $htmlReplaces = array();

    public function __construct(string $statement)
    {
        // Replace all quotes with placeholders to uniquely identify them.
        $this->statement = str_replace(
            array("\'", '\"', '"', "'"),
            array('__SINGLE_ESC__', '__DOUBLE_ESC__', '__DOUBLE__', '__SINGLE__'),
            $statement
        );
    }

    /**
     * By default, mixed quotes are converted to a unified
     * quote style. For example, <code>"Some 'text'"</code>
     * is converted to <code>"Some \"text\""</code>.
     * This allows changing that behavior.
     *
     * @param bool $preserve Set to true to preserve mixed quotes. False is the default.
     * @return $this
     */
    public function setPreserveMixed(bool $preserve) : self
    {
        $this->preserveMixed = $preserve;
        return $this;
    }

    /**
     * By default, HTML compatibility is enabled.
     * This means that HTML attribute double quotes are escaped as required.
     *
     * It can be turned off if needed, which slightly improves
     * performance if you know that no HTML attributes are present.
     *
     * @param bool $compatible
     * @return $this
     */
    public function setHTMLCompatible(bool $compatible) : self
    {
        $this->htmlCompatible = $compatible;
        return $this;
    }

    /**
     * Switches from single to double quote style.
     *
     * NOTE: Assumes that the statement has a correct syntax.
     * Nested quotes must already have been escaped as required.
     *
     * @return string
     */
    public function singleToDouble() : string
    {
        return $this->replaceQuotes(
            array('"', '\"', '\"', "\'"),
            '\"'
        );
    }

    /**
     * Switches from double to single quote style.
     *
     * NOTE: Assumes that the statement has a correct syntax.
     * Nested quotes must already have been escaped as required.
     *
     * @return string
     */
    public function doubleToSingle() : string
    {
        return $this->replaceQuotes(
            array("\'", "'", '\"', "\'"),
            '"'
        );
    }

    private function replaceQuotes(array $notPreserve, string $htmlQuotes) : string
    {
        $statement = $this->prepareStatement();

        $replace = array('"', "'", '\"', "\'");

        if(!$this->preserveMixed) {
            $replace = $notPreserve;
        }

        $result = str_replace(
            array("__SINGLE__", '__DOUBLE__', '__SINGLE_ESC__', '__DOUBLE_ESC__'),
            $replace,
            $statement
        );

        if(!$this->htmlCompatible || empty($this->htmlReplaces)) {
            return $result;
        }

        return $this->replaceHTML($result, $htmlQuotes);
    }

    private function prepareStatement() : string
    {
        if(!$this->htmlCompatible) {
            return $this->statement;
        }

        $statement = $this->statement;

        // No HTML tags present, and no attributes
        if(strpos($statement, '<') === false && strpos($statement, '=__DOUBLE') === false) {
            return $statement;
        }

        preg_match_all('/=\w*(__DOUBLE__|__DOUBLE_ESC__)(.*)(__DOUBLE__|__DOUBLE_ESC__)/U', $statement, $matches);

        if(empty($matches[0])) {
            return $statement;
        }

        $count = 1;
        foreach($matches[0] as $match)
        {
            $placeholder = sprintf('__HTML%04d__', $count);
            $statement = str_replace($match, $placeholder, $statement);
            $this->htmlReplaces[$placeholder] = str_replace(array('__DOUBLE__', '__DOUBLE_ESC__'), '__QUOTE__', $match);
            $count++;
        }

        return $statement;
    }

    private function replaceHTML(string $statement, string $quotes) : string
    {
        foreach($this->htmlReplaces as $placeholder => $replace) {
            $statement = str_replace(
                $placeholder,
                str_replace('__QUOTE__', $quotes, $replace),
                $statement
            );
        }

        return $statement;
    }
}
