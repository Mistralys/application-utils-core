<?php
/**
 * @package Application Utils
 * @subpackage FileHelper
 * @see \AppUtils\FileHelper\PHPFile
 */

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use AppUtils\FileHelper_PHPClassInfo;
use AppUtils\Interfaces\StringableInterface;
use SplFileInfo;
use function AppUtils\t;

/**
 * Specialized file information class for PHP files.
 *
 * Create an instance with {@see PHPFile::factory()}.
 *
 * @package Application Utils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class PHPFile extends FileInfo
{
    public const string EXTENSION = 'php';

    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return PHPFile
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     * @throws FileHelper_Exception
     */
    public static function factory($path) : PHPFile
    {
        return ClassHelper::requireObjectInstanceOf(
            self::class,
            self::createInstance($path)
        );
    }

    /**
     * Validates a PHP file's syntax.
     *
     * > NOTE: This will fail silently if the PHP command line
     * > is not available. Use {@link FileHelper::canMakePHPCalls()}
     * > to check this beforehand as needed.
     *
     * @return true|string[] A boolean true if the file is valid, an array with validation messages otherwise.
     * @throws FileHelper_Exception
     */
    public function checkSyntax() : true|array
    {
        if(!FileHelper::canMakePHPCalls())
        {
            return true;
        }

        $output = array();
        $command = sprintf('php -l "%s" 2>&1', $this->getPath());
        exec($command, $output);

        // when the validation is successful, the first entry
        // in the array contains the success message. When it
        // is invalid, the first entry is always empty.
        if(!empty($output[0])) {
            return true;
        }

        array_shift($output); // the first entry is always empty
        array_pop($output); // the last message is a superfluous message saying there's an error

        return $output;
    }

    public function findClasses() : FileHelper_PHPClassInfo
    {
        return new FileHelper_PHPClassInfo($this);
    }

    public function getTypeLabel(): string
    {
        return t('PHP File');
    }

    /**
     * Saves the provided PHP statements into the file,
     * wrapping them in PHP tags, and optionally adding
     * strict typing declaration and a namespace.
     *
     * @param string|StringableInterface|string[] $phpCode PHP statements to write. In the case of an array,
     *              each entry is treated as a separate line. Ensure that semicolons and other syntax elements
     *              are included as needed.
     * @param bool $strictTyping
     * @param string|null $namespace
     * @return PHPFile
     * @throws FileHelper_Exception
     */
    public function putStatements(string|StringableInterface|array $phpCode, bool $strictTyping=true, ?string $namespace=null) : PHPFile
    {
        $content = '<'.'?'.'php'.PHP_EOL.PHP_EOL;

        if($strictTyping) {
            $content .= 'declare(strict_types=1);'.PHP_EOL.PHP_EOL;
        }

        if($namespace !== null) {
            $content .= 'namespace '.$namespace.';'.PHP_EOL.PHP_EOL;
        }

        if(is_array($phpCode)) {
            $phpCode = implode(PHP_EOL, $phpCode);
        }

        $content .= $phpCode.PHP_EOL;

        return $this->putContents($content);
    }
}
