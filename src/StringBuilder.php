<?php
/**
 * File containing the {@link StringBuilder} class.
 *
 * @package Application Utils
 * @subpackage StringBuilder
 * @see StringBuilder
 */

declare(strict_types=1);

namespace AppUtils;

use AppUtils\Interfaces\StringableInterface;
use DateTime;
use AppLocalize;
use Exception;

/**
 * Utility class used to easily concatenate strings
 * with a chainable interface. 
 * 
 * Each bit of text that is added is automatically 
 * separated by spaces, making it easy to write
 * texts without handling this separately.
 * 
 * Specialized methods help in quickly formatting 
 * text, or adding common HTML-based contents.
 *
 * @package Application Utils
 * @subpackage StringBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see StringBuilder
 */
class StringBuilder implements StringBuilder_Interface
{
    public const ERROR_CALLABLE_THREW_ERROR = 99601;

   /**
    * @var string
    */
    protected string $separator = ' ';

   /**
    * @var string[]
    */
    protected array $strings = array();

   /**
    * @var string
    */
    protected string $noSeparator = '§!§';
    protected bool $separatorEnabled = true;
    
    public function __construct()
    {
        
    }

    public function setSeparator(string $separator) : StringBuilder
    {
        $this->separator = $separator;
        return $this;
    }
    
   /**
    * Adds a subject as a string. Is ignored if empty.
    * 
    * @param string|number|StringableInterface|NULL $string
    * @return $this
    */
    public function add($string) : StringBuilder
    {
        $string = (string)$string;

        if(!empty($string))
        {
            if(!$this->separatorEnabled) {
                $string = $this->noSeparator.$string;
                $this->separatorEnabled = true;
            }

            $this->strings[] = $string;
        }

        // Reset the classes list after each addition.
        $this->classes = array();

        return $this;
    }
    
   /**
    * Adds a string without appending an automatic space.
    * 
    * @param string|number|StringableInterface|NULL $string
    * @return $this
    */
    public function nospace($string) : StringBuilder
    {
        return $this->useNoSpace()->add($string);
    }
    
   /**
    * Adds raw HTML code. Does not add an automatic space.
    * 
    * @param string|number|StringableInterface|NULL $html
    * @return $this
    */
    public function html($html) : StringBuilder
    {
        return $this->nospace($html);
    }
    
   /**
    * Adds an unordered list with the specified items.
    * 
    * @param array<int,string|number|StringableInterface|NULL> $items
    * @return $this
    */
    public function ul(array $items) : StringBuilder
    {
        return $this->list('ul', $items);
    }
    
   /**
    * Adds an ordered list with the specified items.
    * 
    * @param array<int,string|number|StringableInterface|NULL> $items
    * @return $this
    */
    public function ol(array $items) : StringBuilder
    {
        return $this->list('ol', $items);
    }
    
   /**
    * Creates a list tag with the items list.
    * 
    * @param string $type The list type, `ol` or `ul`.
    * @param array<int,string|number|StringableInterface|NULL> $items
    * @return $this
    */
    protected function list(string $type, array $items) : StringBuilder
    {
        return $this->html(sprintf(
            '<%1$s><li>%2$s</li></%1$s>',
            $type,
            implode('</li><li>', $items)
        ));
    }
    
   /**
    * Add a translated string.
    * 
    * @param string $format The native string to translate.
    * @param array<int,mixed> $arguments The variables to inject into the translated string, if any.
    * @return $this
    */
    public function t(string $format, ...$arguments) : StringBuilder
    {
        if(!class_exists('\AppLocalize\Localization'))
        {
            array_unshift($arguments, $format);
            return $this->sf(...$arguments);
        }
        
        return $this->add(call_user_func(
            array(AppLocalize\Localization::getTranslator(), 'translate'),
            $format,
            $arguments
        ));
    }

    /**
     * Add a translated text with translation context information.
     *
     * @param string $format The native string to translate.
     * @param string $context Translation context hints, shown in the translation UI.
     * @param mixed ...$arguments
     * @return $this
     */
    public function tex(string $format, string $context, ...$arguments) : StringBuilder
    {
        unset($context); // Only used by the localization parser.

        if(!class_exists('\AppLocalize\Localization'))
        {
            array_unshift($arguments, $format);
            return $this->sf(...$arguments);
        }

        return $this->add(call_user_func(
            array(AppLocalize\Localization::getTranslator(), 'translate'),
            $format,
            $arguments
        ));
    }

    /**
     * Adds a "5 months ago" age since the specified date.
     *
     * @param DateTime $since
     * @return $this
     * @throws ConvertHelper_Exception
     */
    public function age(DateTime $since) : StringBuilder
    {
        return $this->add(ConvertHelper::duration2string($since));
    }
    
   /**
    * Adds HTML double quotes around the string.
    * 
    * @param string|number|StringableInterface $string
    * @return $this
    */
    public function quote($string) : StringBuilder
    {
        return $this->sf('&quot;%s&quot;', (string)$string);
    }
    
