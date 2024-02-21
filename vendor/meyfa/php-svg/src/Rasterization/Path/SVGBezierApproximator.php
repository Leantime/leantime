<?php

namespace SVG\Rasterization\Path;

/**
 * This class can approximate quadratic and cubic Bézier curves by calculating
 * a series of points on them (converting them to polylines).
 */
class SVGBezierApproximator
{
    /**
     * Approximates a quadratic Bézier curve given the start point, a control
     * point, and the end point.
     *
     * All of the points, both input and output, are provided as arrays with
     * floats where [0 => x coordinate, 1 => y coordinate].
     *
     * @param float[] $p0       The start point.
     * @param float[] $p1       The control point.
     * @param float[] $p2       The end point.
     * @param float   $accuracy Maximum squared distance between two points.
     *
     * @return array[] An approximation for the curve, as an array of points.
     */
    public function quadratic($p0, $p1, $p2, $accuracy = 1.0)
    {
        $t      = 0;
        $prev   = $p0;
        $points = array($p0);

        while ($t < 1) {
            $step  = 0.2;

            do {
                $step /= 2;
                $point = self::calculateQuadratic($p0, $p1, $p2, $t + $step);
                $dist  = self::getDistanceSquared($prev, $point);
            } while ($dist > $accuracy);

            $points[] = $prev = $point;
            $t += $step;
        }

        return $points;
    }

    /**
     * Approximates a cubic Bézier curve given the start point, two control
     * points, and the end point.
     *
     * All of the points, both input and output, are provided as arrays with
     * floats where [0 => x coordinate, 1 => y coordinate].
     *
     * @param float[] $p0       The start point.
     * @param float[] $p1       The first control point.
     * @param float[] $p2       The second control point.
     * @param float[] $p3       The end point.
     * @param float   $accuracy Maximum squared distance between two points.
     *
     * @return array[] An approximation for the curve, as an array of points.
     */
    public function cubic($p0, $p1, $p2, $p3, $accuracy = 1.0)
    {
        $t      = 0;
        $prev   = $p0;
        $points = array($p0);

        while ($t < 1) {
            $step  = 0.2;

            do {
                $step /= 2;
                $point = self::calculateCubic($p0, $p1, $p2, $p3, $t + $step);
                $dist  = self::getDistanceSquared($prev, $point);
            } while ($dist > $accuracy);

            $points[] = $prev = $point;
            $t += $step;
        }

        return $points;
    }

    /**
     * Calculates a single point on the quadratic Bézier curve, using $t as its
     * parameter.
     *
     * @param float[] $p0 The curve's start point.
     * @param float[] $p1 The curve's control point.
     * @param float[] $p2 The curve's end point.
     * @param float   $t  The function parameter (distance from start; 0..1).
     *
     * @return float[] The point on the curve (0 => x, 1 => y).
     */
    private static function calculateQuadratic($p0, $p1, $p2, $t)
    {
        $ti = 1 - $t;

        return array(
            $ti * $ti * $p0[0] + 2 * $ti * $t * $p1[0] + $t * $t * $p2[0],
            $ti * $ti * $p0[1] + 2 * $ti * $t * $p1[1] + $t * $t * $p2[1],
        );
    }

    /**
     * Calculates a single point on the cubic Bézier curve, using $t as its
     * parameter.
     *
     * @param float[] $p0 The curve's start point.
     * @param float[] $p1 The curve's first control point.
     * @param float[] $p2 The curve's second control point.
     * @param float[] $p3 The curve's end point.
     * @param float   $t  The function parameter (distance from start; 0..1).
     *
     * @return float[] The point on the curve (0 => x, 1 => y).
     */
    private static function calculateCubic($p0, $p1, $p2, $p3, $t)
    {
        $ti = 1 - $t;

        // first step: lines between the given points
        $a0x = $ti * $p0[0] + $t * $p1[0];
        $a0y = $ti * $p0[1] + $t * $p1[1];
        $a1x = $ti * $p1[0] + $t * $p2[0];
        $a1y = $ti * $p1[1] + $t * $p2[1];
        $a2x = $ti * $p2[0] + $t * $p3[0];
        $a2y = $ti * $p2[1] + $t * $p3[1];

        // second step: lines between points from step 2
        $b0x = $ti * $a0x + $t * $a1x;
        $b0y = $ti * $a0y + $t * $a1y;
        $b1x = $ti * $a1x + $t * $a2x;
        $b1y = $ti * $a1y + $t * $a2y;

        // last step: line between points from step 3, result
        return array(
            $ti * $b0x + $t * $b1x,
            $ti * $b0y + $t * $b1y,
        );
    }

    /**
     * Calculates the squared distance between two points.
     *
     * The squared distance is defined as d = (x1 - x0)^2 + (y1 - y0)^2.
     *
     * @param float[] $p1 The first point (0 => x, 1 => y).
     * @param float[] $p2 The second point (0 => x, 1 => y).
     *
     * @return float The squared distance between the two points.
     */
    private static function getDistanceSquared($p1, $p2)
    {
        $dx = $p2[0] - $p1[0];
        $dy = $p2[1] - $p1[1];

        return $dx * $dx + $dy * $dy;
    }
}
