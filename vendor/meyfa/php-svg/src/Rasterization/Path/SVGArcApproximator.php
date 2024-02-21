<?php

namespace SVG\Rasterization\Path;

/**
 * This class can approximate elliptical arc segments by calculating a series of
 * points on them (converting them to polylines).
 */
class SVGArcApproximator
{
    /**
     * Approximates a quadratic Bézier curve given the start point, a control
     * point, and the end point.
     *
     * All of the points, both input and output, are provided as arrays with
     * floats where [0 => x coordinate, 1 => y coordinate].
     *
     * @param float[] $p0 The start point (x0, y0).
     * @param float[] $p1 The end point (x1, y1).
     * @param bool    $fa The large arc flag.
     * @param bool    $fs The sweep flag.
     * @param float   $rx The x radius.
     * @param float   $ry The y radius.
     * @param float   $xa The x-axis angle / the ellipse's rotation (radians).
     *
     * @return array[] An approximation for the curve, as an array of points.
     */
    public function approximate($p0, $p1, $fa, $fs, $rx, $ry, $xa)
    {
        $rx = abs($rx);
        $ry = abs($ry);

        $xa = fmod($xa, M_PI * 2);
        if ($xa < 0) {
            $xa += M_PI * 2;
        }

        // out-of-range parameter handling according to W3; see
        // https://www.w3.org/TR/SVG11/implnote.html#ArcImplementationNotes
        if ($p0[0] == $p1[0] && $p0[1] == $p1[1]) {
            // arc with equal points is treated as nonexistent
            return array();
        } elseif ($rx == 0 || $ry == 0) {
            // arc with no radius is treated as straight line
            return array($p0, $p1);
        }

        $params = self::endpointToCenter($p0, $p1, $fa, $fs, $rx, $ry, $xa);
        list($center, $angleStart, $angleDelta) = $params;

        // TODO implement better calculation for $numSteps
        // It would be better if we had access to the rasterization scale for
        // this, otherwise there is no way to make this accurate for every zoom
        $dist = abs($p0[0] - $p1[0]) + abs($p0[1] - $p1[1]);
        $numSteps = max(8, ceil(rad2deg($angleDelta) * $dist / 1000));
        $stepSize = $angleDelta / $numSteps;

        $points = array();
        for ($i = 0; $i <= $numSteps; ++$i) {
            $angle = $angleStart + $stepSize * $i;
            $points[] = self::calculatePoint($center, $rx, $ry, $xa, $angle);
        }

        return $points;
    }

    /**
     * Calculates a single point on an ellipsis described by its center
     * parameterization.
     *
     * @param float[] $center The ellipse's center point (x, y).
     * @param float   $rx     The radius along the ellipse's x-axis.
     * @param float   $ry     The radius along the ellipse's y-axis.
     * @param float   $xa     The x-axis angle / the ellipse's rotation (radians).
     * @param float   $angle  The point's position on the ellipse.
     *
     * @return float[] The calculated point as an (x, y) tuple.
     */
    private static function calculatePoint($center, $rx, $ry, $xa, $angle)
    {
        $a = $rx * cos($angle);
        $b = $ry * sin($angle);

        return array(
            cos($xa) * $a - sin($xa) * $b + $center[0],
            sin($xa) * $a + cos($xa) * $b + $center[1],
        );
    }

    /**
     * Converts an ellipse in endpoint parameterization (standard for SVG paths)
     * to the corresponding center parameterization (easier to work with).
     *
     * In other words, takes two points, sweep flags, and size/orientation
     * values and computes from them the ellipse's optimal center point and the
     * angles the segment covers. For this, the start angle and the angle delta
     * are returned.
     *
     * The formulas can be found in W3's SVG spec.
     *
     * @see https://www.w3.org/TR/SVG11/implnote.html#ArcImplementationNotes
     *
     * @param float[] $p0 The start point (x0, y0).
     * @param float[] $p1 The end point (x1, y1).
     * @param bool    $fa The large arc flag.
     * @param bool    $fs The sweep flag.
     * @param float   $rx The x radius.
     * @param float   $ry The y radius.
     * @param float   $xa The x-axis angle / the ellipse's rotation (radians).
     *
     * @return float[] A tuple with (center (cx, cy), angleStart, angleDelta).
     */
    private static function endpointToCenter($p0, $p1, $fa, $fs, $rx, $ry, $xa)
    {
        $rx2 = $rx * $rx;
        $ry2 = $ry * $ry;

        $xsubhalf = ($p0[0] - $p1[0]) / 2;
        $ysubhalf = ($p0[1] - $p1[1]) / 2;

        // Step 1: Compute (x1', y1')
        $x1prime  =  cos($xa) * $xsubhalf + sin($xa) * $ysubhalf;
        $y1prime  = -sin($xa) * $xsubhalf + cos($xa) * $ysubhalf;
        $x1prime2 = $x1prime * $x1prime;
        $y1prime2 = $y1prime * $y1prime;

        // TODO ensure radiuses are large enough

        // Step 2: Compute (cx', cy')
        $fracA   = ($rx2 * $ry2) - ($rx2 * $y1prime2) - ($ry2 * $x1prime2);
        $fracB   = ($rx2 * $y1prime2) + ($ry2 * $x1prime2);
        $frac    = sqrt(abs($fracA / $fracB));
        $cSign   = $fa != $fs ? 1 : -1;
        $cxprime = $cSign * $frac * ( ($rx * $y1prime) / $ry);
        $cyprime = $cSign * $frac * (-($ry * $x1prime) / $rx);

        // Step 3: Compute (cx, cy) from (cx', cy')
        $cx = cos($xa) * $cxprime - sin($xa) * $cyprime + ($p0[0] + $p1[0]) / 2;
        $cy = sin($xa) * $cxprime + cos($xa) * $cyprime + ($p0[1] + $p1[1]) / 2;

        // Step 4: Compute the angles
        $angleStart = self::vectorAngle(
            1,                              0,
            ($x1prime - $cxprime) / $rx,    ($y1prime - $cyprime) / $ry
        );
        $angleDelta = fmod(self::vectorAngle(
            ( $x1prime - $cxprime) / $rx,   ( $y1prime - $cyprime) / $ry,
            (-$x1prime - $cxprime) / $rx,   (-$y1prime - $cyprime) / $ry
        ), M_PI * 2);

        // Adapt angles to sweep flags
        if (!$fs && $angleDelta > 0) {
            $angleStart -= M_PI * 2;
        } elseif ($fs && $angleDelta < 0) {
            $angleStart += M_PI * 2;
        }

        return array(array($cx, $cy), $angleStart, $angleDelta);
    }

    /**
     * Computes the angle between two given vectors.
     *
     * @param float $ux First vector's x coordinate.
     * @param float $uy First vector's y coordinate.
     * @param float $vx Second vector's x coordinate.
     * @param float $vy Second vector's y coordinate.
     *
     * @return float The angle, in radians.
     */
    private static function vectorAngle($ux, $uy, $vx, $vy)
    {
        $ta = atan2($uy, $ux);
        $tb = atan2($vy, $vx);

        if ($tb >= $ta) {
            return $tb - $ta;
        }

        return 2 * M_PI - ($ta - $tb);
    }
}
