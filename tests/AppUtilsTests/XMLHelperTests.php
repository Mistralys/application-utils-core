<?php

declare(strict_types=1);

namespace AppUtilsTests;

use PHPUnit\Framework\TestCase;

use AppUtils\XMLHelper;

final class XMLHelperTests extends TestCase
{
    /**
     * @see XMLHelper::convertString()
     */
    public function test_convertString(): void
    {
        $xmlString =
            '<?xml version="1.0" encoding="UTF-8"?>
<root data-type="root">
    <title>Title</title>
    <items>
        <item name="Item 1"/>
        <item name="Item 2"/>
    </items>
    <self_closing/>
    <empty></empty>
</root>';

        $result = XMLHelper::convertString($xmlString);

        $array = $result->toArray();

        $expected = array(
            '@attributes' => array(
                'data-type' => 'root'
            ),
            'title' => array(
                '@text' => 'Title'
            ),
            'items' => array(
                'item' => array(
                    array(
                        '@attributes' => array(
                            'name' => 'Item 1'
                        )
                    ),
                    array(
                        '@attributes' => array(
                            'name' => 'Item 2'
                        )
                    )
                )
            ),
            'self_closing' => null,
            'empty' => null
        );

        $this->assertEquals($expected, $array);
    }
}
