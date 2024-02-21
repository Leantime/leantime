<?php

namespace SVG;

use SVG\Nodes\Shapes\SVGRect;

/**
 * @SuppressWarnings(PHPMD)
 */
 class SVGRectTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        // should not set any attributes by default
        $obj = new SVGRect();
        $this->assertSame(array(), $obj->getSerializableAttributes());

        // should set attributes when provided
        $obj = new SVGRect(37, 42, 100, 200);
        $this->assertSame(array(
            'x' => '37',
            'y' => '42',
            'width' => '100',
            'height' => '200'
        ), $obj->getSerializableAttributes());
    }

    public function testGetX()
    {
        $obj = new SVGRect();

        // should return the attribute
        $obj->setAttribute('x', 42);
        $this->assertSame('42', $obj->getX());
    }

    public function testSetX()
    {
        $obj = new SVGRect();

        // should update the attribute
        $obj->setX(42);
        $this->assertSame('42', $obj->getAttribute('x'));

        // should return same instance
        $this->assertSame($obj, $obj->setX(42));
    }

    public function testGetY()
    {
        $obj = new SVGRect();

        // should return the attribute
        $obj->setAttribute('y', 42);
        $this->assertSame('42', $obj->getY());
    }

    public function testSetY()
    {
        $obj = new SVGRect();

        // should update the attribute
        $obj->setY(42);
        $this->assertSame('42', $obj->getAttribute('y'));

        // should return same instance
        $this->assertSame($obj, $obj->setY(42));
    }

    public function testGetWidth()
    {
        $obj = new SVGRect();

        // should return the attribute
        $obj->setAttribute('width', 42);
        $this->assertSame('42', $obj->getWidth());
    }

    public function testSetWidth()
    {
        $obj = new SVGRect();

        // should update the attribute
        $obj->setWidth(42);
        $this->assertSame('42', $obj->getAttribute('width'));

        // should return same instance
        $this->assertSame($obj, $obj->setWidth(42));
    }

    public function testGetHeight()
    {
        $obj = new SVGRect();

        // should return the attribute
        $obj->setAttribute('height', 42);
        $this->assertSame('42', $obj->getHeight());
    }

    public function testSetHeight()
    {
        $obj = new SVGRect();

        // should update the attribute
        $obj->setHeight(42);
        $this->assertSame('42', $obj->getAttribute('height'));

        // should return same instance
        $this->assertSame($obj, $obj->setHeight(42));
    }

    public function testGetRX()
    {
        $obj = new SVGRect();

        // should return the attribute
        $obj->setAttribute('rx', 42);
        $this->assertSame('42', $obj->getRX());
    }

    public function testSetRX()
    {
        $obj = new SVGRect();

        // should update the attribute
        $obj->setRX(42);
        $this->assertSame('42', $obj->getAttribute('rx'));

        // should return same instance
        $this->assertSame($obj, $obj->setRX(42));
    }

    public function testGetRY()
    {
        $obj = new SVGRect();

        // should return the attribute
        $obj->setAttribute('ry', 42);
        $this->assertSame('42', $obj->getRY());
    }

    public function testSetRY()
    {
        $obj = new SVGRect();

        // should update the attribute
        $obj->setRY(42);
        $this->assertSame('42', $obj->getAttribute('ry'));

        // should return same instance
        $this->assertSame($obj, $obj->setRY(42));
    }

    public function testRasterize()
    {
        $obj = new SVGRect(37, 42, 100, 200);
        $obj->setAttribute('rx', 15);
        $obj->setAttribute('ry', 25);

        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        // should call image renderer with correct options
        $rast->expects($this->once())->method('render')->with(
            $this->identicalTo('rect'),
            $this->identicalTo(array(
                'x' => '37',
                'y' => '42',
                'width' => '100',
                'height' => '200',
                'rx' => '15',
                'ry' => '25',
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
