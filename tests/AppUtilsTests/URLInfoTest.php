<?php
/**
 * @package AppUtilsTests
 */

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtils\URLInfo\URIFilter;
use AppUtils\URLInfo\URISchemes;
use AppUtils\URLInfo\URLHosts;
use AppUtilsTestClasses\BaseTestCase;
use AppUtils\URLInfo;
use function AppUtils\parseURL;

/**
 * @package AppUtilsTests
 */
final class URLInfoTest extends BaseTestCase
{
    public function test_parsing() : void
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'url' => '',
                'valid' => false,
                'normalized' => '',
            ),
            array(
                'label' => 'Whitespace string',
                'url' => '       ',
                'valid' => false,
                'normalized' => ''
            ),
            array(
                'label' => 'Whitespace string with newlines',
                'url' => "    \n    \r   \t    ",
                'valid' => false,
                'normalized' => ''
            ),
            array(
                'label' => 'Whitespace string with newlines as encoded url',
                'url' => "    %0D    %0A   %09    ",
                'valid' => false,
                'normalized' => ''
            ),
            array(
                'label' => 'Whitespace string with newlines as encoded url and normal characters',
                'url' => "    \n    \r   \t    %0D    %0A   %09    ",
                'valid' => false,
                'normalized' => ''
            ),
            array(
                'label' => 'Random non-URL string',
                'url' => 'Foo and bar jump over the fox',
                'valid' => false,
                'normalized' => ''
            ),
            array(
                'label' => 'HTML tag',
                'url' => '<foo>bar</foo>',
                'valid' => false,
                'normalized' => ''
            ),
            array(
                'label' => 'Regular URL',
                'url' => 'http://www.foo.com',
                'valid' => true,
                'normalized' => 'http://www.foo.com'
            ),
            array(
                'label' => 'URL with too many scheme slashes',
                'url' => 'http:///www.foo.com',
                'valid' => true,
                'normalized' => 'http://www.foo.com'
            ),
            array(
                'label' => 'URL with whitespace',
                'url' => '   http://www.foo.com    ',
                'valid' => true,
                'normalized' => 'http://www.foo.com'
            ),
            array(
                'label' => 'URL with newlines',
                'url' => "  \n http://www.\rfoo.com  \r  ",
                'valid' => true,
                'normalized' => 'http://www.foo.com'
            ),
            array(
                'label' => 'URL with the weird hyphen',
                'url' => "http://www.foo-bar.com",
                'valid' => true,
                'normalized' => 'http://www.foo-bar.com'
            ),
            array(
                'label' => 'With whitespaces within the URL',
                'url' => "http://www.   foo-bar.   com /  some/ folder /",
                'valid' => true,
                'normalized' => 'http://www.foo-bar.com/  some/ folder /'
            ),
            array(
                'label' => 'With HTML encoded ampersands',
                'url' => "http://www.foo-bar.com?foo=bar&amp;bar=foo&amp;lopos=yes",
                'valid' => true,
                'normalized' => 'http://www.foo-bar.com?bar=foo&foo=bar&lopos=yes'
            ),
            array(
                'label' => 'With spaces in param names',
                'url' => "http://www.spaceparams.com?  foo  =bar",
                'valid' => true,
                'normalized' => 'http://www.spaceparams.com?%20%20foo%20%20=bar'
            ),
            array(
                'label' => 'With double question marks',
                'url' => "https://params.com/special-offers?campaign=spring?utm_campaign=newsletter_march",
                'valid' => true,
                'normalized' => 'https://params.com/special-offers?campaign=spring%3Futm_campaign%3Dnewsletter_march'
            ),
            array(
                'label' => 'With previously encoded spaces in param names',
                'url' => "http://www.spaceparams.com?%20%20foo%20%20=bar",
                'valid' => true,
                'normalized' => 'http://www.spaceparams.com?%20%20foo%20%20=bar'
            ),
            array(
                'label' => 'Uppercase local parts',
                'url' => 'HTTPS://DOMAIN.COM',
                'valid' => true,
                'normalized' => 'https://domain.com'
            ),
            array(
                'label' => 'Phone URL',
                'url' => 'tel:0033458874545',
                'valid' => true,
                'normalized' => 'tel:0033458874545'
            ),
            array(
                'label' => 'GIT URL',
                'url' => 'git://github.com/user/project-name.git',
                'valid' => true,
                'normalized' => 'git://github.com/user/project-name.git'
            ),
            array(
                'label' => 'IP Address',
                'url' => 'https://192.168.0.1',
                'valid' => true,
                'normalized' => 'https://192.168.0.1'
            ),
            array(
                'label' => 'IP Address without scheme',
                'url' => '192.168.0.1',
                'valid' => true,
                'normalized' => 'https://192.168.0.1'
            ),
            array(
                'label' => 'Localhost with scheme',
                'url' => 'http://localhost',
                'valid' => true,
                'normalized' => 'http://localhost'
            ),
            array(
                'label' => 'Localhost without scheme',
                'url' => 'localhost',
                'valid' => true,
                'normalized' => 'https://localhost'
            ),
            array(
                'label' => 'Localhost without scheme, with fragment',
                'url' => 'localhost#foo',
                'valid' => true,
                'normalized' => 'https://localhost#foo'
            ),
            array(
                'label' => 'Database DSN',
                'url' => 'mariadb://user:pass@localhost/dbname',
                'valid' => true,
                'normalized' => 'mariadb://user:pass@localhost/dbname'
            )
        );

        foreach ($tests as $test)
        {
            $info = new URLInfo($test['url']);

            $this->assertEquals($test['valid'], $info->isValid(), $test['label'] . '.' . PHP_EOL . 'URL: ' . $test['url'] . PHP_EOL . 'Reason: ' . $info->getErrorMessage());
            $this->assertEquals($test['normalized'], $info->getNormalized(), $test['label']);
        }
    }

    public function test_foldersWithSpaces() : void
    {
        $url = 'https://bar.com/folder with spaces';

        $control = parse_url($url);
        $this->assertSame('/folder with spaces', $control['path']);

        $info = parseURL($url);
        $parser = $info->getParser();

        $this->assertSame($url, URIFilter::filter($url));
        $this->assertSame('/folder with spaces', $parser->getPath());
        $this->assertSame('/folder with spaces', $info->getPath());
        $this->assertStringContainsString('/folder with spaces', $info->getNormalized());
    }

    public function test_detectEmail() : void
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'url' => '',
                'isEmail' => false,
                'normalized' => '',
            ),
            array(
                'label' => 'Simple email address, without mailto',
                'url' => 'foo@bar.com',
                'isEmail' => true,
                'normalized' => 'mailto:foo@bar.com',
            ),
            array(
                'label' => 'Simple email address, with mailto',
                'url' => 'mailto:foo@bar.com',
                'isEmail' => true,
                'normalized' => 'mailto:foo@bar.com',
            ),
            array(
                'label' => 'With whitespace',
                'url' => '    mailto:      foo@  bar.com   ',
                'isEmail' => true,
                'normalized' => 'mailto:foo@bar.com',
            ),
            array(
                'label' => 'Without mailto, and with whitespace',
                'url' => '      foo@  bar.com   ',
                'isEmail' => true,
                'normalized' => 'mailto:foo@bar.com',
            ),
            array(
                'label' => 'With different characters',
                'url' => 'foo_bar-test/hey+crazy!@some-bar.co.uk',
                'isEmail' => true,
                'normalized' => 'mailto:foo_bar-test/hey+crazy!@some-bar.co.uk',
            )
        );

        foreach ($tests as $test)
        {
            $info = new URLInfo($test['url']);

            $this->assertEquals($test['isEmail'], $info->isEmail(), $test['label'] . ' Error: ' . $info->getErrorMessage());
            $this->assertEquals($test['normalized'], $info->getNormalized(), $test['label'] . ' Error: ' . $info->getErrorMessage());
        }
    }

    public function test_detectFragment() : void
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'url' => '',
                'isFragment' => false,
                'normalized' => '',
            ),
            array(
                'label' => 'Regular fragment',
                'url' => '#foo',
                'isFragment' => true,
                'normalized' => '#foo',
            ),
            array(
                'label' => 'With whitespace',
                'url' => '    #foo    ',
                'isFragment' => true,
                'normalized' => '#foo',
            ),
            array(
                'label' => 'With newlines and tabs',
                'url' => "  \n  #foo  \r    \t ",
                'isFragment' => true,
                'normalized' => '#foo',
            ),
            array(
                'label' => 'Not a fragment',
                'url' => 'http://www.foo.com#foo',
                'isFragment' => false,
                'normalized' => 'http://www.foo.com#foo',
            ),
            array(
                'label' => 'With just some letters before it',
                'url' => "some text bar#foo",
                'isFragment' => false,
                'normalized' => '',
            ),
            array(
                'label' => 'Localhost fragment',
                'url' => "localhost#foo",
                'isFragment' => false,
                'normalized' => 'https://localhost#foo',
            )
        );

        foreach ($tests as $test)
        {
            $info = new URLInfo($test['url']);

            $this->assertEquals($test['isFragment'], $info->isAnchor(), $test['label'] . ' Error: ' . $info->getErrorMessage());
            $this->assertEquals($test['normalized'], $info->getNormalized(), $test['label'] . ' Error: ' . $info->getErrorMessage());
        }
    }

    public function test_detectPhone() : void
    {
        $tests = array(
            array(
                'label' => 'Empty string',
                'url' => '',
                'isPhone' => false,
                'normalized' => '',
            ),
            array(
                'label' => 'Phone with +',
                'url' => 'tel://+33 123456789',
                'isPhone' => true,
                'normalized' => 'tel:+33123456789',
            ),
            array(
                'label' => 'Phone with 00',
                'url' => 'tel://0033 12 34 56 78',
                'isPhone' => true,
                'normalized' => 'tel:003312345678',
            ),
            array(
                'label' => 'Free spacing',
                'url' => 'tel://    +  33 12 34 56 78',
                'isPhone' => true,
                'normalized' => 'tel:+3312345678',
            ),
            array(
                'label' => 'With newlines and tabs',
                'url' => "tel://  \n  +  \r 33 12 34 \t 56 78",
                'isPhone' => true,
                'normalized' => 'tel:+3312345678',
            ),
            array(
                'label' => 'Without slashes',
                'url' => "tel:+33 12 34 56 78",
                'isPhone' => true,
                'normalized' => 'tel:+3312345678',
            )
        );

        foreach ($tests as $test)
        {
            $info = new URLInfo($test['url']);

            $this->assertEquals($test['isPhone'], $info->isPhoneNumber(), $test['label'] . ' Error: ' . $info->getErrorMessage());
            $this->assertEquals($test['normalized'], $info->getNormalized(), $test['label'] . ' Error: ' . $info->getErrorMessage());
        }
    }

    public function test_phoneScheme() : void
    {
        $info = parseURL('tel:+1111111111');
        $this->assertEquals('tel', $info['scheme']);

        $info = parseURL('tel://+1111111111');
        $this->assertEquals('tel', $info['scheme']);
    }

    public function test_globalFunction() : void
    {
        $info = parseURL('https://foo.com');

        $this->assertSame('foo.com', $info->getHost());
    }

    public function test_arrayAccess() : void
    {
        $info = parseURL('https://user:pass@foo.com:1234/path/to/page/index.html#fragment');

        $this->assertEquals('https', $info['scheme']);
        $this->assertEquals('user', $info['user']);
        $this->assertEquals('pass', $info['pass']);
        $this->assertEquals('foo.com', $info['host']);
        $this->assertSame(1234, $info['port']);
        $this->assertEquals('/path/to/page/index.html', $info['path']);
        $this->assertEquals('fragment', $info['fragment']);
    }

    public function test_arrayAccess_empty() : void
    {
        $info = parseURL('//foo.com');

        $this->assertSame('', $info['scheme']);
        $this->assertSame('', $info['user']);
        $this->assertSame('', $info['pass']);
        $this->assertSame(-1, $info['port']);
        $this->assertSame('', $info['path']);
        $this->assertSame('', $info['fragment']);
    }

    public function test_scheme() : void
    {
        $tests = array(
            array(
                'label' => 'Regular HTTP url',
                'url' => 'http://foo.com',
                'expected' => 'http',
                'hasScheme' => true,
                'isSecure' => false,
                'isURL' => true
            ),
            array(
                'label' => 'Regular HTTPS url',
                'url' => 'https://foo.com',
                'expected' => 'https',
                'hasScheme' => true,
                'isSecure' => true,
                'isURL' => true
            ),
            array(
                'label' => 'Regular FTP url',
                'url' => 'ftp://foo.com',
                'expected' => 'ftp',
                'hasScheme' => true,
                'isSecure' => false,
                'isURL' => true
            ),
            array(
                'label' => 'Schemeless but valid URL',
                'url' => '//foo.com',
                'expected' => '',
                'hasScheme' => false,
                'isSecure' => false,
                'isURL' => true
            ),
            array(
                'label' => 'Invalid URL',
                'url' => 'foo.com',
                'expected' => '',
                'hasScheme' => false,
                'isSecure' => false,
                'isURL' => false
            ),
            array(
                'label' => 'Unknown scheme',
                'url' => 'fooscheme://bar.com',
                'expected' => '',
                'hasScheme' => false,
                'isSecure' => false,
                'isURL' => true
            )
        );

        foreach ($tests as $test)
        {
            $info = parseURL($test['url']);

            $this->assertEquals($test['expected'], $info->getScheme(), $test['label']);
            $this->assertEquals($test['hasScheme'], $info->hasScheme(), $test['label']);
            $this->assertEquals($test['isSecure'], $info->isSecure(), $test['label']);
            $this->assertEquals($test['isURL'], $info->isURL(), $test['label']);
        }
    }

    public function test_port() : void
    {
        $tests = array(
            array(
                'label' => 'No port specified',
                'url' => 'https://foo.com',
                'expected' => -1,
                'hasPort' => false,
            ),
            array(
                'label' => 'Port specified',
                'url' => 'https://foo.com:3120',
                'expected' => 3120,
                'hasPort' => true,
            )
        );

        foreach ($tests as $test)
        {
            $info = parseURL($test['url']);

            $this->assertSame($test['expected'], $info->getPort(), $test['label']);
            $this->assertEquals($test['hasPort'], $info->hasPort(), $test['label']);
        }
    }

    /**
     * Ensure that the same URLs, but with a different order of parameters
     * have the same hash (which is generated from the normalized URL).
     */
    public function test_getHash() : void
    {
        $url1 = 'https://foo.com?param1=foo&param2=bar&param3=dog';
        $url2 = 'https://foo.com?param3=dog&param1=foo&param2=bar';

        $info1 = parseURL($url1);
        $info2 = parseURL($url2);

        $this->assertEquals($info1->getNormalized(), $info2->getNormalized(), 'The normalized URLs should match.');
        $this->assertEquals($info1->getHash(), $info2->getHash(), 'The hashes should match.');
    }

    public function test_tryConnect() : void
    {
        $this->assertTrue(parseURL('https://google.com')->tryConnect(false), 'Could not connect to google.com without SSL checks enabled.');
        $this->assertTrue(parseURL('https://google.com')->tryConnect(), 'Could not connect to google.com with SSL checks enabled.');

        $this->assertFalse(parseURL('https://' . md5((string)microtime(true)) . '.org')->tryConnect(), 'Could connect to an unknown website.');
    }

    public function test_normalize() : void
    {
        $tests = array(
            array(
                'label' => 'Regular URL',
                'value' => 'https://www.foo.com',
                'expected' => 'https://www.foo.com'
            ),
            array(
                'label' => 'With parameter',
                'value' => 'https://www.foo.com?bar=foo',
                'expected' => 'https://www.foo.com?bar=foo'
            ),
            array(
                'label' => 'With port number',
                'value' => 'https://www.foo.com:5511/path/to/page',
                'expected' => 'https://www.foo.com:5511/path/to/page'
            ),
            array(
                'label' => 'With parameter and fragment',
                'value' => 'https://www.foo.com?bar=foo#somewhere',
                'expected' => 'https://www.foo.com?bar=foo#somewhere'
            ),
            array(
                'label' => 'With path, parameter and fragment',
                'value' => 'https://www.foo.com/some/path/?bar=foo#somewhere',
                'expected' => 'https://www.foo.com/some/path/?bar=foo#somewhere'
            ),
            array(
                'label' => 'With username and password',
                'value' => 'https://username:password@www.foo.com',
                'expected' => 'https://username:password@www.foo.com'
            ),
            array(
                'label' => 'Parameter reordering',
                'value' => 'https://www.foo.com?foo=bar&bar=foo',
                'expected' => 'https://www.foo.com?bar=foo&foo=bar'
            )
        );

        foreach ($tests as $test)
        {
            $info = parseURL($test['value']);

            $this->assertEquals($test['expected'], $info->getNormalized(), $test['label']);
        }
    }

    /**
     * The Username and password have to be URL encoded, since they
     * can contain URL-specific syntax characters. This has to be
     * handled correctly, so they are URL decoded when accessing them,
     * and URL encoded when normalizing.
     *
     * @see \AppUtils\URLInfo\URIParser::filterParsed()
     */
    public function test_credentialsSpecialCharacters() : void
    {
        $specialchars = 'öä§#()!?/{}';
        $encoded = urlencode($specialchars);

        $url = 'https://' . $encoded . ':' . $encoded . '@www.foo.com';

        $info = parseURL($url);

        $this->assertEquals($specialchars, $info->getUsername(), 'Username should be URL decoded.');
        $this->assertEquals($specialchars, $info->getPassword(), 'Password should be URL decoded.');
        $this->assertEquals('https://' . $encoded . ':' . $encoded . '@www.foo.com', $info->getNormalized(), 'Password and Username should be URL encoded.');
    }

    public function test_normalizedWithoutAuth() : void
    {
        $info = parseURL('http://username:password@test.com');

        $this->assertEquals('http://username:password@test.com', $info->getNormalized());
        $this->assertEquals('http://test.com', $info->getNormalizedWithoutAuth());
    }

    /**
     * Excluding parameters in URLs.
     */
    public function test_excludeParam() : void
    {
        $tests = array(
            array(
                'label' => 'The URL should stay unchanged.',
                'url' => 'http://test.com/feedback?medium=somevalue',
                'expected' => 'http://test.com/feedback?medium=somevalue'
            ),
            array(
                'label' => ' The ac parameter should be stripped.',
                'url' => 'http://test.com/feedback?medium=somevalue&ac=stripme',
                'expected' => 'http://test.com/feedback?medium=somevalue'
            ),
            array(
                'label' => ' The ac parameter should be stripped, other parameters left alone.',
                'url' => 'http://test.com/feedback?medium=somevalue&ac=stripme&medium2=othervalue',
                'expected' => 'http://test.com/feedback?medium=somevalue&medium2=othervalue'
            )
        );

        foreach ($tests as $entry)
        {
            $info = parseURL($entry['url']);

            $info->excludeParam('ac', 'Reason');

            $this->assertEquals($entry['expected'], $info->getNormalized(), $entry['label']);
        }
    }

    /**
     * Check the switching between parameter exclusion modes.
     */
    public function test_disableParamExclusion() : void
    {
        $tests = array(
            array(
                'label' => ' The ac parameter should be stripped, other parameters left alone.',
                'url' => 'http://test.com/feedback?ac=stripme&medium1=somevalue&medium2=othervalue',
                'excluded' => 'http://test.com/feedback?medium1=somevalue&medium2=othervalue',
                'not-excluded' => 'http://test.com/feedback?ac=stripme&medium1=somevalue&medium2=othervalue'
            )
        );

        foreach ($tests as $entry)
        {
            $info = parseURL($entry['url']);

            // the default state: no parameters excluded.
            $this->assertFalse($info->isParamExclusionEnabled(), 'By default, parameter exclusion should be turned off.');
            $this->assertEquals($entry['not-excluded'], $info->getNormalized(), 'By default, URL should still contain excluded params.');

            // exluding a parameter should auto-enable the exclusion mode.
            $info->excludeParam('ac', 'Reason');
            $this->assertTrue($info->isParamExclusionEnabled(), 'Parameter exclusion should be auto-enabled when adding exclude params.');
            $this->assertEquals($entry['excluded'], $info->getNormalized(), 'URL should not contain any of the excluded params.');

            // turning it off should return the original URL with all excluded params
            $info->setParamExclusion(false);
            $this->assertFalse($info->isParamExclusionEnabled(), 'Parameter exclusion should be disabled.');
            $this->assertEquals($entry['not-excluded'], $info->getNormalized(), 'URL should contain all of the excluded params.');

            // turning it on again without adding new excluded parameters
            $info->setParamExclusion();
            $this->assertTrue($info->isParamExclusionEnabled(), 'Parameter exclusion should be enabled.');
            $this->assertEquals($entry['excluded'], $info->getNormalized(), 'URL should not contain any of the excluded params.');
        }
    }

    /**
     * Ensure that highlighting excluded parameters works.
     */
    public function test_highlightExcluded() : void
    {
        $info = parseURL('http://test.com/feedback?ac=stripme&medium1=somevalue&medium2=othervalue');
        $info->excludeParam('ac', 'Reason');
        $info->setHighlightExcluded();

        $highlighted = $info->getHighlighted();

        $this->assertStringContainsString('stripme', $highlighted, 'Should contain the excluded parameter.');
        $this->assertStringContainsString('excluded-param', $highlighted, 'Should contain the class for excluded parameters.');
    }

    public function test_highlightGetStyl4es() : void
    {
        $css = URLInfo::getHighlightCSS();

        $this->assertStringContainsString('.link-scheme.scheme-https', $css);
    }

    /**
     * Ensure that checking whether a URL contains excluded parameters works as intended.
     */
    public function test_containsExcludedParams() : void
    {
        $tests = array(
            array(
                'label' => 'Should contain no excluded params.',
                'url' => 'https://test.com/feedback?medium=somevalue',
                'expected' => false
            ),
            array(
                'label' => 'Should contain excluded params.',
                'url' => 'https://test.com/feedback?medium=somevalue&ac=stripme',
                'expected' => true
            )
        );

        foreach ($tests as $entry)
        {
            $info = parseURL($entry['url']);

            $info->excludeParam('ac', 'Reason');

            $this->assertEquals($entry['expected'], $info->containsExcludedParams(), $entry['label']);
        }
    }

    public function test_unicodeChars() : void
    {
        $tests = array(
            array(
                'label' => 'German characters in the path.',
                'url' => 'http://test.com/mögenß/',
                'expected' => 'http://test.com/mögenß/'
            ),
            array(
                'label' => 'Spanish character',
                'url' => 'https://www.lideresenservicio.com/metodología/',
                'expected' => 'https://www.lideresenservicio.com/metodología/',
            )
        );

        foreach ($tests as $entry)
        {
            $info = parseURL($entry['url']);

            $result = $info->getNormalized();

            $this->assertEquals($entry['expected'], $result, $entry['label']);
        }
    }

    public function test_unicodeChars_withUrlencode() : void
    {
        $tests = array(
            array(
                'label' => 'German characters in the path.',
                'url' => 'http://test.com/mögenß/',
                'expected' => 'http://test.com/m%C3%B6gen%C3%9F/'
            ),
            array(
                'label' => 'Spanish character',
                'url' => 'https://www.lideresenservicio.com/metodología/',
                'expected' => 'https://www.lideresenservicio.com/metodolog%C3%ADa/',
            )
        );

        foreach ($tests as $entry)
        {
            $info = parseURL($entry['url']);
            $info->setUTFEncoding();

            $result = $info->getNormalized();

            $this->assertEquals($entry['expected'], $result, $entry['label']);
        }
    }

    public function test_setParam() : void
    {
        $tests = array(
            array(
                'label' => 'No existing parameters',
                'url' => 'http://test.com',
                'expected' => 'http://test.com?foo=bar',
                'params' => array(
                    'foo' => 'bar'
                )
            ),
            array(
                'label' => 'With existing parameter',
                'url' => 'https://www.test.com?bar=foo',
                'expected' => 'https://www.test.com?bar=foo&foo=bar',
                'params' => array(
                    'foo' => 'bar'
                )
            )
        );

        foreach ($tests as $entry)
        {
            $info = parseURL($entry['url']);

            foreach ($entry['params'] as $name => $val)
            {
                $info->setParam($name, $val);
            }

            $result = $info->getNormalized();

            $this->assertEquals($entry['expected'], $result, $entry['label']);
        }
    }

    public function test_removeParam() : void
    {
        $tests = array(
            array(
                'label' => 'Single existing param',
                'url' => 'http://test.com?foo=bar',
                'expected' => 'http://test.com',
                'param' => 'foo'
            ),
            array(
                'label' => 'Several existing parameters',
                'url' => 'https://www.test.com?bar=foo&foo=bar',
                'expected' => 'https://www.test.com?bar=foo',
                'param' => 'foo'
            ),
            array(
                'label' => 'Param not present',
                'url' => 'https://www.test.com?bar=foo',
                'expected' => 'https://www.test.com?bar=foo',
                'param' => 'foo'
            )
        );

        foreach ($tests as $entry)
        {
            $info = parseURL($entry['url']);

            $info->removeParam($entry['param']);

            $result = $info->getNormalized();

            $this->assertEquals($entry['expected'], $result, $entry['label']);
        }
    }

    public function test_detectIPAddress() : void
    {
        $tests = array(
            array(
                'url' => '192.168.0.1',
                'hasIP' => true
            ),
            array(
                'url' => 'https://192.168.0.1',
                'hasIP' => true
            ),
            array(
                'url' => '192.168.0.1?param=yes',
                'hasIP' => true
            ),
            array(
                'url' => '192.168.0.1/path/to/page',
                'hasIP' => true
            ),
            array(
                'url' => '192.168.0.1#fragment',
                'hasIP' => true
            ),
            array(
                'url' => 'https://192.domain.com',
                'hasIP' => false
            )
        );

        foreach($tests as $test)
        {
            $info = parseURL($test['url']);
            $this->assertSame(
                $test['hasIP'],
                $info->hasIPAddress(),
                'URL: '.$test['url'].PHP_EOL.
                'Reason: '.$info->getErrorMessage()
            );
        }
    }

    public function test_addScheme() : void
    {
        $url = 'myscheme://host/path';

        $this->assertFalse(parseURL($url)->isValid());

        URISchemes::addScheme('myscheme://');

        $this->assertTrue(parseURL($url)->isValid());

        URISchemes::removeScheme('myscheme://');

        $this->assertFalse(parseURL($url)->isValid());
    }

    public function test_addHosts() : void
    {
        $url = 'foohost';

        $this->assertFalse(parseURL($url)->isValid());

        URLHosts::addHost('foohost');

        $this->assertTrue(parseURL($url)->isValid());
    }

    public function test_databaseDSN() : void
    {
        $info = parseURL('mariadb://user:pass@localhost/dbname');

        $this->assertSame('mariadb', $info->getScheme());
        $this->assertSame('user', $info->getUsername());
        $this->assertSame('pass', $info->getPassword());
        $this->assertSame('localhost', $info->getHost());
        $this->assertSame('/dbname', $info->getPath());
    }

    protected function setUp() : void
    {
        parent::setUp();

        URISchemes::addScheme('mariadb://');
    }
}
