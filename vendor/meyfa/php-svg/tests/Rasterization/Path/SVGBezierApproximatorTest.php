<?php

namespace SVG;

use SVG\Rasterization\Path\SVGBezierApproximator;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGBezierApproximatorTest extends \PHPUnit\Framework\TestCase
{
    public function testQuadratic()
    {
        $svgBezier = new SVGBezierApproximator();
        $p0 = array(10.5, 10.5);
        $p1 = array(10.6, 10.6);
        $p2 = array(10.7, 10.7);
        $result = $svgBezier->quadratic($p0, $p1, $p2);

        $this->assertCount(12, $result);
        $this->assertSame(10.5, $result[0][0]);
        $this->assertSame(10.5, $result[0][1]);
        $this->assertSame(10.52, $result[1][0]);
        $this->assertSame(10.52, $result[1][1]);
    }

    public function testCubic()
    {
        $svgBezier = new SVGBezierApproximator();
        $p0 = array(10.5, 10.5);
        $p1 = array(10.6, 10.6);
        $p2 = array(10.7, 10.7);
        $p3 = array(10.8, 10.8);
        $result = $svgBezier->cubic($p0, $p1, $p2, $p3);

        $this->assertCount(12, $result);
        $this->assertSame(10.5, $result[0][0]);
        $this->assertSame(10.5, $result[0][1]);
        $this->assertSame(10.53, $result[1][0]);
        $this->assertSame(10.53, $result[1][1]);
    }
}
