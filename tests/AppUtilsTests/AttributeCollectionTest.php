<?php

declare(strict_types=1);

namespace AppUtilsTests;

use AppUtils\AttributeCollection;
use AppUtilsTestClasses\BaseTestCase;
use AppUtilsTestClasses\AttributableTraitImpl;
use function AppUtils\attr;
use function AppUtils\sb;

final class AttributeCollectionTest extends BaseTestCase
{
    public function test_initialAttributes(): void
    {
        $attribs = AttributeCollection::create(array(
            'name' => 'Name',
            'class' => '    class-one class-two  '
        ));

        $this->assertTrue($attribs->hasClasses());
        $this->assertTrue($attribs->hasClass('class-one'));
        $this->assertTrue($attribs->hasClass('class-two'));
        $this->assertEquals('Name', $attribs->getAttribute('name'));
    }

    public function test_initialAttributeString() : void
    {
        $attribs = AttributeCollection::createAuto('name=Foo&id=45');

        $this->assertEquals('Foo', $attribs->getAttribute('name'));
        $this->assertEquals('45', $attribs->getAttribute('id'));
    }

    public function test_globalFunction() : void
    {
        $attribs = attr('name=Foo');

        $this->assertEquals('Foo', $attribs->getAttribute('name'));
    }

    public function test_hasAttribute(): void
    {
        $attribs = AttributeCollection::create();

        $this->assertFalse($attribs->hasAttribute('test'));

        $attribs->attr('test', null);
        $this->assertFalse($attribs->hasAttribute('test'));

        $attribs->attr('test', '');
        $this->assertFalse($attribs->hasAttribute('test'));

        $attribs->attr('test', false);
        $this->assertTrue($attribs->hasAttribute('test'));
    }

    public function test_removeAttribute(): void
    {
        $attribs = AttributeCollection::create();

        $attribs->attr('test', 'value');
        $this->assertTrue($attribs->hasAttribute('test'));

        $attribs->remove('test');
        $this->assertFalse($attribs->hasAttribute('test'));
    }

    public function test_escapeQuotes(): void
    {
        $attribs = AttributeCollection::create();

        $attribs->attrQuotes('test', 'Label with "Quotes"');

        $this->assertEquals('Label with &quot;Quotes&quot;', $attribs->getAttribute('test'));
    }

    public function test_href(): void
    {
        $attribs = AttributeCollection::create();

        $attribs->href('https://testdomain.com?param=value&foo=bar&amp;preencoded=true');

        $this->assertEquals('https://testdomain.com?param=value&amp;foo=bar&amp;preencoded=true', $attribs->getAttribute('href'));
    }

    public function test_variableTypes(): void
    {
        $attribs = AttributeCollection::create(array(
            'string' => 'String',
            'stringBuilder' => sb()->add('StringBuilder'),
            'null' => null,
            'empty' => '',
            'zero' => 0,
            'int' => 45,
            'float' => 42.5,
            'bool' => true
        ));

        $this->assertEquals('String', $attribs->getAttribute('string'));
        $this->assertEquals('StringBuilder', $attribs->getAttribute('stringBuilder'));
        $this->assertEquals('', $attribs->getAttribute('null'));
        $this->assertEquals('', $attribs->getAttribute('empty'));
        $this->assertEquals('0', $attribs->getAttribute('zero'));
        $this->assertEquals('45', $attribs->getAttribute('int'));
        $this->assertEquals('42.5', $attribs->getAttribute('float'));
        $this->assertEquals('true', $attribs->getAttribute('bool'));
    }

    public function test_renderEmptyAttribute(): void
    {
        $attribs = AttributeCollection::create(array(
            'empty' => '',
        ));

        $this->assertSame('', $attribs->render());
    }

    public function test_renderForcedEmptyAttribute(): void
    {
        $attribs = AttributeCollection::create(array(
            'empty' => '',
        ))
            ->setKeepIfEmpty('empty');

        $this->assertSame(' empty=""', $attribs->render());
    }

    public function test_setEmptyLater(): void
    {
        $attribs = AttributeCollection::create(array(
            'string' => 'Non empty value',
        ))
            ->setKeepIfEmpty('string');

        $attribs->attr('string', '');

        $this->assertSame(' string=""', $attribs->render());
    }

    public function test_setValueLater(): void
    {
        $attribs = AttributeCollection::create(array(
            'string' => '',
        ))
            ->setKeepIfEmpty('string');

        $attribs->attr('string', 'Value');

        $this->assertSame(' string="Value"', $attribs->render());
    }

    public function test_attributableTrait() : void
    {
        $attributable = new AttributableTraitImpl();

        $attributable->attr('name', 'Foo');

        $this->assertSame('Foo', $attributable->getAttributes()->getAttribute('name'));
    }
}