   /**
    * Adds a text that is meant as a reference to a UI element,
    * like a menu item, button, etc.
    * 
    * @param string|number|StringableInterface $string
    * @return $this
    */
    public function reference($string) : StringBuilder
    {
        return $this->sf('"%s"', $string);
    }

   /**
    * Add a string using the `sprintf` method.
    * 
    * @param string $format The format string
    * @param string|number|StringableInterface ...$arguments The variables to inject
    * @return $this
    */
    public function sf(string $format, ...$arguments) : StringBuilder
    {
        array_unshift($arguments, $format);
        
        return $this->add(sprintf(...$arguments));
    }
    
   /**
    * Adds a bold string.
    * 
    * @param string|number|StringableInterface $string
    * @return $this
    */
    public function bold($string) : StringBuilder
    {
        return $this->tag('b', $string);
    }
    
   /**
    * Adds an HTML `<br>` tag.
    *
    * Note: for adding a newline character instead,
    * use {@see StringBuilder::eol()}.
    * 
    * @return $this
    * @see StringBuilder::eol()
    */
    public function nl() : StringBuilder
    {
        return $this->html('<br>');
    }

    /**
     * Adds an EOL character, without space.
     *
     * @return $this
     * @see StringBuilder::nl()
     */
    public function eol() : StringBuilder
    {
        return $this->nospace(PHP_EOL);
    }
    
   /**
    * Adds the current time, in the format <code>H:i:s</code>.
    * 
    * @return $this
    */
    public function time() : StringBuilder
    {
        return $this->add(date('H:i:s'));
    }
    
   /**
    * Adds the "Note:" text.
    * 
    * @return $this
    */
    public function note() : StringBuilder
    {
        return $this->t('Note:');
    }
    
   /**
    * Like {@see StringBuilder::note()}, but as bold text.
    * 
    * @return $this
    */
    public function noteBold() : StringBuilder
    {
        return $this->bold(sb()->note());
    }
    
   /**
    * Adds the "Hint:" text.
    * 
    * @return $this
    * @see StringBuilder::hintBold()
    */
    public function hint() : StringBuilder
    {
        return $this->t('Hint:');
    }

    /**
     * Like {@see StringBuilder::hint()}, but as bold text.
     *
     * @return $this
     */
    public function hintBold() : StringBuilder
    {
        return $this->bold(sb()->hint());
    }

   /**
    * Adds two linebreaks.
    *
    * @param StringBuilder_Interface|string|number|NULL $content
    * @return $this
    */
    public function para($content=null) : StringBuilder
    {
        if($content !== null)
        {
            $content = (string)$content;
            if($content === '') {
                return $this;
            }

            return $this->html(sprintf('<p%s>', $this->compileClasses()))->nospace($content)->html('</p>');
        }

        return $this->nl()->nl();
    }

    /**
     * Adds an anchor HTML tag.
     *
     * @param string|StringableInterface $label
     * @param string|StringableInterface $url
     * @param bool $newTab
     * @param AttributeCollection|null $attributes
     * @return $this
     */
    public function link($label, $url, bool $newTab=false, ?AttributeCollection $attributes=null) : StringBuilder
    {
        return $this->add($this->createLink((string)$label, (string)$url, $newTab, $attributes));
    }

    private function createLink(string $label, string $url, bool $newTab=false, ?AttributeCollection $attributes=null) : HTMLTag
    {
        if($attributes === null)
        {
            $attributes = AttributeCollection::create();
        }

        $attributes->href($url);

        if($newTab)
        {
            $attributes->target();
        }

        return HTMLTag::create('a', $attributes)
            ->addText($label);
    }

    public function linkOpen(string $url, bool $newTab=false, ?AttributeCollection $attributes=null) : StringBuilder
    {
        return $this->html($this->createLink('', $url, $newTab, $attributes)->renderOpen());
    }

    public function linkClose() : StringBuilder
    {
        return $this->html(HTMLTag::create('a')->renderClose());
    }

   /**
    * Wraps the string in a `code` tag.
    * 
    * @param string|number|StringableInterface $string
    * @return $this
    */
    public function code($string) : StringBuilder
    {
        return $this->tag('code', $string);
    }
    
   /**
    * Wraps the string in a `pre` tag.
    * 
    * @param string|number|StringableInterface|NULL $string
    * @return $this
    */
    public function pre($string) : StringBuilder
    {
        return $this->tag('pre', $string);
    }
    
   /**
    * Wraps the text in a `span` tag with the specified classes.
    * 
    * @param string|number|StringableInterface $string
    * @param string|string[] $classes
    * @return $this
    */
    public function spanned($string, $classes) : StringBuilder
    {
        if(is_array($classes))
        {
            $this->useClasses($classes);
        }
        else
        {
            $this->useClass($classes);
        }
        
        return $this->tag('span', $string);
    }

