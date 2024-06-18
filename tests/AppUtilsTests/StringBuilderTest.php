<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtilsTestClasses\BaseTestCase;
use DateTime;
use function AppUtils\attr;
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

    public function test_translate_with_context(): void
    {
        $result = (string)sb()->tex('%1$s here and %2$s', 'Context here', 'One', 'Two');

        $this->assertEquals('One here and Two', $result);
    }

    public function test_para(): void
    {
        $this->assertEquals('<br><br>', (string)sb()->para());
        $this->assertEquals('<p>Test</p>', (string)sb()->para('Test'));
        $this->assertEquals('', (string)sb()->para(''));
        $this->assertEquals('<p id="42">Foo</p>', (string)sb()->para('Foo', attr('id=42')));
    }

    public function test_nospace(): void
    {
        $this->assertEquals('Test', (string)sb()->nospace('Test'));
        $this->assertEquals('TestFoo', (string)sb()->nospace('Test')->nospace('Foo'));
        $this->assertEquals('Test YoFoo', (string)sb()->nospace('Test')->add('Yo')->nospace('Foo'));
        $this->assertEquals('', (string)sb()->nospace(null));
        $this->assertEquals('', (string)sb()->nospace(''));
        $this->assertEquals('0', (string)sb()->nospace(0));
        $this->assertEquals('0', (string)sb()->nospace('0'));
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
        $this->assertEquals('<i>0</i>', (string)sb()->italic(0));
        $this->assertEquals('<i>0</i>', (string)sb()->italic('0'));
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
        $this->assertEquals('<span class="classA">0</span>', (string)sb()->spanned(0, 'classA'));
        $this->assertEquals('<span class="classA">0</span>', (string)sb()->spanned('0', 'classA'));
    }

    public function test_bool() : void
    {
        $this->assertEquals('true', sb()->bool(true));
        $this->assertEquals('yes', sb()->bool(true, true));
        $this->assertEquals('true', sb()->bool(1));
        $this->assertEquals('true', sb()->bool('1'));
        $this->assertEquals('true', sb()->bool('true'));
        $this->assertEquals('true', sb()->bool('yes'));
    }

    public function test_boolYes() : void
    {
        $this->assertEquals('yes', sb()->boolYes(true));
    }

    public function test_quote() : void
    {
        $this->assertEquals('&quot;Foobar&quot;', (string)sb()->quote('Foobar'));
        $this->assertEquals('', (string)sb()->quote(''));
        $this->assertEquals('&quot;0&quot;', (string)sb()->quote(0));
        $this->assertEquals('&quot;0&quot;', (string)sb()->quote('0'));
    }

    public function test_ul() : void
    {
        $this->assertEquals('<ul><li>Test</li></ul>', (string)sb()->ul(array('Test')));
        $this->assertEquals('<ul><li>0</li></ul>', (string)sb()->ul(array('0')));
        $this->assertEquals('', (string)sb()->ul(array(null, '')));
    }

    public function test_ol() : void
    {
        $this->assertEquals('<ol><li>Test</li></ol>', (string)sb()->ol(array('Test')));
        $this->assertEquals('<ol><li>0</li></ol>', (string)sb()->ol(array('0')));
        $this->assertEquals('', (string)sb()->ol(array(null, '')));
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
        $this->assertEquals('<code>0</code>', (string)sb()->code(0));
        $this->assertEquals('<code>0</code>', (string)sb()->code('0'));
        $this->assertEquals('<code class="classA">Test</code>', (string)sb()->useClass('classA')->code('Test'));
        $this->assertEquals('', (string)sb()->code(null));
    }
}
