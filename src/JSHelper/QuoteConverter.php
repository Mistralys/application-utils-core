<?php
/**
 * @package AppUtils
 * @subpackage JSHelper
 */

declare(strict_types=1);

namespace AppUtils\JSHelper;

use AppUtils\BaseException;
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
    public const ERROR_BROKEN_QUOTES = 6488112;
    public const ERROR_MISMATCHED_ATTRIBUTE_QUOTE = 6488113;

    private const DOUBLE = '__QUOT_D__';
    private const SINGLE = '__QUOT_S__';
    private const SINGLE_ESC = '__QUOT_SE__';
    private const DOUBLE_ESC = '__QUOT_DE__';

    private string $statement;
    private bool $preserveMixed = false;
    private bool $htmlCompatible = true;

    /**
     * @var array<string,string>
     */
    private array $htmlReplaces = array();
    private array $quotes = array();
    private string $original;

    public function __construct(string $statement)
    {
        $this->original = $statement;
    }

    private function parse() : void
    {
        // Replace all quotes with placeholders to uniquely identify them.
        $this->statement = str_replace(
            array("\'", '\"', '"', "'"),
            array(self::SINGLE_ESC, self::DOUBLE_ESC, self::DOUBLE, self::SINGLE),
            $this->original
        );

        $this->detectHTMLQuotes();
        $this->detectQuotePairs();
    }

    /**
     * Detects all quote pairs in the target string, and
     * replaces each with a unique placeholder.
     * Accepts alternating quote styles, for example,
     * a single quoted string followed by a double-quoted one.
     *
     * @return void
     * @throws BaseException
     */
    private function detectQuotePairs() : void
    {
        $count = 1;

        while($style = $this->detectFirstQuote($this->statement))
        {
            preg_match('/'.$style.'(.*)'.$style.'/', $this->statement, $matches);

            if(empty($matches[1]))
            {
                throw new JSHelperException(
                    'Broken quotes in string',
                    sprintf(
                        'Not all quote pairs are correctly closed in the target string:'.PHP_EOL.
                        '%s',
                        $this->original
                    ),
                    self::ERROR_BROKEN_QUOTES
                );
            }

            $placeholder = sprintf('__QUOTES%04d__', $count);
            $this->statement = str_replace($matches[0], $placeholder, $this->statement);

            $this->quotes[$placeholder] = $matches[1];

            $count++;
        }
    }

    /**
     * Detects the quote style used for the next quote pair
     * found in the target string, if any.
     *
     * @param string $statement
     * @return string|null
     */
    private function detectFirstQuote(string $statement) : ?string
    {
        $posD = strpos($statement, self::DOUBLE);
        $posS = strpos($statement, self::SINGLE);

        if($posD === false && $posS === false) {
            return null;
        }

        if($posD === false) {
            return self::SINGLE;
        }

        if($posS === false) {
            return self::DOUBLE;
        }

        if($posD < $posS) {
            return self::DOUBLE;
        }

        return self::SINGLE;
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
    public function setPreserveMixed(bool $preserve=false) : self
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
            '"',
            "'",
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
            "'",
            '"',
            '"'
        );
    }

    private function replaceQuotes(string $outer, string $inner, string $htmlQuotes) : string
    {
        $this->parse();

        $result = $this->statement;

        $replaces = array(
            self::SINGLE => '\\'.$outer,
            self::DOUBLE => '\\'.$outer,
            self::SINGLE_ESC => '\\'.$outer,
            self::DOUBLE_ESC => '\\'.$outer
        );

        if($this->preserveMixed) {
           $replaces[self::SINGLE] = $inner;
           $replaces[self::DOUBLE] = $inner;
        }

        $search = array_keys($replaces);
        $replace = array_values($replaces);

        foreach($this->quotes as $placeholder => $content)
        {
            $content = str_replace($search, $replace, $content);
            $content = $outer.$content.$outer;

            $result = str_replace($placeholder, $content, $result);
        }

        if(!$this->htmlCompatible || empty($this->htmlReplaces)) {
            return $result;
        }

        return $this->replaceHTML($result, $htmlQuotes);
    }

    /**
     * Detects any HTML quoted attributes by searching for
     * <code>=""</code>. These attributes are then replaced
     * with placeholders to be reinserted at the end, in
     * the {@see self::replaceHTML()} method.
     *
     * NOTE: Assumes that the HTML is valid to work correctly.
     *
     * @return void
     */
    private function detectHTMLQuotes() : void
    {
        if(!$this->htmlCompatible) {
            return;
        }

        // No HTML tags present, and no attributes
        if(strpos($this->statement, '<') === false) {
            return;
        }

        preg_match_all('/=\w*__QUOT_(DE|D)__(.*)__QUOT_(DE|D)__/U', $this->statement, $matches);

        if(empty($matches[0])) {
            return;
        }

        $count = 1;
        foreach($matches[0] as $idx => $match)
        {
            if($matches[1] !== $matches[3]) {
                throw new JSHelperException(
                    'Incorrectly quoted HTML attribute',
                    sprintf(
                        'The attribute string seems to have mismatched quotes:'.PHP_EOL.
                        '%s',
                        $match
                    ),
                    self::ERROR_MISMATCHED_ATTRIBUTE_QUOTE
                );
            }

            $placeholder = sprintf('__HTML%04d__', $count);
            $this->statement = str_replace($match, $placeholder, $this->statement);
            $this->htmlReplaces[$placeholder] = $matches[2][$idx];
            $count++;
        }
    }

    private function replaceHTML(string $statement, string $quotes) : string
    {
        $single = "'";
        if($quotes === '"') {
            $single = "\'";
        }

        foreach($this->htmlReplaces as $placeholder => $replace)
        {
            // Special case: Single quotes in HTML attributes.
            // Double quotes are illegal, and should be inserted
            // as entities anyway, so we do not need to handle them.
            $replace = str_replace(array(self::SINGLE_ESC, self::SINGLE), $single, $replace);

            $statement = str_replace(
                $placeholder,
                '='.$quotes.$replace.$quotes,
                $statement
            );
        }

        return $statement;
    }
}
