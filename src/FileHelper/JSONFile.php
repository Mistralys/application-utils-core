<?php
/**
 * @package AppUtils
 * @subpackage FileHelper
 */

declare(strict_types=1);

namespace AppUtils\FileHelper;

use AppUtils\ClassHelper;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\FileHelper;
use AppUtils\FileHelper\JSONFile\JSONFileOptions;
use AppUtils\FileHelper_Exception;
use JsonException;
use SplFileInfo;
use function AppUtils\sb;

/**
 * Specialized file handler for JSON encoded files.
 *
 * @package AppUtils
 * @subpackage FileHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class JSONFile extends FileInfo
{
    public const EXTENSION = 'json';

    /**
     * @var string
     */
    private string $targetEncoding = '';

    /**
     * @var string|string[]|NULL
     */
    private $sourceEncodings = '';

    /**
     * @var JSONFileOptions|mixed
     */
    private $options;

    /**
     * @param string|PathInfoInterface|SplFileInfo $path
     * @return JSONFile
     * @throws FileHelper_Exception
     */
    public static function factory($path) : JSONFile
    {
        return ClassHelper::requireObjectInstanceOf(
            self::class,
            self::createInstance($path)
        );
    }

    protected function init(): void
    {
        parent::init();

        $this->options = new JSONFileOptions();
    }

    public function options() : JSONFileOptions
    {
        return $this->options;
    }

    /**
     * Whether to add a trailing newline at the end of the JSON file.
     *
     * @param bool $enabled
     * @return $this
     */
    public function setTrailingNewline(bool $enabled) : self
    {
        $this->options->setTrailingNewline($enabled);
        return $this;
    }

    /**
     * Whether to escape slashes in the JSON data values.
     * @param bool $enabled
     * @return $this
     */
    public function setEscapeSlashes(bool $enabled) : self
    {
        $this->options->setEscapeSlashes($enabled);
        return $this;
    }

    /**
     * Whether to indent and prettify the JSON output.
     * @param bool $enabled
     * @return $this
     */
    public function setPrettyPrint(bool $enabled) : self
    {
        $this->options->setPrettyPrint($enabled);
        return $this;
    }

    /**
     * @param string $targetEncoding
     * @return $this
     */
    public function setTargetEncoding(string $targetEncoding) : self
    {
        $this->targetEncoding = $targetEncoding;
        return $this;
    }

    /**
     * @param string|string[]|NULL $sourceEncodings
     * @return $this
     */
    public function setSourceEncodings($sourceEncodings) : self
    {
        $this->sourceEncodings = $sourceEncodings;
        return $this;
    }

    /**
     * Alias for {@see JSONFile::parse()}.
     * @return array<int|string,mixed>
     * @throws FileHelper_Exception
     */
    public function getData() : array
    {
        return $this->parse();
    }

    /**
     * Opens a serialized file and returns the unserialized data.
     * Only supports serialized arrays - classes are not allowed.
     *
     * @return array<int|string,mixed>
     * @throws FileHelper_Exception
     * @see FileHelper::parseSerializedFile()
     *
     * @see FileHelper::ERROR_FILE_DOES_NOT_EXIST
     * @see FileHelper::ERROR_SERIALIZED_FILE_CANNOT_BE_READ
     * @see FileHelper::ERROR_SERIALIZED_FILE_UNSERIALZE_FAILED
     */
    public function parse() : array
    {
        try
        {
            return json_decode(
                $this->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }
        catch (JsonException $e)
        {
            throw new FileHelper_Exception(
                'Cannot decode json data',
                (string)sb()
                    ->sf(
                        'Loaded the contents of file [%s] successfully, but decoding it as JSON failed.',
                        $this->getPath()
                    )
                    ->eol()
                    ->sf('Source encodings: [%s]', JSONConverter::var2jsonSilent($this->sourceEncodings))
                    ->eol()
                    ->sf('Target encoding: [%s]', $this->targetEncoding),
                FileHelper::ERROR_CANNOT_DECODE_JSON_FILE,
                $e
            );
        }
    }

    public function getContents() : string
    {
        return $this->convertEncoding(parent::getContents());
    }

    private function convertEncoding(string $contents) : string
    {
        if(!empty($this->targetEncoding))
        {
            return (string)mb_convert_encoding(
                $contents,
                $this->targetEncoding,
                $this->sourceEncodings
            );
        }

        return $contents;
    }

    public function getJSONOptionsBitmask() : int
    {
        $options = 0;

        if($this->options->isPrettyPrintEnabled()) {
            $options = $options | JSON_PRETTY_PRINT;
        }

        if(!$this->options->isEscapeSlashesEnabled()) {
            $options = $options | JSON_UNESCAPED_SLASHES;
        }

        return $options;
    }

    /**
     * @param mixed $data
     * @param bool $pretty
     * @return $this
     * @throws FileHelper_Exception
     */
    public function putData($data, ?bool $pretty=false) : self
    {
        if($pretty === true) {
            $this->setPrettyPrint(true);
        }

        try
        {
            $json = JSONConverter::var2json($data, $this->getJSONOptionsBitmask());

            if($this->options->isTrailingNewlineEnabled()) {
                $json .= PHP_EOL;
            }

            $this->putContents($json);

            return $this;
        }
        catch (JSONConverterException $e)
        {
            throw new FileHelper_Exception(
                'An error occurred while encoding a data set to JSON.',
                sprintf('Tried saving to file: [%s].', $this->getPath()),
                FileHelper::ERROR_JSON_ENCODE_ERROR,
                $e
            );
        }
    }
}
