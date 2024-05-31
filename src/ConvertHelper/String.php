<?php
/**
 * File containing the {@see \AppUtils\ConvertHelper_String} class.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @see \AppUtils\ConvertHelper_String
 */

declare(strict_types=1);

namespace AppUtils;

use ForceUTF8\Encoding;

/**
 * String conversion helper: focuses on string operations.
 *
 * @package Application Utils
 * @subpackage ConvertHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ConvertHelper_String
{
    /**
     * Searches for needle in the specified string, and returns a list
     * of all occurrences, including the matched string. The matched
     * string is useful when doing a case-insensitive search, as it
     * shows the exact matched case of needle.
     *
     * @param string $needle
     * @param string $haystack
     * @param bool $caseInsensitive
     * @return ConvertHelper_StringMatch[]
     */
    public static function findString(string $needle, string $haystack, bool $caseInsensitive=false): array
    {
        if($needle === '') {
            return array();
        }

        $function = 'mb_strpos';
        if($caseInsensitive) {
            $function = 'mb_stripos';
        }

        $pos = 0;
        $positions = array();
        $length = mb_strlen($needle);

        while( ($pos = $function($haystack, $needle, $pos)) !== false)
        {
            $match = mb_substr($haystack, $pos, $length);
            $positions[] = new ConvertHelper_StringMatch($pos, $match);
            $pos += $length;
        }

        return $positions;
    }

    /**
     * Splits a string into an array of all characters it is composed of.
     * Unicode character safe.
     *
     * NOTE: Spaces and newlines (both \r and \n) are also considered single
     * characters.
     *
     * @param string $string
     * @return string[]
     */
    public static function toArray(string $string) : array
    {
        $result = preg_split('//u', $string, 0, PREG_SPLIT_NO_EMPTY);
        if($result !== false) {
            return $result;
        }

        return array();
    }

    /**
     * Calculates the byte length of a string, taking into
     * account any unicode characters.
     *
     * @param string $string
     * @return int
     * @see https://stackoverflow.com/a/9718273/2298192
     */
    public static function toBytes(string $string) : int
    {
        return mb_strlen($string, '8bit');
    }

    /**
     * Converts a string into an MD5 hash.
     *
     * @param string $string
     * @return string
     */
    public static function toHash(string $string): string
    {
        return md5($string);
    }

    /**
     * Creates a short, 8-character long hash for the specified string.
     *
     * WARNING: Not cryptographically safe.
     *
     * @param string $string
     * @return string
     */
    public static function toShortHash(string $string) : string
    {
        return hash('crc32', $string, false);
    }

    /**
     * Converts a string to valid utf8, regardless
     * of the string's encoding(s).
     *
     * @param string $string
     * @return string
     */
    public static function toUtf8(string $string) : string
    {
        if(!self::isASCII($string)) {
            return Encoding::toUTF8($string);
        }

        return $string;
    }

    /**
     * Checks whether the specified string is an ASCII
     * string, without any special or UTF8 characters.
     * Note: empty strings and NULL are considered ASCII.
     * Any variable types other than strings are not.
     *
     * @param mixed $string
     * @return boolean
     */
    public static function isASCII($string) : bool
    {
        if($string === '' || $string === NULL) {
            return true;
        }

        if(!is_string($string)) {
            return false;
        }

        return !preg_match('/[^\x00-\x7F]/', $string);
    }

    /**
     * Checks whether the specified string contains HTML code.
     *
     * @param string $string
     * @return boolean
     */
    public static function isHTML(string $string) : bool
    {
        if(preg_match('%<[a-z/][\s\S]*>%iU', $string)) {
            return true;
        }

        $decoded = html_entity_decode($string);

        return $decoded !== $string;
    }

    /**
     * Normalizes tabs in the specified string by indenting everything
     * back to the minimum tab distance. With the second parameter,
     * tabs can optionally be converted to spaces as well (recommended
     * for HTML output).
     *
     * @param string $string
     * @param boolean $tabs2spaces
     * @return string
     */
    public static function normalizeTabs(string $string, bool $tabs2spaces = false) : string
    {
        $normalizer = new ConvertHelper_TabsNormalizer();
        $normalizer->convertTabsToSpaces($tabs2spaces);

        return $normalizer->normalize($string);
    }

    /**
     * Converts tabs to spaces in the specified string.
     *
     * @param string $string
     * @param int $tabSize The amount of spaces per tab.
     * @return string
     */
    public static function tabs2spaces(string $string, int $tabSize=4) : string
    {
        return str_replace("\t", str_repeat(' ', $tabSize), $string);
    }

    /**
     * Converts spaces to tabs in the specified string.
     *
     * @param string $string
     * @param int $tabSize The amount of spaces per tab in the source string.
     * @return string
     */
    public static function spaces2tabs(string $string, int $tabSize=4) : string
    {
        return str_replace(str_repeat(' ', $tabSize), "\t", $string);
    }

    /**
     * Makes all hidden characters visible in the target string,
     * from spaces to control characters.
     *
     * @param string $string
     * @return string
     */
    public static function hidden2visible(string $string) : string
    {
        return (new ConvertHelper_HiddenConverter())->convert($string);
    }

    /**
     * UTF8-safe wordwrap method: works like the regular wordwrap
     * PHP function but compatible with UTF8. Otherwise, the lengths
     * are not calculated correctly.
     *
     * @param string $str
     * @param int $width
     * @param string $break
     * @param bool $cut
     * @return string
     */
    public static function wordwrap(string $str, int $width = 75, string $break = "\n", bool $cut = false) : string
    {
        $wrapper = new ConvertHelper_WordWrapper();

        return $wrapper
            ->setLineWidth($width)
            ->setBreakCharacter($break)
            ->setCuttingEnabled($cut)
            ->wrapText($str);
    }

    /**
     * Transliterates a string.
     *
     * @param string $string
     * @param string $spaceChar
     * @param bool $lowercase
     * @return string
     */
    public static function transliterate(string $string, string $spaceChar = '-', bool $lowercase = true) : string
    {
        $transliterate = new Transliteration();
        $transliterate->setSpaceReplacement($spaceChar);

        if ($lowercase)
        {
            $transliterate->setLowercase();
        }

        return $transliterate->convert($string);
    }

    /**
     * Cuts a text to the specified length if it is longer than the
     * target length. Appends a text to signify it has been cut at
     * the end of the string.
     *
     * @param string $text
     * @param int $targetLength
     * @param string $append
     * @return string
     */
    public static function cutText(string $text, int $targetLength, string $append = '...') : string
    {
        $length = mb_strlen($text);
        if ($length <= $targetLength) {
            return $text;
        }

        return trim(mb_substr($text, 0, $targetLength)) . $append;
    }

    /**
     * Like explode, but trims all entries, and removes
     * empty entries from the resulting array.
     *
     * @param string $delimiter
     * @param string $string
     * @return string[]
     */
    public static function explodeTrim(string $delimiter, string $string) : array
    {
        if(empty($string) || empty($delimiter)) {
            return array();
        }

        $tokens = explode($delimiter, $string);
        $tokens = array_map('trim', $tokens);

        $keep = array();
        foreach($tokens as $token) {
            if($token !== '') {
                $keep[] = $token;
            }
        }

        return $keep;
    }

    /**
     * Whether the specified string contains unicode characters.
     *
     * @param string $string
     * @return bool
     */
    public static function isUnicode(string $string) : bool
    {
        return strlen($string) !== strlen(utf8_decode($string));
    }

    /**
     * Checks whether the specified character is an uppercase character,
     * with Unicode support.
     *
     * @param string $char A single character, or a longer string. Only the first character will be used.
     * @return bool
     * @see https://stackoverflow.com/a/55992883/2298192
     */
    public static function isCharUppercase(string $char) : bool
    {
        if(mb_strlen($char) > 1) {
            $char = mb_substr($char, 0, 1);
        }

        return preg_match('~^\p{Lu}~u', $char) === 1;
    }

    /**
     * Converts a camel case string to snake case (with underscores),
     * with Unicode support.
     *
     * Examples:
     *
     * - camelCase > camel_case
     * - camelCaseString > camel_case_string
     * - CamelCase > camel_case
     * - CamelACase > camel_a_case
     * - ÖffnenDasFenster > öffnen_das_fenster
     *
     * @param string $camelCase
     * @param bool $transliterate
     * @return string
     */
    public static function camel2snake(string $camelCase, bool $transliterate=false) : string
    {
        $result = '';
        $camelCase = self::removeSpecialChars($camelCase, '_');

        foreach(self::toArray($camelCase) as $char)
        {
            if (self::isCharUppercase($char)) {
                $result .= '_' . mb_strtolower($char, 'UTF-8');
            } else {
                $result .= $char;
            }
        }

        if($transliterate) {
            $result = self::transliterate($result, '_');
        }

        return implode('_', self::explodeTrim('_', $result));
    }

    public static function snake2camel(string $snakeCase, bool $transliterate=false) : string
    {
        if(strpos($snakeCase, '_') === false) {
            return $snakeCase;
        }

        $snakeCase = mb_strtolower(self::toUtf8($snakeCase));

        if($transliterate) {
            $snakeCase = self::transliterate($snakeCase, '_', false);
        }

        return self::words2Camel(self::explodeTrim('_', $snakeCase));
    }

    /**
     * Extracts all words from the target string and returns them
     * as an indexed array. Ignores all special characters.
     *
     * @param string $subject
     * @param string[]|NULL $wordChars Characters to include in words, e.g. "_" so that "foo_bar" is matched as a word.
     * @return string[]|null
     */
    public static function explodeWords(string $subject, ?array $wordChars=null) : ?array
    {
        $subject = self::removeSpecialChars($subject, ' ', $wordChars);

        return preg_split("/ +/", $subject, 0, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Removes punctuation, white space and other special characters from
     * the string. Unicode-safe.
     *
     * @param string $subject
     * @param string $replaceChar
     * @param string[]|null $preserveChars Special characters to preserve.
     * @return string
     */
    public static function removeSpecialChars(string $subject, string $replaceChar='', ?array $preserveChars=array()) : string
    {
        self::toUtf8($subject);

        $replaces = array();

        // Replace the specified characters with something that will not
        // be stripped by the normalization regex.
        if(!empty($preserveChars)) {
            $wordChars = array_unique($preserveChars);
            $counter = 0;
            foreach ($preserveChars as $char) {
                $replaces[$char] = '9aw' . $counter . 'wa9';
                $counter++;
            }

            $subject = str_replace(array_keys($replaces), array_values($replaces), $subject);
        }

        // Replace all special characters and white space with spaces.
        // With the unicode flag, punctuation includes things like EM dashes.
        $subject = preg_replace('/[[:punct:]´€°\s]/u', $replaceChar, $subject);

        // Restore the characters to keep
        if(!empty($wordChars)) {
            $subject = str_replace(array_values($replaces), array_keys($replaces), $subject);
        }

        return $subject;
    }

    private static function words2Camel(array $words) : string
    {
        $result = '';
        foreach(array_values($words) as $idx => $part)
        {
            if($idx > 0) {
                $part = self::ucFirst($part);
            }

            $result .= $part;
        }

        return $result;
    }

    public static function toCamel(string $subject, bool $transliterate=false) : string
    {
        $subject = self::toUtf8($subject);

        if($transliterate) {
            $subject = self::transliterate($subject, ' ');
        }

        return self::words2Camel(self::explodeWords(mb_strtolower($subject)));
    }

    public static function toSnake(string $subject, bool $transliterate=false) : string
    {
        $subject = self::toUtf8($subject);

        if($transliterate) {
            $subject = self::transliterate($subject, ' ');
        }

        return implode('_', self::explodeWords(mb_strtolower($subject)));
    }

    /**
     * Unicode-safe <code>ucfirst</code> implementation.
     *
     * @param string $subject
     * @return string
     */
    public static function ucFirst(string $subject) : string
    {
        if($subject === '') {
            return '';
        }

        $chars = self::toArray($subject);
        $chars[0] = mb_strtoupper($chars[0]);

        return implode('', $chars);
    }
}
