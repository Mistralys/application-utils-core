<?php
/**
 * @package Application Utils
 * @subpackage StringHelper
 * @see \AppUtils\StringHelper\WordSplitter
 */

declare(strict_types=1);

namespace AppUtils\StringHelper;

use AppUtils\StringHelper;

/**
 * @package Application Utils
 * @subpackage StringHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class WordSplitter
{
    private string $subject;
    private bool $removeDuplicates = false;
    private bool $sorting = false;
    private int $minWordLength = 0;

    /**
     * @var string[]
     */
    private array $wordCharacters = array();

    private bool $duplicatesCaseInsensitive;

    public function __construct(string $subject)
    {
        $this->subject = $subject;
    }

    public function setRemoveDuplicates(bool $remove=true, bool $caseInsensitive=false) : self
    {
        $this->removeDuplicates = $remove;
        $this->duplicatesCaseInsensitive = $caseInsensitive;
        return $this;
    }

    /**
     * Adds a special character that should be recognized as a word.
     * For example, adding the underscore as word character will
     * preserve any words separated by underscores.
     *
     *<code>addWordCharacter('.')</code>
     *
     * @param string $char
     * @return $this
     */
    public function addWordCharacter(string $char) : self
    {
        if(!in_array($char, $this->wordCharacters, true)) {
            $this->wordCharacters[] = $char;
        }

        return $this;
    }

    /**
     * @param string[] $chars
     * @return $this
     */
    public function addWordCharacters(array $chars) : self
    {
        foreach ($chars as $char) {
            $this->addWordCharacter($char);
        }

        return $this;
    }

    public function setSorting(bool $sorting=true) : self
    {
        $this->sorting = $sorting;
        return $this;
    }

    public function setMinWordLength(int $length) : self
    {
        $this->minWordLength = $length;
        return $this;
    }

    /**
     * @return string[]
     */
    public function split() : array
    {
        $words = StringHelper::explodeWords($this->subject, $this->wordCharacters);

        $words = $this->filterEmpty($words);

        if($this->removeDuplicates) {
            $words = $this->filterDuplicates($words);
        }

        if($this->sorting) {
            usort($words, 'strnatcasecmp');
        }

        return $words;
    }

    /**
     * @param string[] $words
     * @return string[]
     */
    private function filterDuplicates(array $words) : array
    {
        if($this->duplicatesCaseInsensitive) {
            return $this->filterDuplicatesCaseInsensitive($words);
        }

        return array_unique($words);
    }

    /**
     * @param string[] $array
     * @return string[]
     */
    private function filterDuplicatesCaseInsensitive(array $array) : array
    {
        return array_intersect_key(
            $array,
            array_unique( array_map( "strtolower", $array ) )
        );
    }

    /**
     * @param string[] $words
     * @return string[]
     */
    private function filterEmpty(array $words) : array
    {
        $keep = array();

        foreach($words as $word)
        {
            if(empty($word)) {
                continue;
            }

            if(mb_strlen($word) < $this->minWordLength) {
                continue;
            }

            $keep[] = $word;
        }

        return $keep;
    }
}
