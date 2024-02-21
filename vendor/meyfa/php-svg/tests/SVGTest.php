<?php

namespace SVG;

use SVG\SVG;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGTest extends \PHPUnit\Framework\TestCase
{
    private $xml, $xmlNoDeclaration;

    public function setUp()
    {
        $this->xml  = '<?xml version="1.0" encoding="utf-8"?>';
        $this->xml .= '<svg width="37" height="42" '.
            'xmlns="http://www.w3.org/2000/svg" '.
            'xmlns:xlink="http://www.w3.org/1999/xlink">';
        $this->xml .= '</svg>';

        $this->xmlNoDeclaration  = '<svg width="37" height="42" '.
            'xmlns="http://www.w3.org/2000/svg" '.
            'xmlns:xlink="http://www.w3.org/1999/xlink">';
        $this->xmlNoDeclaration .= '</svg>';
    }

    public function testGetDocument()
    {
        $image = new SVG(37, 42);
        $doc = $image->getDocument();

        // should be instanceof the correct class
        $docFragClass = '\SVG\Nodes\Structures\SVGDocumentFragment';
        $this->assertInstanceOf($docFragClass, $doc);

        // should be set to root
        $this->assertTrue($doc->isRoot());

        // should have correct width and height
        $this->assertSame('37', $doc->getWidth());
        $this->assertSame('42', $doc->getHeight());
    }

    public function testToRasterImage()
    {
        $image = new SVG(37, 42);
        $rasterImage = $image->toRasterImage(100, 200);

        // should be a gd resource
        $this->assertTrue(is_resource($rasterImage));
        $this->assertSame('gd', get_resource_type($rasterImage));

        // should have correct width and height
        $this->assertSame(100, imagesx($rasterImage));
        $this->assertSame(200, imagesy($rasterImage));
    }

    public function test__toString()
    {
        $image = new SVG(37, 42);

        // should return correctly stringified XML
        $this->assertSame($this->xml, (string) $image);
    }

    public function testToXMLString()
    {
        $image = new SVG(37, 42);

        // should return correctly stringified XML
        $this->assertSame($this->xml, $image->toXMLString());

        // should respect standalone=false
        $this->assertSame($this->xmlNoDeclaration, $image->toXMLString(false));
    }

    public function testFromString()
    {
        $image = SVG::fromString($this->xml);
        $doc = $image->getDocument();

        // should return an instance of SVG
        $this->assertInstanceOf('\SVG\SVG', $image);

        // should have correct width and height
        $this->assertSame('37', $doc->getWidth());
        $this->assertSame('42', $doc->getHeight());

        // should succeed without xml declaration
        $image = SVG::fromString($this->xmlNoDeclaration);
        $doc = $image->getDocument();
        $this->assertInstanceOf('\SVG\SVG', $image);
        $this->assertSame('37', $doc->getWidth());
        $this->assertSame('42', $doc->getHeight());
    }

    public function testFromFile()
    {
        $image = SVG::fromFile(__DIR__.'/php_test.svg');

        $this->assertInstanceOf('\SVG\SVG', $image);
    }
}
