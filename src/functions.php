<?php

namespace AppUtils;

use AppUtils\DateTimeHelper\DateIntervalExtended;
use AppUtils\DateTimeHelper\DurationStringInfo;
use AppUtils\Interfaces\StringableInterface;
use DateInterval;
use Throwable;

/**
 * Parses the specified variable, and allows accessing
 * information on it.
 *
 * @param mixed $variable
 * @return VariableInfo
 * @throws BaseException
 */
function parseVariable($variable) : VariableInfo
{
    return new VariableInfo($variable);
}

/**
 * Creates a throwable info instance for the specified error,
 * which enables accessing additional information on it,
 * as well as serializing it to be able to persist it in storage.
 *
 * @param Throwable $e
 * @return ThrowableInfo
 */
function parseThrowable(Throwable $e) : ThrowableInfo
{
    return ThrowableInfo::fromThrowable($e);
}

/**
 * Restores a throwable info instance from a previously
 * serialized array.
 *
 * @param array<string,mixed> $serialized
 * @return ThrowableInfo
 * @throws BaseException
 */
function restoreThrowable(array $serialized) : ThrowableInfo
{
    return ThrowableInfo::fromSerialized($serialized);
}

/**
 * Translation function used to translate internal strings:
 * if the localization is installed, it will use this to
 * do the translation.
 *
 * @param string $text
 * @param string|int|float|StringableInterface ...$placeholderValues
 * @return string
 */
function t(string $text, ...$placeholderValues) : string
{
    $args = func_get_args();

    // is the localization package installed?
    if(function_exists('\AppLocalize\t'))
    {
        return call_user_func_array('\AppLocalize\t', $args);
    }

    // simulate the translation function
    return (string)call_user_func_array('sprintf', $args);
}

/**
 * Creates a new StringBuilder instance.
 *
 * @return StringBuilder
 */
function sb() : StringBuilder
{
    return new StringBuilder();
}

/**
 * Creates a new attribute collection, optionally with initial attributes.
 * @param array<string,string|number|bool|NULL|StringableInterface>|string|NULL $attributes Associative array, or a query string that will be parsed.
 * @return AttributeCollection
 */
function attr($attributes=null) : AttributeCollection
{
    return AttributeCollection::createAuto($attributes);
}

/**
 * Creates an interval wrapper, that makes it a lot easier
 * to work with date intervals. It also solves
 *
 * @param DateInterval $interval
 * @return DateIntervalExtended
 */
function parseInterval(DateInterval $interval) : DateIntervalExtended
{
    return DateIntervalExtended::fromInterval($interval);
}

/**
 * Like the native PHP function <code>parse_url</code>,
 * but with a friendly API and some enhancements and fixes
 * for a few things that the native function handles poorly.
 *
 * @param string $url The URL to parse.
 * @return URLInfo
 */
function parseURL(string $url) : URLInfo
{
    return new URLInfo($url);
}

/**
 * Removes the specified values from the target array.
 *
 * @param array<mixed> $haystack
 * @param array<mixed> $values
 * @param bool $strict
 * @return array<mixed>
 */
function array_remove_values(array $haystack, array $values, bool $strict=true) : array
{
    return array_filter(
        $haystack,
        static fn($entry) => !in_array($entry, $values, $strict)
    );
}

/**
 * Parses the specified number, and returns a NumberInfo instance.
 *
 * @param NumberInfo|string|int|float|NULL $value
 * @param bool $forceNew
 * @return NumberInfo
 */
function parseNumber($value, bool $forceNew=false) : NumberInfo
{
    if($value instanceof NumberInfo && $forceNew !== true) {
        return $value;
    }

    return new NumberInfo($value);
}

/**
 * Like {@see parseNumber()}, but returns an immutable
 * instance where any operations that modify the value
 * return a new instance, leaving the original instance
 * intact.
 *
 * @param NumberInfo|string|int|float|NULL $value
 * @return NumberInfo_Immutable
 */
function parseNumberImmutable($value) : NumberInfo_Immutable
{
    return new NumberInfo_Immutable($value);
}

/**
 * Parses a standardized duration string in the format
 * `1h 30m 15s` and returns a duration info object that can
 * be converted to a date interval and more.
 *
 * @param string|NULL $duration Allowing `NULL` values for convenience.
 * @return DurationStringInfo
 */
function parseDurationString(?string $duration) : DurationStringInfo
{
    return DurationStringInfo::create($duration);
}

/**
 * Initializes the utilities: this is called automatically
 * because this file is included in the file list in the
 * composer.json, guaranteeing it is always loaded.
 */
function init() : void
{
    if(!class_exists('\AppLocalize\Localization')) {
        return;
    }

    $installFolder = __DIR__.'/../';

    // Register the classes as a localization source,
    // so they can be found, and use the bundled localization
    // files.
    \AppLocalize\Localization::addSourceFolder(
        'application-utils',
        'Application Utils Package',
        'Composer Packages',
        $installFolder.'/localization',
        $installFolder.'/src'
    );
}

init();
