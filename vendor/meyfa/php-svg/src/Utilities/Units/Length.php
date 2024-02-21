<?php

namespace SVG\Utilities\Units;

final class Length
{
    /**
     * Converts any valid SVG length string into an absolute pixel length,
     * using the given canvas width as reference for percentages.
     *
     * If the string does not denote a valid length unit, null is returned.
     *
     * @param string $unit       The SVG length string to convert.
     * @param float  $viewLength The canvas width to use as reference length.
     *
     * @return float|null The absolute pixel number the given string denotes.
     */
    public static function convert($unit, $viewLength)
    {
        $regex = '/^([+-]?\d*\.?\d*)(px|pt|pc|cm|mm|in|%)?$/';
        if (!preg_match($regex, $unit, $matches) || $matches[1] === '') {
            return null;
        }

        $factors = array(
            'px' => (1),                    // base unit
            'pt' => (16 / 12),              // 12pt = 16px
            'pc' => (16),                   // 1pc = 16px
            'in' => (96),                   // 1in = 96px
            'cm' => (96 / 2.54),            // 1in = 96px, 1in = 2.54cm
            'mm' => (96 / 25.4),            // 1in = 96px, 1in = 25.4mm
            '%'  => ($viewLength / 100),    // 1% = 1/100 of viewLength
        );

        $value = floatval($matches[1]);
        $unit  = empty($matches[2]) ? 'px' : $matches[2];

        return $value * $factors[$unit];
    }
}
