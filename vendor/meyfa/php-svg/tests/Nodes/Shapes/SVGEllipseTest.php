<?php

namespace SVG;

use SVG\Nodes\Shapes\SVGEllipse;

/**
 * @SuppressWarnings(PHPMD)
 */
 class SVGEllipseTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        // should not set any attributes by default
        $obj = new SVGEllipse();
        $this->assertSame(array(), $obj->getSerializableAttributes());

        // should set attributes when provided
        $obj = new SVGEllipse(37, 42, 100, 200);
        $this->assertSame(array(
            'cx' => '37',
            'cy' => '42',
            'rx' => '100',
            'ry' => '200'
        ), $obj->getSerializableAttributes());
    }

    public function testGetCenterX()
    {
        $obj = new SVGEllipse();

        // should return the attribute
        $obj->setAttribute('cx', 42);
        $this->assertSame('42', $obj->getCenterX());
    }

    public function testSetCenterX()
    {
        $obj = new SVGEllipse();

        // should update the attribute
        $obj->setCenterX(42);
        $this->assertSame('42', $obj->getAttribute('cx'));

        // should return same instance
        $this->assertSame($obj, $obj->setCenterX(42));
    }

    public function testGetCenterY()
    {
        $obj = new SVGEllipse();

        // should return the attribute
        $obj->setAttribute('cy', 42);
        $this->assertSame('42', $obj->getCenterY());
    }

    public function testSetCenterY()
    {
        $obj = new SVGEllipse();

        // should update the attribute
        $obj->setCenterY(42);
        $this->assertSame('42', $obj->getAttribute('cy'));

        // should return same instance
        $this->assertSame($obj, $obj->setCenterY(42));
    }

    public function testGetRadiusX()
    {
        $obj = new SVGEllipse();

        // should return the attribute
        $obj->setAttribute('rx', 42);
        $this->assertSame('42', $obj->getRadiusX());
    }

    public function testSetRadiusX()
    {
        $obj = new SVGEllipse();

        // should update the attribute
        $obj->setRadiusX(42);
        $this->assertSame('42', $obj->getAttribute('rx'));

        // should return same instance
        $this->assertSame($obj, $obj->setRadiusX(42));
    }

    public function testGetRadiusY()
    {
        $obj = new SVGEllipse();

        // should return the attribute
        $obj->setAttribute('ry', 42);
        $this->assertSame('42', $obj->getRadiusY());
    }

    public function testSetRadiusY()
    {
        $obj = new SVGEllipse();

        // should update the attribute
        $obj->setRadiusY(42);
        $this->assertSame('42', $obj->getAttribute('ry'));

        // should return same instance
        $this->assertSame($obj, $obj->setRadiusY(42));
    }

    public function testRasterize()
    {
        $obj = new SVGEllipse(37, 42, 100, 200);

        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        // should call image renderer with correct options
        $rast->expects($this->once())->method('render')->with(
            $this->identicalTo('ellipse'),
            $this->identicalTo(array(
                'cx' => '37',
                'cy' => '42',
                'rx' => '100',
                'ry' => '200',
            )),
            $this->identicalTo($obj)
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
