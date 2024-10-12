<?php

declare(strict_types=1);

namespace AppUtils\FileHelper\JSONFile;

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\OptionableTrait;

class JSONFileOptions implements OptionableInterface
{
    use OptionableTrait;

    public const DEFAULT_PRETTY_PRINT = false;
    public const DEFAULT_TRAILING_NEWLINE = false;
    public const DEFAULT_ESCAPE_SLASHES = true;
    public const OPTION_PRETTY_PRINT = 'prettyPrint';
    public const OPTION_TRAILING_NEWLINE = 'trailingNewline';
    public const OPTION_ESCAPE_SLASHES = 'escapeSlashes';

    private static array $globalDefaults = array(
        self::OPTION_TRAILING_NEWLINE => self::DEFAULT_TRAILING_NEWLINE,
        self::OPTION_ESCAPE_SLASHES => self::DEFAULT_ESCAPE_SLASHES,
        self::OPTION_PRETTY_PRINT => self::DEFAULT_PRETTY_PRINT
    );

    public function getDefaultOptions(): array
    {
        return array(
            self::OPTION_TRAILING_NEWLINE => self::$globalDefaults[self::OPTION_TRAILING_NEWLINE],
            self::OPTION_ESCAPE_SLASHES => self::$globalDefaults[self::OPTION_ESCAPE_SLASHES],
            self::OPTION_PRETTY_PRINT => self::$globalDefaults[self::OPTION_PRETTY_PRINT]
        );
    }

    /**
     * Whether to add a trailing newline at the end of the JSON file.
     *
     * @param bool $enabled Default: {@see self::DEFAULT_TRAILING_NEWLINE}
     * @return $this
     */
    public function setTrailingNewline(bool $enabled) : self
    {
        return $this->setOption(self::OPTION_TRAILING_NEWLINE, $enabled);
    }

    /**
     * Whether to escape slashes in the JSON data values.
     * @param bool $enabled Default: {@see self::DEFAULT_ESCAPE_SLASHES}
     * @return $this
     */
    public function setEscapeSlashes(bool $enabled) : self
    {
        return $this->setOption(self::OPTION_ESCAPE_SLASHES, $enabled);
    }

    /**
     * Whether to indent and prettify the JSON output.
     * @param bool $enabled Default: {@see self::DEFAULT_PRETTY_PRINT}
     * @return $this
     */
    public function setPrettyPrint(bool $enabled) : self
    {
        return $this->setOption(self::OPTION_PRETTY_PRINT, $enabled);
    }

    public function isPrettyPrintEnabled() : bool
    {
        return $this->getOption(self::OPTION_PRETTY_PRINT);
    }

    public function isTrailingNewlineEnabled() : bool
    {
        return $this->getOption(self::OPTION_TRAILING_NEWLINE);
    }

    public function isEscapeSlashesEnabled() : bool
    {
        return $this->getOption(self::OPTION_ESCAPE_SLASHES);
    }

    /**
     * Sets the value of a JSONFile option globally.
     *
     * NOTE: This will only affect new instances of JSONFile.
     * Existing instances will not be affected.
     *
     * @param string $name
     * @param $value
     * @return void
     */
    public static function setGlobalOption(string $name, $value) : void
    {
        self::$globalDefaults[$name] = $value;
    }

    /**
     * Resets all global options to their default values.
     *
     * NOTE: This will only affect new instances of JSONFile.
     * Existing instances will not be affected.
     *
     * @return void
     */
    public static function resetGlobalOptions() : void
    {
        self::$globalDefaults = array(
            self::OPTION_TRAILING_NEWLINE => self::DEFAULT_TRAILING_NEWLINE,
            self::OPTION_ESCAPE_SLASHES => self::DEFAULT_ESCAPE_SLASHES,
            self::OPTION_PRETTY_PRINT => self::DEFAULT_PRETTY_PRINT
        );
    }
}
