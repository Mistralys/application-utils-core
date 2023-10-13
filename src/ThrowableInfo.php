<?php

declare(strict_types=1);

namespace AppUtils;

use AppUtils\ThrowableInfo\ThrowableCall;
use AppUtils\ThrowableInfo\ThrowableMessageRenderer;
use AppUtils\ThrowableInfo\ThrowableSerializer;
use AppUtils\ThrowableInfo\ThrowableStringConverter;
use Exception;
use Throwable;

class ThrowableInfo implements Interface_Optionable
{
    use Traits_Optionable;
    
    public const ERROR_NO_PREVIOUS_EXCEPTION = 43301;
    public const ERROR_INVALID_SERIALIZED_DATA_TYPE = 43302;
    
    public const FORMAT_HTML = 'html';

    public const CONTEXT_COMMAND_LINE = 'cli';
    public const CONTEXT_WEB = 'web';

    /**
    * @var Throwable
    */
    protected $exception;
    
   /**
    * @var ThrowableCall[]
    */
    protected $calls = array();
    
   /**
    * @var integer
    */
    protected $code = 0;
    
   /**
    * @var string
    */
    protected $message;
    
   /**
    * @var integer
    */
    protected $callsCount = 0;
    
   /**
    * @var ThrowableInfo|NULL
    */
    protected $previous = null;
    
   /**
    * @var string
    */
    protected $referer = '';
    
   /**
    * @var Microtime
    */
    protected $date;
    
   /**
    * @var string
    */
    protected $context = self::CONTEXT_WEB;

    /**
     * @var string
     */
    private $class = '';

    /**
     * @var string
     */
    private $details = '';

    /**
     * @param array<string,mixed>|Throwable $subject
     * @throws Exception
     */
    protected function __construct($subject)
    {
        if(is_array($subject))
        {
            $this->parseSerialized($subject);
        }
        else
        {
            $this->parseException($subject);
        }
    }
    
    public static function fromThrowable(Throwable $e) : ThrowableInfo
    {
        return new ThrowableInfo($e);
    }

    /**
     * @param array<string,mixed> $serialized
     * @return ThrowableInfo
     * @throws ConvertHelper_Exception
     */
    public static function fromSerialized(array $serialized) : ThrowableInfo
    {
        return new ThrowableInfo(ThrowableSerializer::unserialize($serialized));
    }
    
    public function getCode() : int
    {
        return $this->code;
    }
    
    public function getMessage() : string
    {
        return $this->message;
    }

    public function getDefaultOptions() : array
    {
        return array(
            'folder-depth' => 2
        );
    }
    
    public function hasPrevious() : bool
    {
        return isset($this->previous);
    }
    
   /**
    * Retrieves the information on the previous exception.
    * 
    * NOTE: Throws an exception if there is no previous 
    * exception. Use hasPrevious() first to avoid this.
    * 
    * @return ThrowableInfo
    * @throws ConvertHelper_Exception
    * @see ThrowableInfo::ERROR_NO_PREVIOUS_EXCEPTION
    */
    public function getPrevious() : ThrowableInfo
    {
        if(isset($this->previous)) {
            return $this->previous;
        }
        
        throw new ConvertHelper_Exception(
            'Cannot get previous exception info: none available.',
            'Always use hasPrevious() before using getPrevious() to avoid this error.',
            self::ERROR_NO_PREVIOUS_EXCEPTION
        );
    }
    
    public function hasCode() : bool
    {
        return !empty($this->code);
    }
    
   /**
    * Improved text-only exception trace.
    */
    public function toString() : string
    {
        return (new ThrowableStringConverter($this))
            ->toString();
    }
    
   /**
    * Retrieves the URL of the page in which the exception
    * was thrown, if applicable: in CLI context, this will
    * return an empty string.
    * 
    * @return string
    */
    public function getReferer() : string
    {
        return $this->referer;
    }
    
   /**
    * Whether the exception occurred in a command line context.
    * @return bool
    */
    public function isCommandLine() : bool
    {
        return $this->getContext() === self::CONTEXT_COMMAND_LINE;
    }
    
   /**
    * Whether the exception occurred during an http request.
    * @return bool
    */
    public function isWebRequest() : bool
    {
        return $this->getContext() === self::CONTEXT_WEB;
    }
    
   /**
    * Retrieves the context identifier, i.e. if the exception
    * occurred in a command line context or regular web request.
    * 
    * @return string
    * 
    * @see ThrowableInfo::isCommandLine()
    * @see ThrowableInfo::isWebRequest()
    * @see ThrowableInfo::CONTEXT_COMMAND_LINE
    * @see ThrowableInfo::CONTEXT_WEB
    */
    public function getContext() : string
    {
        return $this->context;
    }
    
   /**
    * Retrieves the date of the exception, and approximate time:
    * since exceptions do not store time, this is captured the 
    * moment the ThrowableInfo is created.
    * 
    * @return Microtime
    */
    public function getDate() : Microtime
    {
        return $this->date;
    }

    /**
     * Serializes all information on the exception to an
     * associative array. This can be saved (file, database,
     * session...), and later be restored into a throwable
     * info instance using the fromSerialized() method.
     *
     * @return array<string,mixed>
     * @throws ConvertHelper_Exception
     * @see ThrowableInfo::fromSerialized()
     */
    public function serialize() : array
    {
        return ThrowableSerializer::serialize($this);
    }

