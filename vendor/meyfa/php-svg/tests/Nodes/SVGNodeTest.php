<?php

namespace SVG;

use SVG\Nodes\SVGNode;

class SVGNodeSubclass extends SVGNode
{
    const TAG_NAME = 'test_subclass';

    /**
     * @SuppressWarnings("unused")
     */
    public function rasterize(\SVG\Rasterization\SVGRasterizer $rasterizer)
    {
    }
}

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGNodeTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructFromAttributes()
    {
        $obj = SVGNodeSubclass::constructFromAttributes(array());

        // should construct child class
        $this->assertInstanceOf('SVG\SVGNodeSubclass', $obj);
    }

    public function testGetName()
    {
        $obj = new SVGNodeSubclass();

        // should return child class const
        $this->assertSame(SVGNodeSubclass::TAG_NAME, $obj->getName());
    }

    public function testGetParent()
    {
        $obj = new SVGNodeSubclass();

        // should return null when parentless
        $this->assertNull($obj->getParent());
    }

    public function testGetValue()
    {
        $obj = new SVGNodeSubclass();

        // should return empty string
        $this->assertSame('', $obj->getValue());
    }

    public function testSetValue()
    {
        $obj = new SVGNodeSubclass();

        // should update value
        $obj->setValue('hello world');
        $this->assertSame('hello world', $obj->getValue());

        // should treat null like empty string
        $obj->setValue(null);
        $this->assertSame('', $obj->getValue());

        // should return same instance
        $this->assertSame($obj, $obj->setValue('foo'));
    }

    public function testGetStyle()
    {
        $obj = new SVGNodeSubclass();

        // should return null for undefined properties
        $this->assertNull($obj->getStyle('fill'));
    }

    public function testSetStyle()
    {
        $obj = new SVGNodeSubclass();

        // should set properties
        $obj->setStyle('fill', '#FFFFFF');
        $this->assertSame('#FFFFFF', $obj->getStyle('fill'));

        // should unset properties when given null
        $obj->setStyle('fill', null);
        $this->assertNull($obj->getStyle('fill'));

        // should unset properties when given ''
        $obj->setStyle('fill', '');
        $this->assertNull($obj->getStyle('fill'));

        // should convert value to a string
        $obj->setStyle('width', 42);
        $this->assertSame('42', $obj->getStyle('width'));

        // should not treat 0 as an empty value
        $obj->setStyle('width', 0);
        $this->assertSame('0', $obj->getStyle('width'));

        // should return same instance
        $this->assertSame($obj, $obj->setStyle('fill', '#FFF'));
        $this->assertSame($obj, $obj->setStyle('fill', null));
    }

    public function testRemoveStyle()
    {
        $obj = new SVGNodeSubclass();

        // should remove the property
        $obj->setStyle('fill', '#FFFFFF');
        $obj->removeStyle('fill');
        $this->assertNull($obj->getStyle('fill'));

        // should return same instance
        $this->assertSame($obj, $obj->removeStyle('fill'));
    }

    public function testGetAttribute()
    {
        $obj = new SVGNodeSubclass();

        // should return null for undefined properties
        $this->assertNull($obj->getAttribute('x'));
    }

    public function testSetAttribute()
    {
        $obj = new SVGNodeSubclass();

        // should set properties
        $obj->setAttribute('x', '100%');
        $this->assertSame('100%', $obj->getAttribute('x'));

        // should unset properties when given null
        $obj->setAttribute('x', null);
        $this->assertNull($obj->getAttribute('x'));

        // should not unset properties when given ''
        $obj->setAttribute('x', '');
        $this->assertSame('', $obj->getAttribute('x'));

        // should convert value to a string
        $obj->setAttribute('x', 42);
        $this->assertSame('42', $obj->getAttribute('x'));

        // should not treat 0 as an empty value
        $obj->setAttribute('x', 0);
        $this->assertSame('0', $obj->getAttribute('x'));

        // should return same instance
        $this->assertSame($obj, $obj->setAttribute('x', 42));
        $this->assertSame($obj, $obj->setAttribute('x', null));
    }

    public function testRemoveAttribute()
    {
        $obj = new SVGNodeSubclass();

        // should remove the property
        $obj->setAttribute('x', '100%');
        $obj->removeAttribute('x');
        $this->assertNull($obj->getAttribute('x'));

        // should return same instance
        $this->assertSame($obj, $obj->removeAttribute('x'));
    }

    public function testGetSerializableAttributes()
    {
        $obj = new SVGNodeSubclass();

        // should return previously defined properties
        $obj->setAttribute('x', 0);
        $obj->setAttribute('y', 0);
        $obj->setAttribute('width', '100%');
        $this->assertSame(array(
            'x' => '0',
            'y' => '0',
            'width' => '100%',
        ), $obj->getSerializableAttributes());
    }

    public function testGetSerializableStyles()
    {
        $obj = new SVGNodeSubclass();

        // should return previously defined properties
        $obj->setStyle('fill', '#FFFFFF');
        $obj->setStyle('width', 42);
        $this->assertSame(array(
            'fill' => '#FFFFFF',
            'width' => '42',
        ), $obj->getSerializableStyles());
    }

    public function testGetViewBox()
    {
        $obj = new SVGNodeSubclass();

        // should return null for missing viewBox
        $this->assertNull($obj->getViewBox());

        // should return null for ill-formed viewBox
        $obj->setAttribute('viewBox', 'foobar');
        $this->assertNull($obj->getViewBox());

        // should return float array for well-formed viewBox
        $obj->setAttribute('viewBox', '37, 42.25, 100 200');
        $this->assertSame(array(37.0, 42.25, 100.0, 200.0), $obj->getViewBox());
    }
}
