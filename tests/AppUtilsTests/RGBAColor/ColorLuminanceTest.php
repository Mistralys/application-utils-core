<?php

declare(strict_types=1);

namespace AppUtilsTests\RGBAColor;

use AppUtils\RGBAColor;
use AppUtils\RGBAColor\ColorException;
use AppUtils\RGBAColor\ColorFactory;
use AppUtilsTestClasses\BaseTestCase;

final class ColorLuminanceTest extends BaseTestCase
{
    public function test_isDarkIsLight() : void
    {
        $tests = array(
            array(
                'label' => 'Light color',
                'rgb' => array(219, 237, 248),
                'dark' => false,
                'luma' => 92,
                '8Bit' => 234
            ),
            array(
                'label' => 'Medium dark color',
                'rgb' => array(20, 116, 196),
                'dark' => true,
                'luma' => 40,
                '8Bit' => 101
            ),
            array(
                'label' => 'Darker color',
                'rgb' => array(0, 61, 143),
                'dark' => true,
                'luma' => 21,
                '8Bit' => 54
            ),
            array(
                'label' => 'Full black',
                'rgb' => array(0, 0, 0),
                'dark' => true,
                'luma' => 0,
                '8Bit' => 0
            ),
            array(
                'label' => 'Full white',
                'rgb' => array(255, 255, 255),
                'dark' => false,
                'luma' => 100,
                '8Bit' => 255
            )
        );

        foreach($tests as $test)
        {
            $color = ColorFactory::create8Bit($test['rgb'][0], $test['rgb'][1], $test['rgb'][2]);

            $label = PHP_EOL.
                $test['label'].PHP_EOL.
                'Color......: '.$color->toCSS().PHP_EOL.
                'Luma %.....: '.$color->getLumaPercent().'%'.PHP_EOL.
                'Luma 8bit..: '.$color->getLuma();

            $this->assertSame($test['dark'], $color->isDark(), 'Color should be considered dark. '.$label);
            $this->assertSame(!$test['dark'], $color->isLight(), 'Color should not be considered light. '.$label);
            $this->assertSame($test['luma'], (int)round($color->getLumaPercent()), 'Luma percentage does not match the expected value. '.$label);
            $this->assertSame($test['8Bit'], $color->getLuma(), 'Luma 8Bit does not match the expected value. '.$label);
        }
    }

    public function test_adjustLumaThreshold() : void
    {
        $color = ColorFactory::createAuto(array(20, 116, 196));

        // This color has a Luma of 40%
        $this->assertSame(40, (int)round($color->getLumaPercent()));

        // It is considered dark with the default setting,
        // which is 50% or less Luma.
        $this->assertTrue($color->isDark());

        // Decrease the default Luma so colors need to have
        // 30% Luma or less.
        RGBAColor::setDarkLumaThreshold(30);

        // Now the color must not be considered dark anymore,
        // as it is at 40% Luma.
        $this->assertFalse($color->isDark());
    }

    public function test_forceDark() : void
    {
        // #E20000 has a luma of ~17%, so it is already dark by luma.
        // Register it explicitly as force-dark and verify the API.
        RGBAColor::setForceDark('#e20000');

        $color = ColorFactory::createFromHEX('e20000');

        $this->assertTrue($color->isDark(), 'Force-dark color must return true for isDark().');
        $this->assertFalse($color->isLight(), 'Force-dark color must return false for isLight().');
    }

    public function test_forceLight() : void
    {
        // #003D8F (0,61,143) has a luma of ~21% and is naturally dark.
        // Force it to be treated as light.
        $naturallyDark = ColorFactory::create8Bit(0, 61, 143);
        $this->assertTrue($naturallyDark->isDark(), 'Pre-condition: color must be naturally dark.');

        RGBAColor::setForceLight('#003D8F');

        $color = ColorFactory::create8Bit(0, 61, 143);
        $this->assertFalse($color->isDark(), 'Force-light color must return false for isDark().');
        $this->assertTrue($color->isLight(), 'Force-light color must return true for isLight().');
    }

    public function test_forceDark_conflictThrowsException() : void
    {
        RGBAColor::setForceLight('#FFFF00');

        $this->expectException(ColorException::class);
        $this->expectExceptionCode(RGBAColor::ERROR_DARK_OVERRIDE_CONFLICT);

        RGBAColor::setForceDark('#FFFF00');
    }

    public function test_forceLight_conflictThrowsException() : void
    {
        RGBAColor::setForceDark('#e20000');

        $this->expectException(ColorException::class);
        $this->expectExceptionCode(RGBAColor::ERROR_DARK_OVERRIDE_CONFLICT);

        RGBAColor::setForceLight('#e20000');
    }

    public function test_removeLumaOverride_restoresDefault() : void
    {
        // #003D8F is naturally dark (luma ~21%).
        RGBAColor::setForceLight('#003D8F');

        $color = ColorFactory::create8Bit(0, 61, 143);
        $this->assertTrue($color->isLight(), 'Pre-condition: color must be force-light.');

        RGBAColor::removeLumaOverride('#003D8F');

        $this->assertTrue($color->isDark(), 'After removal, luma-based dark detection must be restored.');
    }

    public function test_resetLumaOverrides_clearsAll() : void
    {
        RGBAColor::setForceDark('#e20000');
        RGBAColor::setForceLight('#FFFF00');

        RGBAColor::resetLumaOverrides();

        // #FFFF00 has high luma (~93%) and must now be light again.
        $yellow = ColorFactory::createFromHEX('FFFF00');
        $this->assertFalse($yellow->isDark(), 'After reset, yellow must be light by luma.');

        // #E20000 has low luma (~17%) and must now be dark again.
        $red = ColorFactory::createFromHEX('e20000');
        $this->assertTrue($red->isDark(), 'After reset, dark red must be dark by luma.');
    }

    public function test_forceDark_acceptsColorInstance() : void
    {
        $instance = ColorFactory::createFromHEX('e20000');
        RGBAColor::setForceDark($instance);

        $color = ColorFactory::createFromHEX('e20000');
        $this->assertTrue($color->isDark(), 'Passing an RGBAColor instance to setForceDark() must work the same as a HEX string.');
    }

    // region: Support methods

    protected function setUp(): void
    {
        parent::setUp();

        // Reset the Luma threshold
        RGBAColor::setDarkLumaThreshold(RGBAColor::DEFAULT_LUMA_THRESHOLD);
        RGBAColor::resetLumaOverrides();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Reset the Luma threshold
        RGBAColor::setDarkLumaThreshold(RGBAColor::DEFAULT_LUMA_THRESHOLD);
        RGBAColor::resetLumaOverrides();
    }
}
