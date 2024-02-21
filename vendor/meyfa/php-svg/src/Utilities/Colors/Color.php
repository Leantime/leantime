<?php

namespace SVG\Utilities\Colors;

use SVG\Utilities\Units\Angle;

final class Color
{
    /**
     * Converts any valid SVG color string into an array of RGBA components.
     *
     * All of the components are ints 0-255.
     *
     * @param string $color The color string to convert, as specified in SVG.
     *
     * @return int[] The color converted to RGBA components.
     */
    public static function parse($color)
    {
        $lookupResult = ColorLookup::get($color);
        if (isset($lookupResult)) {
            return $lookupResult;
        }

        // pass on to dedicated functions depending on notation
        if (preg_match('/^#([0-9A-F]+)$/i', $color, $matches)) {
            list($r, $g, $b, $a) = self::parseHexComponents($matches[1]);
        } elseif (preg_match('/^rgba?\((.*)\)$/', $color, $matches)) {
            list($r, $g, $b, $a) = self::parseRGBAComponents($matches[1]);
        } elseif (preg_match('/^hsla?\((.*)\)$/', $color, $matches)) {
            list($r, $g, $b, $a) = self::parseHSLAComponents($matches[1]);
        }

        // any illegal component invalidates all components
        if (!isset($r) || !isset($g) || !isset($b) || !isset($a)) {
            return array(0, 0, 0, 0);
        }

        return self::clamp($r, $g, $b, $a);
    }

    /**
     * Clamps the RGBA components into the range 0 - 255 (inclusive). All values
     * are converted to integers.
     *
     * @param float $r
     * @param float $g
     * @param float $b
     * @param float $a
     * @return int[] The clamped integer components array.
     */
    private static function clamp($r, $g, $b, $a)
    {
        $r = min(max(intval($r), 0), 255);
        $g = min(max(intval($g), 0), 255);
        $b = min(max(intval($b), 0), 255);
        $a = min(max(intval($a), 0), 255);

        return array($r, $g, $b, $a);
    }

    /**
     * Takes a hex string of length 3, 4, 6 or 8 and converts it into an array
     * of floating-point RGBA components.
     *
     * For strings of invalid length, all components will be null.
     *
     * @param string $str The hexadecimal color string to convert.
     *
     * @return float[] The RGBA components (0 - 255).
     */
    private static function parseHexComponents($str)
    {
        $len = strlen($str);

        $r = $g = $b = $a = null;

        if ($len === 6 || $len === 8) {
            $r = hexdec($str[0].$str[1]);
            $g = hexdec($str[2].$str[3]);
            $b = hexdec($str[4].$str[5]);
            $a = $len === 8 ? hexdec($str[6].$str[7]) : 255;
        } elseif ($len === 3 || $len == 4) {
            $r = hexdec($str[0].$str[0]);
            $g = hexdec($str[1].$str[1]);
            $b = hexdec($str[2].$str[2]);
            $a = $len === 4 ? hexdec($str[3].$str[3]) : 255;
        }

        return array($r, $g, $b, $a);
    }

    /**
     * Takes a parameter string from the rgba functional notation
     * (i.e., the 'x' inside 'rgb(x)') and converts it into an array of
     * floating-point RGBA components.
     *
     * If any of the components could not be deduced, that component will be
     * null. No other component will be influenced.
     *
     * @param string $str The parameter string to convert.
     *
     * @return float[] The RGBA components.
     */
    private static function parseRGBAComponents($str)
    {
        $params = preg_split('/(\s*[\/,]\s*)|(\s+)/', trim($str));
        if (count($params) !== 3 && count($params) !== 4) {
            return array(null, null, null, null);
        }

        $r = self::parseRGBAComponent($params[0]);
        $g = self::parseRGBAComponent($params[1]);
        $b = self::parseRGBAComponent($params[2]);
        $a = count($params) < 4 ? 255 : self::parseRGBAComponent($params[3], 1, 255);

        return array($r, $g, $b, $a);
    }

