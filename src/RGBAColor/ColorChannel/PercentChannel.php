<?php
/**
 * @package Application Utils
 * @subpackage RGBAColor
 * @see \AppUtils\RGBAColor\ColorChannel\PercentChannel
 */

declare(strict_types=1);

namespace AppUtils\RGBAColor\ColorChannel;

use AppUtils\RGBAColor\ColorChannel;
use AppUtils\RGBAColor\UnitsConverter;

/**
 * Color channel with values from 0 to 100.
 *
 * Native value: {@see self::getPercent()} and
 * {@see self::getPercentRounded()}.
 *
 * @package Application Utils
 * @subpackage RGBAColor
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class PercentChannel extends ColorChannel
{
    public const VALUE_MIN = 0.0;
    public const VALUE_MAX = 100.0;

    /**
     * @var float
     */
    protected float $value;

    /**
     * @param int|float $value
     */
    public function __construct($value)
    {
        $value = (float)$value;

        if($value < self::VALUE_MIN) { $value = self::VALUE_MIN; }
        if($value > self::VALUE_MAX) { $value = self::VALUE_MAX; }

        $this->value = $value;
    }

    public function getValue() : float
    {
        return $this->value;
    }

    public function getAlpha() : float
    {
        return UnitsConverter::percent2Alpha($this->value);
    }

    public function get8Bit() : int
    {
        return UnitsConverter::percent2IntEightBit($this->value);
    }

    public function get7Bit() : int
    {
        return UnitsConverter::percent2IntSevenBit($this->value);
    }

    public function getPercent() : float
    {
        return $this->value;
    }

    public function invert() : PercentChannel
    {
        return ColorChannel::percent(100-$this->value);
    }
}
