<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtilsTestClasses\BaseTestCase;
use DateTime;
use function AppUtils\sb;

final class StringBuilderTest extends BaseTestCase
{
    public function test_sf(): void
    {
        $result = (string)sb()->sf('%1$s here and %2$s', 'One', 'Two');

        $this->assertEquals('One here and Two', $result);
    }

    public function test_translate(): void
    {
        $result = (string)sb()->t('Hello');

        $this->assertEquals('Hello', $result);
    }

    public function test_translate_params(): void
    {
        $result = (string)sb()->t('%1$s here and %2$s', 'One', 'Two');

        $this->assertEquals('One here and Two', $result);
    }

    public function test_para(): void
    {
        $this->assertEquals('<br><br>', (string)sb()->para());
        $this->assertEquals('<p>Test</p>', (string)sb()->para('Test'));
        $this->assertEquals('', (string)sb()->para(''));
    }

    public function test_nospace(): void
    {
        $this->assertEquals('Test', (string)sb()->nospace('Test'));
        $this->assertEquals('TestFoo', (string)sb()->nospace('Test')->nospace('Foo'));
        $this->assertEquals('Test YoFoo', (string)sb()->nospace('Test')->add('Yo')->nospace('Foo'));
        $this->assertEquals('', (string)sb()->nospace(null));
        $this->assertEquals('', (string)sb()->nospace(''));
    }

    public function test_ifTrue(): void
    {
        $this->assertEquals('Test', (string)sb()->ifTrue(true, 'Test'));
        $this->assertEquals('', (string)sb()->ifTrue(false, 'Test'));
        $this->assertEquals('Yup', (string)sb()->ifTrue(true, static function () {
            return 'Yup';
        }));
        $this->assertEquals('', (string)sb()->ifTrue(false, static function () {
            return 'Yup';
        }));
    }

    public function test_ifTrue_emptyString(): void
    {
        $this->assertEquals('', (string)sb()->ifTrue(true, null));
        $this->assertEquals('', (string)sb()->ifTrue(true, ''));
    }

    public function test_ifFalse(): void
    {
        $this->assertEquals('Test', (string)sb()->ifFalse(false, 'Test'));
        $this->assertEquals('', (string)sb()->ifFalse(true, 'Test'));
    }

    public function test_ifEmpty(): void
    {
        $this->assertEquals('Test', (string)sb()->ifEmpty('', 'Test'));
        $this->assertEquals('', (string)sb()->ifEmpty('Not empty', 'Test'));
    }

    public function test_ifEmpty_objectNullable(): void
    {
        $subject = null;

        $this->assertEquals('', (string)sb()->ifEmpty($subject, $subject));
    }

    public function test_ifNotEmpty(): void
    {
        $this->assertEquals('Test', (string)sb()->ifNotEmpty('Not empty', 'Test'));
        $this->assertEquals('', (string)sb()->ifNotEmpty('', 'Test'));
        $this->assertEquals('Test', (string)sb()->ifNotEmpty('Not empty callback', static function () {
            return 'Test';
        }));
        $this->assertEquals('', (string)sb()->ifNotEmpty('', static function () {
            return 'Test';
        }));
    }

    public function test_italic() : void
    {
        $this->assertEquals('<i>Test</i>', (string)sb()->italic('Test'));
        $this->assertEquals('<i class="classA">Test</i>', (string)sb()->useClass('classA')->italic('Test'));
    }

    public function test_useClass() : void
    {
        $this->assertEquals(
            '<i class="classA">Test</i>',
            (string)sb()
                ->useClass('classA')
                ->italic('Test')
        );
    }

    public function test_useClasses() : void
    {
        $this->assertEquals(
            '<i class="classA classB">Test</i>',
            (string)sb()
                ->useClasses(array('classB', 'classA'))
                ->italic('Test')
        );
    }

    public function test_classesAreResetWithEachAdd() : void
    {
        $string = sb()
            ->useClass('classA')
            ->italic('Test')
            ->bold('Test2');

        $this->assertSame('<i class="classA">Test</i> <b>Test2</b>', (string)$string);
    }

    public function test_useNoSpace() : void
    {
        $this->assertEquals(
            '<i>Test 1</i><b>Test 2</b>',
            (string)sb()
                ->italic('Test 1')
                ->useNoSpace()
                ->bold('Test 2')
        );
    }

    public function test_age() : void
    {
        $start = new DateTime('2020-01-01 14:00:00');
        $this->assertStringContainsString('years ago', (string)sb()->age($start));
    }

    public function test_noteBold() : void
    {
        $this->assertStringContainsString('<b>', (string)sb()->noteBold());
        $this->assertStringContainsString((string)sb()->note(), (string)sb()->noteBold());
    }

    public function test_linkOpenClose() : void
    {
        $this->assertEquals('<a href="#">', (string)sb()->linkOpen('#'));
        $this->assertEquals('</a>', (string)sb()->linkClose());
    }

    public function test_spanned() : void
    {
        $this->assertEquals('<span class="classA">Test</span>', (string)sb()->spanned('Test', 'classA'));
    }

    public function test_bold() : void
    {
        $this->assertEquals('<b>Test</b>', (string)sb()->bold('Test'));
        $this->assertEquals('<b class="classA">Test</b>', (string)sb()->useClass('classA')->bold('Test'));
    }

    public function test_pre() : void
    {
        $this->assertEquals('<pre>Test</pre>', (string)sb()->pre('Test'));
        $this->assertEquals('<pre class="classA">Test</pre>', (string)sb()->useClass('classA')->pre('Test'));
    }

    public function test_code() : void
    {
        $this->assertEquals('<code>Test</code>', (string)sb()->code('Test'));
        $this->assertEquals('<code class="classA">Test</code>', (string)sb()->useClass('classA')->code('Test'));
    }
}