    /**
     * @param string|bool|int $value
     * @param bool $yesNo
     * @return $this
     * @throws ConvertHelper_Exception
     */
    public function bool($value, bool $yesNo=false) : StringBuilder
    {
        return $this->add(ConvertHelper::bool2string($value, $yesNo));
    }

    /**
     * Adds the specified content only if the condition is true.
     * Use a callback to render the content to avoid rendering it
     * even if the condition is false.
     *
     * @param bool $condition
     * @param string|number|StringableInterface|NULL|callable $content
     * @return $this
     *
     * @throws StringBuilder_Exception
     * @see StringBuilder::ERROR_CALLABLE_THREW_ERROR
     */
    public function ifTrue(bool $condition, $content) : StringBuilder
    {
        if($condition === true)
        {
            $this->add($this->renderContent($content));
        }

        return $this;
    }

    /**
     * Adds the specified content only if the condition is false.
     * Use a callback to render the content to avoid rendering it
     * even if the condition is true.
     *
     * @param bool $condition
     * @param string|number|StringableInterface|callable|NULL $string
     * @return $this
     *
     * @throws StringBuilder_Exception
     * @see StringBuilder::ERROR_CALLABLE_THREW_ERROR
     */
    public function ifFalse(bool $condition, $string) : StringBuilder
    {
        if($condition === false)
        {
            $this->add($this->renderContent($string));
        }

        return $this;
    }

    /**
     * Handles callbacks used to render content on demand when
     * it is needed. All other values are simply passed through.
     *
     * @param string|number|StringableInterface|callable|NULL $content
     * @return string|number|StringableInterface|NULL
     *
     * @throws StringBuilder_Exception
     * @see StringBuilder::ERROR_CALLABLE_THREW_ERROR
     */
    private function renderContent($content)
    {
        if (!is_callable($content))
        {
            return $content;
        }

        try
        {
            return $content();
        }
        catch (Exception $e)
        {
            throw new StringBuilder_Exception(
                'The callable has thrown an error.',
                sprintf(
                    'The callable [%s] has thrown an exception when it was called.',
                    ConvertHelper::callback2string($content)
                ),
                self::ERROR_CALLABLE_THREW_ERROR,
                $e
            );
        }
    }

    /**
     * @param mixed $subject
     * @param string|number|StringableInterface|callable|NULL $content
     * @return $this
     *
     * @throws StringBuilder_Exception
     * @see StringBuilder::ERROR_CALLABLE_THREW_ERROR
     */
    public function ifEmpty($subject, $content) : StringBuilder
    {
        return $this->ifTrue(empty($subject), $content);
    }

    /**
     * @param mixed $subject
     * @param string|number|StringableInterface|callable|NULL $content
     * @return $this
     *
     * @throws StringBuilder_Exception
     * @see StringBuilder::ERROR_CALLABLE_THREW_ERROR
     */
    public function ifNotEmpty($subject, $content) : StringBuilder
    {
        return $this->ifFalse(empty($subject), $content);
    }

    /**
     * Adds the contents depending on the condition is true.
     * Use callbacks to render the contents to avoid rendering
     * them even when they are not needed.
     *
     * @param bool $condition
     * @param string|number|StringableInterface|callable|NULL $ifTrue
     * @param string|number|StringableInterface|callable|NULL $ifFalse
     * @return $this
     *
     * @throws StringBuilder_Exception
     * @see StringBuilder::ERROR_CALLABLE_THREW_ERROR
     */
    public function ifOr(bool $condition, $ifTrue, $ifFalse) : StringBuilder
    {
        if($condition === true)
        {
            return $this->add($this->renderContent($ifTrue));
        }

        return $this->add($this->renderContent($ifFalse));
    }

    /**
     * @var string[]
     */
    private array $classes = array();

    public function useClasses(array $classes) : StringBuilder
    {
        $this->classes = $classes;
        return $this;
    }

    public function useClass(string $class) : StringBuilder
    {
        return $this->useClasses(array($class));
    }

    private function compileClasses() : string
    {
        if(empty($this->classes))
        {
            return '';
        }

        sort($this->classes);

        return ' class="'.implode(' ', $this->classes).'"';
    }

    /**
     * @param string|int|float|StringableInterface|NULL $string
     * @return $this
     */
    public function italic($string) : StringBuilder
    {
        return $this->tag(
            'i',
            $string
        );
    }

    public function tag(string $name, $content) : StringBuilder
    {
        $content = (string)$content;

        if(empty($content))
        {
            return $this;
        }

        return $this->sf(
            '<%1$s%2$s>%3$s</%1$s>',
            $name,
            $this->compileClasses(),
            $content
        );
    }

    public function useNoSpace() : StringBuilder
    {
        $this->separatorEnabled = false;
        return $this;
    }

    public function render() : string
    {
        $result = implode($this->separator, $this->strings);
        
        return str_replace(array($this->separator.$this->noSeparator, $this->noSeparator), '', $result);
    }
    
    public function __toString()
    {
        return $this->render();
    }
    
    public function display() : void
    {
        echo $this->render();
    }
}
