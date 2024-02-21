<?php

namespace SVG;

use SVG\Nodes\Structures\SVGStyle;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGStyleTest extends \PHPUnit\Framework\TestCase
{
    public function testSetType()
    {
        $obj = new SVGStyle();

        $type = 'type_attribute';
        $this->assertInstanceOf('SVG\Nodes\Structures\SVGStyle',
            $obj->setType($type));

        $this->assertEquals($type, $obj->getAttribute('type'));
    }

    public function testGetType()
    {
        $obj = new SVGStyle();

        $type = 'type_attribute';
        $obj->setAttribute('type', $type);

        $this->assertSame($type, $obj->getType());
    }

    public function testSetCss()
    {
        $obj = new SVGStyle();

        $this->assertInstanceOf('SVG\Nodes\Structures\SVGStyle',
            $obj->setCss('svg {background-color: beige;}'));
    }

    public function testGetCss()
    {
        $obj = new SVGStyle();

        $css = 'svg {background-color: beige;}';
        $obj->setCss($css);

        $this->assertSame($css, $obj->getCss());
    }
}
