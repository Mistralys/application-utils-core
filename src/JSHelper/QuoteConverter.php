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
 */
class QuoteConverter
{
    private string $statement;
    private bool $preserveMixed = false;

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
     * Switches from single to double quote style.
     *
     * NOTE: Assumes that the statement has a correct syntax.
     * Nested quotes must already have been escaped as required.
     *
     * @return string
     */
    public function singleToDouble() : string
    {
        $replace = array('"', "'", '\"', "\'");

        if(!$this->preserveMixed) {
            $replace = array('"', '\"', '\"', "\'");
        }

        return str_replace(
            array("__SINGLE__", '__DOUBLE__', '__SINGLE__', '__DOUBLE__'),
            $replace,
            $this->statement
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
        $replace = array('"', "'", "\'", '\"');

        if(!$this->preserveMixed) {
            $replace = array("\'", "'", "\'", '\"');
        }

        return str_replace(
            array("__SINGLE__", '__DOUBLE__', '__SINGLE__', '__DOUBLE__'),
            $replace,
            $this->statement
        );
    }
}