   /**
    * Sets the maximum folder depth to show in the 
    * file paths, to avoid them being too long.
    * 
    * @param int $depth
    * @return ThrowableInfo
    */
    public function setFolderDepth(int $depth) : ThrowableInfo
    {
        return $this->setOption('folder-depth', $depth);
    }
    
   /**
    * Retrieves the current folder depth option value.
    * 
    * @return int
    * @see ThrowableInfo::setFolderDepth()
    */
    public function getFolderDepth() : int
    {
        $depth = $this->getOption('folder-depth');
        if(!empty($depth)) {
            return $depth;
        }
        
        return 2;
    }
    
   /**
    * Retrieves all function calls that led to the error,
    * ordered from latest to earliest (the first one in
    * the stack is actually the last call).
    *
    * @return ThrowableCall[]
    */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * Retrieves the last call that led to the error.
     *
     * @return ThrowableCall
     */
    public function getFinalCall() : ThrowableCall
    {
        return $this->calls[0];
    }
    
   /**
    * Returns the amount of function and method calls in the stack trace.
    * @return int
    */
    public function countCalls() : int
    {
        return $this->callsCount;
    }


    /**
     * @param array<string,mixed> $serialized
     * @throws Exception
     */
    private function parseSerialized(array $serialized) : void
    {
        $this->date = new Microtime($serialized[ThrowableSerializer::SERIALIZED_DATE]);
        $this->class = $serialized[ThrowableSerializer::SERIALIZED_CLASS];
        $this->details = $serialized[ThrowableSerializer::SERIALIZED_DETAILS];
        $this->code = $serialized[ThrowableSerializer::SERIALIZED_CODE];
        $this->message = $serialized[ThrowableSerializer::SERIALIZED_MESSAGE];
        $this->referer = $serialized[ThrowableSerializer::SERIALIZED_REFERER];
        $this->context = $serialized[ThrowableSerializer::SERIALIZED_CONTEXT];
        $this->callsCount = $serialized[ThrowableSerializer::SERIALIZED_AMOUNT_CALLS];
        $this->previous = $serialized[ThrowableSerializer::SERIALIZED_PREVIOUS];

        $this->setOptions($serialized[ThrowableSerializer::SERIALIZED_OPTIONS]);
        
        foreach($serialized[ThrowableSerializer::SERIALIZED_CALLS] as $def)
        {
            $this->calls[] = ThrowableCall::fromSerialized($this, $def);
        }
    }
    
    protected function parseException(Throwable $e) : void
    {
        $this->date = new Microtime();
        $this->class = get_class($e);

        if($e instanceof BaseException)
        {
            $this->details = $e->getDetails();
        }

        $this->parseMessage($e);
        $this->parsePrevious($e);
        $this->parseContext();
        $this->parseTrace($e);
    }

    /**
     * Retrieves the class name of the exception.
     *
     * @return string
     */
    public function getClass() : string
    {
        return $this->class;
    }

    /**
     * Converts the exception's information into a human-
     * readable string containing the exception's essentials.
     *
     * It includes any previous exceptions as well, recursively.
     *
     * @param bool $withDeveloperInfo Whether to include developer-specific info
     *                                when available (which may include sensitive
     *                                information).
     * @return string
     * @throws ConvertHelper_Exception
     */
    public function renderErrorMessage(bool $withDeveloperInfo=false) : string
    {
        return (new ThrowableMessageRenderer($this, $withDeveloperInfo))
            ->render();
    }

    public function getDetails() : string
    {
        return $this->details;
    }

    public function hasDetails() : bool
    {
        return !empty($this->details);
    }
    
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @param Throwable $e
     */
    private function parseTrace(Throwable $e) : void
    {
        $trace = $e->getTrace();

        // add the origin file as entry
        array_unshift($trace, array(
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ));

        $idx = 1;

        foreach ($trace as $entry)
        {
            $this->calls[] = ThrowableCall::fromTrace($this, $idx, $entry);

            $idx++;
        }

        // we want the last function call first
        $this->calls = array_reverse($this->calls, false);

        $this->callsCount = count($this->calls);
    }

    private function parseContext() : void
    {
        if (!isset($_REQUEST['REQUEST_URI']))
        {
            $this->context = self::CONTEXT_COMMAND_LINE;
        }

        if (isset($_SERVER['REQUEST_URI']))
        {
            $this->referer = $_SERVER['REQUEST_URI'];
        }
    }

    /**
     * @param Throwable $e
     */
    private function parseMessage(Throwable $e) : void
    {
        $code = $e->getCode();
        $this->message = $e->getMessage();

        if (is_integer($code))
        {
            $this->code = $code;
        }
        else
        {
            $this->message = 'Original error code: [' . $code . ']. ' . $this->message;
        }
    }

    /**
     * @param Throwable $e
     */
    protected function parsePrevious(Throwable $e) : void
    {
        $previous = $e->getPrevious();

        if (!empty($previous))
        {
            $this->previous = ConvertHelper::throwable2info($previous);
        }
    }
}
