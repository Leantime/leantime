<?php

namespace SVG;

use SVG\Rasterization\Renderers\SVGImageRenderer;
use SVG\Nodes\SVGNode;

class SVGNodeClass extends SVGNode
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
class SVGImageRendererTest extends \PHPUnit\Framework\TestCase
{
    public function testRender()
    {
        $rast = new \SVG\Rasterization\SVGRasterizer(10, 20, null, 100, 200);
        $options = array(
            'href'   => __DIR__.'/../../sample.svg',
            'x'      => 10.5,
            'y'      => 10.5,
            'width'  => 100.5,
            'height' => 100.5
        );
        $svgImageRender = new SVGImageRenderer();
        $context = new SVGNodeClass();

        $this->assertNull($svgImageRender->render($rast, $options, $context));
    }
}
