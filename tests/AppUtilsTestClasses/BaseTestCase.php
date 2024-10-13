<?php

declare(strict_types=1);

namespace AppUtilsTestClasses;

use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    protected string $assetsRootFolder;
    private static int $counter = 0;

    protected function setUp() : void
    {
        parent::setUp();

        $this->assetsRootFolder = __DIR__.'/../assets';

        date_default_timezone_set('Europe/Paris');
    }

    protected function skipWebserverURL() : void
    {
        if(!defined('TESTS_WEBSERVER_URL'))
        {
            $this->markTestSkipped('Webserver URL has not been set.');
        }
    }

    protected function getTestCounter() : int
    {
        return self::$counter++;
    }
}