    /**
     * Converts a single numeric color component (e.g. '10.5' or '20%') into a
     * floating-point value.
     *
     * The optional base argument represents 100%. It should be set to 255 for
     * the RGB components and to 1 for the A component.
     *
     * The optional scalar argument is the multiplier applied to the result. It
     * should be set to 1 for the RGB components (since they are already in the
     * correct final range) and to 255 for the A component (since it would
     * otherwise be between 0 and 1).
     *
     * @param string $str    The component string.
     * @param int    $base   The base value for percentages.
     * @param int    $scalar A multiplier for the final value.
     *
     * @return float The floating-point converted component.
     */
    private static function parseRGBAComponent($str, $base = 255, $scalar = 1)
    {
        $regex = '/^([+-]?(?:\d+|\d*\.\d+))(%)?$/';
        if (!preg_match($regex, $str, $matches)) {
            return null;
        }
        if (isset($matches[2]) && $matches[2] === '%') {
            return floatval($matches[1]) * $base / 100 * $scalar;
        }
        return floatval($matches[1]) * $scalar;
    }

    /**
     * Takes a parameter string from the hsla functional notation
     * (i.e., the 'x' inside 'hsl(x)') and converts it into an array of
     * floating-point RGBA components.
     *
     * If any of the components could not be deduced, that component will be
     * null. No other component will be influenced.
     *
     * @param string $str The parameter string to convert.
     *
     * @return float[] The RGBA components.
     */
    private static function parseHSLAComponents($str)
    {
        // split on delimiters
        $params = preg_split('/(\s*[\/,]\s*)|(\s+)/', trim($str));
        if (count($params) !== 3 && count($params) !== 4) {
            return null;
        }

        // parse HSL
        $h = Angle::convert($params[0]);
        $s = self::parseRGBAComponent($params[1], 1);
        $l = self::parseRGBAComponent($params[2], 1);

        // convert HSL to RGB
        $r = $g = $b = null;
        if (isset($h) && isset($s) && isset($l)) {
            list($r, $g, $b) = self::convertHSLtoRGB($h, $s, $l);
        }
        // add alpha
        $a = count($params) < 4 ? 255 : self::parseRGBAComponent($params[3], 1, 255);

        return array($r, $g, $b, $a);
    }

    /**
     * Takes three arguments H (0 - 360), S (0 - 1), L (0 - 1) and converts them
     * to RGB components (0 - 255).
     *
     * @param float $h The hue.
     * @param float $s The saturation.
     * @param float $l The lightness.
     *
     * @return float[] An RGB array with values ranging from 0 - 255 each.
     */
    private static function convertHSLtoRGB($h, $s, $l)
    {
        $s = min(max($s, 0), 1);
        $l = min(max($l, 0), 1);

        if ($s == 0) {
            // shortcut if grayscale
            return array($l * 255, $l * 255, $l * 255);
        }

        // compute intermediates
        $m2 = ($l <= 0.5) ? ($l * (1 + $s)) : ($l + $s - $l * $s);
        $m1 = 2 * $l - $m2;

        // convert intermediates + hue to components
        $r = self::convertHSLHueToRGBComponent($m1, $m2, $h + 120);
        $g = self::convertHSLHueToRGBComponent($m1, $m2, $h);
        $b = self::convertHSLHueToRGBComponent($m1, $m2, $h - 120);

        return array($r, $g, $b);
    }

    /**
     * Takes the two intermediate values from `convertHSLtoRGB()` and the hue,
     * and computes the component's value.
     *
     * @param float $m1  Intermediate 1.
     * @param float $m2  Intermediate 2.
     * @param float $hue The hue, adapted to the component.
     *
     * @return float The component's value (0 - 255).
     */
    private static function convertHSLHueToRGBComponent($m1, $m2, $hue)
    {
        // bring hue into range (fmod assures that 0 <= abs($hue) < 360, while
        // the next step assures that it's positive)
        $hue = fmod($hue, 360);
        if ($hue < 0) {
            $hue += 360;
        }

        $v = $m1;

        if ($hue < 60) {
            $v = $m1 + ($m2 - $m1) * $hue / 60;
        } elseif ($hue < 180) {
            $v = $m2;
        } elseif ($hue < 240) {
            $v = $m1 + ($m2 - $m1) * (240 - $hue) / 60;
        }

        return $v * 255;
    }
}
