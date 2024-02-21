<?php

namespace SVG;

use SVG\Nodes\Shapes\SVGPath;

/**
 * @SuppressWarnings(PHPMD)
 */
 class SVGPathTest extends \PHPUnit\Framework\TestCase
{
    private static $sampleDescription = 'M100,100 h20 Z M200,200 h20';
    private static $sampleParse = array(
        array('id' => 'M', 'args' => array(100, 100)),
        array('id' => 'h', 'args' => array(20)),
        array('id' => 'Z', 'args' => array()),
        array('id' => 'M', 'args' => array(200, 200)),
        array('id' => 'h', 'args' => array(20)),
    );
    private static $sampleApproximate = array(
        array(array(100, 100), array(120, 100), array(100, 100)),
        array(array(200, 200), array(220, 200)),
    );

    public function test__construct()
    {
        // should not set any attributes by default
        $obj = new SVGPath();
        $this->assertSame(array(), $obj->getSerializableAttributes());

        // should set attributes when provided
        $obj = new SVGPath(self::$sampleDescription);
        $this->assertSame(array(
            'd' => self::$sampleDescription,
        ), $obj->getSerializableAttributes());
    }

    public function testGetDescription()
    {
        $obj = new SVGPath();

        // should return the attribute
        $obj->setAttribute('d', self::$sampleDescription);
        $this->assertSame(self::$sampleDescription, $obj->getDescription());
    }

    public function testSetDescription()
    {
        $obj = new SVGPath();

        // should update the attribute
        $obj->setDescription(self::$sampleDescription);
        $this->assertSame(self::$sampleDescription, $obj->getAttribute('d'));

        // should return same instance
        $this->assertSame($obj, $obj->setDescription(self::$sampleDescription));
    }

    public function testRasterizeWithNull()
    {
        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();
        $obj = new SVGPath();

        $this->assertNull($obj->rasterize($rast));
    }

    public function testRasterize()
    {
        $obj = new SVGPath(self::$sampleDescription);

        // setup mocks
        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();
        $pathParser = $this->getMockBuilder('\SVG\Rasterization\Path\SVGPathParser')
            ->getMock();
        $pathApproximator = $this->getMockBuilder('\SVG\Rasterization\Path\SVGPathApproximator')
            ->getMock();

        // link mocks
        $rast->expects($this->any())->method('getPathParser')
            ->willReturn($pathParser);
        $rast->expects($this->any())->method('getPathApproximator')
            ->willReturn($pathApproximator);

        // should call path parser with description attribute
        $pathParser->expects($this->any())->method('parse')->with(
            $this->identicalTo(self::$sampleDescription)
        )->willReturn(self::$sampleParse);

        // should call path approximator with parser's return value
        $pathApproximator->expects($this->any())->method('approximate')->with(
            $this->identicalTo(self::$sampleParse)
        )->willReturn(self::$sampleApproximate);

        // should call image renderer with correct options
        // (once for every subpath)
        $rast->expects($this->exactly(2))->method('render')->withConsecutive(
            array(
                $this->identicalTo('polygon'),
                $this->identicalTo(array(
                    'open' => true,
                    'points' => self::$sampleApproximate[0],
                )),
                $this->identicalTo($obj)
            ),
            array(
                $this->identicalTo('polygon'),
                $this->identicalTo(array(
                    'open' => true,
                    'points' => self::$sampleApproximate[1],
                )),
                $this->identicalTo($obj)
            )
        );
        $obj->rasterize($rast);

        // should not rasterize with 'display: none' style
        $obj->setStyle('display', 'none');
        $obj->rasterize($rast);

        // should not rasterize with 'visibility: hidden' or 'collapse' style
        $obj->setStyle('display', null);
        $obj->setStyle('visibility', 'hidden');
        $obj->rasterize($rast);
        $obj->setStyle('visibility', 'collapse');
        $obj->rasterize($rast);
    }
}
