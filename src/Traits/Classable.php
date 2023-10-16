<?php
/**
 * @package Application Utils
 * @subpackage Traits
 * @see \AppUtils\Traits\ClassableTrait
 */

namespace AppUtils;

/**
 * @deprecated Use {@see ClassableTrait} instead.
 */
trait Traits_Classable
{
   /**
    * @var string[]
    */
    protected array $classes = array();

    public function hasClasses() : bool
    {
        return !empty($this->classes);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function addClass($name)
    {
        if(!in_array($name, $this->classes, true)) {
            $this->classes[] = $name;
        }
        
        return $this;
    }

    /**
     * @param string[] $names
     * @return $this
     */
    public function addClasses(array $names) : self
    {
        foreach($names as $name) {
            $this->addClass($name);
        }
        
        return $this;
    }
    
    public function hasClass(string $name) : bool
    {
        return in_array($name, $this->classes, true);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeClass(string $name) : self
    {
        $idx = array_search($name, $this->classes, true);
        
        if($idx !== false) {
            unset($this->classes[$idx]);
            sort($this->classes);
        }
        
        return $this;
    }
    
   /**
    * Retrieves a list of all classes, if any.
    * 
    * @return string[]
    */
    public function getClasses() : array
    {
        return $this->classes;
    }
    
   /**
    * Renders the class names list as space-separated string for use in an HTML tag.
    * 
    * @return string
    */
    public function classesToString() : string
    {
        return implode(' ', $this->classes);
    }
    
   /**
    * Renders the "class" attribute string for inserting into an HTML tag.
    * @return string
    */
    public function classesToAttribute() : string
    {
        if(!empty($this->classes))
        {
            return sprintf(
                ' class="%s" ',
                $this->classesToString()
            );
        }
        
        return '';
    }
}
