<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtils\RequestHelper;
use AppUtilsTestClasses\BaseTestCase;

final class RequestHelperTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        RequestHelper::clearCache();

        unset(
            $_SERVER['HTTP_AUTHORIZATION'],
            $_SERVER['REDIRECT_HTTP_AUTHORIZATION'],
            $_SERVER['Authorization']
        );
    }

    public function testGetBearerTokenReturnsNullWhenNoHeaders(): void
    {
        RequestHelper::clearCache();

        self::assertNull(RequestHelper::getBearerToken());
        self::assertNull(RequestHelper::getBearerToken(), 'Second call should still return null due to cache.');
    }

    public function testGetBearerTokenFromHttpAuthorization(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer http-token';

        self::assertSame('http-token', RequestHelper::getBearerToken());
    }

    public function testGetBearerTokenFromRedirectHttpAuthorization(): void
    {
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = 'Bearer redirect-token';

        self::assertSame('redirect-token', RequestHelper::getBearerToken());
    }

    public function testGetBearerTokenFromAuthorization(): void
    {
        $_SERVER['Authorization'] = 'Bearer env-token';

        self::assertSame('env-token', RequestHelper::getBearerToken());
    }

    public function testGetBearerTokenServerKeyPrecedence(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer http-token';
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = 'Bearer redirect-token';
        $_SERVER['Authorization'] = 'Bearer env-token';

        self::assertSame('http-token', RequestHelper::getBearerToken());
    }

    public function testGetBearerTokenSkipsEmptyServerEntries(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = '';
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = 'Bearer redirect-token';
        $_SERVER['Authorization'] = '';

        self::assertSame('redirect-token', RequestHelper::getBearerToken());
    }

    public function testGetBearerTokenTrimsAndParsesBearerToken(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = '   Bearer   spaced-token   ';

        self::assertSame('spaced-token', RequestHelper::getBearerToken());
    }

    public function testGetBearerTokenIsCaseInsensitive(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'bearer lower-keyword';

        self::assertSame('lower-keyword', RequestHelper::getBearerToken());
    }

    public function testGetBearerTokenWithInvalidAuthorizationReturnsNull(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic xyz';
        $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] = 'Token abc';
        $_SERVER['Authorization'] = 'Something else';

        self::assertNull(RequestHelper::getBearerToken());
    }

    public function testGetBearerTokenWithOnlyBearerKeywordReturnsNull(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer';

        self::assertNull(RequestHelper::getBearerToken());
    }

    public function testGetBearerTokenCachesResultUntilCleared(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer first';

        $first = RequestHelper::getBearerToken();
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer second';

        $second = RequestHelper::getBearerToken();

        self::assertSame('first', $first);
        self::assertSame('first', $second, 'Cached token should be returned even after header changes.');
    }

    public function testGetBearerTokenReDetectsAfterClearCache(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer first';

        self::assertSame('first', RequestHelper::getBearerToken());

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer second';

        RequestHelper::clearCache();

        self::assertSame('second', RequestHelper::getBearerToken());
    }

    public function testGetBearerTokenCachedEmptyResultRequiresClearCache(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic xyz';
        RequestHelper::clearCache();

        $first = RequestHelper::getBearerToken();
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer now-valid';

        $second = RequestHelper::getBearerToken();

        self::assertNull($first);
        self::assertNull($second, 'Cached empty result should keep returning null until cache is cleared.');
    }

    public function testGetBearerTokenAfterClearCacheUsesNewValidToken(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Basic xyz';
        RequestHelper::clearCache();

        self::assertNull(RequestHelper::getBearerToken());

        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer now-valid';
        RequestHelper::clearCache();

        self::assertSame('now-valid', RequestHelper::getBearerToken());
    }
}
