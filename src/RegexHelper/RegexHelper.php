<?php

namespace AppUtils;

class RegexHelper
{
    /**
     * Disallow <> and double quotes from titles.
     * @var string
     */
    public const REGEX_NAME_OR_TITLE = '/\A[^<>"]+/s';
    
    public const REGEX_URL = '/\A(?:^
	(# Scheme
	 [a-z][a-z0-9+\-.]*:
	 (# Authority & path
	  \/\/
	  ([a-z0-9\-._~%!$&\'()*+,;=]+@)?              # User
	  ([a-z0-9\-._~%]+                            # Named host
	  |\[[a-f0-9:.]+\]                            # IPv6 host
	  |\[v[a-f0-9][a-z0-9\-._~%!$&\'()*+,;=:]+\])  # IPvFuture host
	  (:[0-9]+)?                                  # Port
	  (\/[a-z0-9\-._~%!$&\'()*+,;=:@]+)*\/?          # Path
	 |# Path without authority
	  (\/?[a-z0-9\-._~%!$&\'()*+,;=:@]+(\/[a-z0-9\-._~%!$&\'()*+,;=:@]+)*\/?)?
	 )
	|# Relative URL (no scheme or authority)
	 ([a-z0-9\-._~%!$&\'()*+,;=@]+(\/[a-z0-9\-._~%!$&\'()*+,;=:@]+)*\/?  # Relative path
	 |(\/[a-z0-9\-._~%!$&\'()*+,;=:@]+)+\/?)                            # Absolute path
	)
	# Query
	(\?[a-z0-9\-._~%!$&\'()*+,;=:@\/?]*)?
	# Fragment
	(\#[a-z0-9\-._~%!$&\'()*+,;=:@\/?]*)?
	$)\Z/ix';
    
    public const REGEX_ALIAS = '/\A[a-z][0-9_a-z-.]{1,80}\Z/';
    
    public const REGEX_ALIAS_CAPITALS = '/\A[a-zA-Z][0-9_a-zA-Z-.]{1,80}\Z/';

    /**
     * Summary: Matches an entire label made of one or more word/space
     * characters plus a specific set of punctuation; rejects any character
     * not in that set. It is case-insensitive and Unicode-aware.
     *
     * ## Details
     *
     * - Anchors: ^ and $ require the pattern to match the whole string.
     * - Core: one or more characters from a character class (the allowed set).
     * - Allowed classes:
     *   - `\w` — word characters (letters, digits, underscore). With u this includes Unicode letters.
     *   - `\s` — whitespace (space, tab, newline, etc.).
     * - A collection of punctuation: apostrophe, asterisk, dot, hyphen, parentheses, square brackets, curly braces, question mark, equals, acute accent (´), backtick (`), dollar, exclamation, percent, plus, hash, underscore, comma, semicolon, colon, pipe.
     * - Quantifier: `+` — requires at least one allowed character.
     * - Flags:
     *   - `i` — case-insensitive (affects letter matching).
     *   - `u` — PCRE Unicode mode (makes \w/[a-z] Unicode-aware).
     *
     * ## Behavior notes
     *
     * - Newlines are permitted because of \s.
     * - Characters not listed (for example <, >, or double quote " ) will cause the match to fail.
     * - The hyphen and other metacharacters are escaped inside the class to prevent range/metacharacter meaning.
     *
     * ## Intended use
     *
     * Validate labels/titles that may include letters, numbers, spaces and
     * common punctuation while excluding potentially dangerous or unwanted
     * characters.
     *
     * @var string
     */
    public const REGEX_LABEL = '/^[\w\s\'*.\-\(\)\[\]{}?=´`$!%+#_,;:|]+$/iu';
    
    public const REGEX_MD5 = '/^[a-f0-9]{32}$/i';

   /**
    * @var string
    * @see https://en.wikipedia.org/wiki/Email_address#Local-part
    */
    public const REGEX_EMAIL = '/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/i';
    
    public const REGEX_PHONE = '/\A[+0-9][0-9 +\-()]+\z/m';
    
    /**
     * A file name, WITHOUT its extension.
     * @var string
     */
    public const REGEX_FILENAME = '/\A[a-zA-Z0-9][\d\w \-_.]*\z/m';
    
    /**
     * An integer number. Disallows leading zeroes as well.
     * @var string
     */
    public const REGEX_INTEGER = '/\A(0|[1-9][0-9]*)\z/';
    
    public const REGEX_FLOAT =
    '~
        ^           # start of input
        -?          # minus, optional
        [0-9]+      # at least one digit
        (           # begin group
            \.      # a dot
            [0-9]+  # at least one digit
        )           # end of group
        ?           # group is optional
        $           # end of input
    ~xD';
    
    /**
     * A date notation. Allows the following formats:
     *
     * 08/30/2015
     * 8/30/15
     * 08/30/2015 8:15
     * 08/30/2015 08:30
     *
     * @var string
     */
    public const REGEX_DATE = '%([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4}) ([0-9]{1,2}):([0-9]{1,2})|([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})%i';

    /**
     * Finds tags like this:
     * - <ul
     * - ul>
     * - <br>
     * - <br/>
     * @var string
     */
    public const REGEX_IS_HTML = '%<{0,1}[a-z\/][\s\S]*>|<[a-z\/][\s\S]*>{0,1}%i';
    
   /**
    * Hexadecimal color codes. Allows the following formats:
    * 
    * FFFFFF
    * FFF
    * 
    * @var string
    */
    public const REGEX_HEX_COLOR_CODE = '/\A(?:[0-9a-fA-F]{6}|[0-9a-fA-F]{3})\z/iU';

    /**
     * IPV4 IP Address. Does only some basic checks.
     */
    public const REGEX_IPV4 = '/((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/siU';
}
