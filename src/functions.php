<?php

namespace AppUtils;

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
 * @param string|int|float|Interface_Stringable ...$placeholderValues
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
 * Creates an interval wrapper, that makes it a lot easier
 * to work with date intervals. It also solves
 *
 * @param DateInterval $interval
 * @return ConvertHelper_DateInterval
 */
function parseInterval(DateInterval $interval) : ConvertHelper_DateInterval
{
    return ConvertHelper_DateInterval::fromInterval($interval);
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
 * Initializes the utilities: this is called automatically
 * because this file is included in the files list in the
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
