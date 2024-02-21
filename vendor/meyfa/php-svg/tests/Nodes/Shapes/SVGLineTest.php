<?php

namespace SVG;

use SVG\Nodes\Shapes\SVGLine;

/**
 * @SuppressWarnings(PHPMD)
 */
 class SVGLineTest extends \PHPUnit\Framework\TestCase
{
    public function test__construct()
    {
        // should not set any attributes by default
        $obj = new SVGLine();
        $this->assertSame(array(), $obj->getSerializableAttributes());

        // should set attributes when provided
        $obj = new SVGLine(11, 12, 13, 14);
        $this->assertSame(array(
            'x1' => '11',
            'y1' => '12',
            'x2' => '13',
            'y2' => '14'
        ), $obj->getSerializableAttributes());
    }

    public function testGetX1()
    {
        $obj = new SVGLine();

        // should return the attribute
        $obj->setAttribute('x1', 42);
        $this->assertSame('42', $obj->getX1());
    }

    public function testSetX1()
    {
        $obj = new SVGLine();

        // should update the attribute
        $obj->setX1(42);
        $this->assertSame('42', $obj->getAttribute('x1'));

        // should return same instance
        $this->assertSame($obj, $obj->setX1(42));
    }

    public function testGetY1()
    {
        $obj = new SVGLine();

        // should return the attribute
        $obj->setAttribute('y1', 42);
        $this->assertSame('42', $obj->getY1());
    }

    public function testSetY1()
    {
        $obj = new SVGLine();

        // should update the attribute
        $obj->setY1(42);
        $this->assertSame('42', $obj->getAttribute('y1'));

        // should return same instance
        $this->assertSame($obj, $obj->setY1(42));
    }

    public function testGetX2()
    {
        $obj = new SVGLine();

        // should return the attribute
        $obj->setAttribute('x2', 42);
        $this->assertSame('42', $obj->getX2());
    }

    public function testSetX2()
    {
        $obj = new SVGLine();

        // should update the attribute
        $obj->setX2(42);
        $this->assertSame('42', $obj->getAttribute('x2'));

        // should return same instance
        $this->assertSame($obj, $obj->setX2(42));
    }

    public function testGetY2()
    {
        $obj = new SVGLine();

        // should return the attribute
        $obj->setAttribute('y2', 42);
        $this->assertSame('42', $obj->getY2());
    }

    public function testSetY2()
    {
        $obj = new SVGLine();

        // should update the attribute
        $obj->setY2(42);
        $this->assertSame('42', $obj->getAttribute('y2'));

        // should return same instance
        $this->assertSame($obj, $obj->setY2(42));
    }

    public function testRasterize()
    {
        $obj = new SVGLine(11, 12, 13, 14);

        $rast = $this->getMockBuilder('\SVG\Rasterization\SVGRasterizer')
            ->disableOriginalConstructor()
            ->getMock();

        // should call image renderer with correct options
        $rast->expects($this->once())->method('render')->with(
            $this->identicalTo('line'),
            $this->identicalTo(array(
                'x1' => '11',
                'y1' => '12',
                'x2' => '13',
                'y2' => '14',
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
