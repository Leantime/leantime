<?php

namespace SVG;

use AssertGD\GDSimilarityConstraint;

use SVG\Rasterization\SVGRasterizer;
use SVG\Rasterization\Renderers\SVGRectRenderer;

/**
 * @SuppressWarnings(PHPMD)
 */
class SVGRectRendererTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldRenderStroke()
    {
        $obj = new SVGRectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', 'none');
        $context->setStyle('stroke', '#FF0000');
        $context->setStyle('stroke-width', '1px');

        $rasterizer = new SVGRasterizer('40px', '40px', null, 40, 40);
        $obj->render($rasterizer, array(
            'x' => '4px', 'y' => '8px',
            'width' => '20px', 'height' => '16px',
        ), $context);
        $img = $rasterizer->finish();

        $this->assertThat($img,
            new GDSimilarityConstraint('./tests/images/renderer-rect-stroke.png'));
    }

    public function testShouldRenderStrokeThick()
    {
        $obj = new SVGRectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', 'none');
        $context->setStyle('stroke', '#FF0000');
        $context->setStyle('stroke-width', '3px');

        $rasterizer = new SVGRasterizer('20px', '20px', null, 40, 40);
        $obj->render($rasterizer, array(
            'x' => '2px', 'y' => '4px',
            'width' => '10px', 'height' => '8px',
        ), $context);
        $img = $rasterizer->finish();

        $this->assertThat($img,
            new GDSimilarityConstraint('./tests/images/renderer-rect-stroke-thick.png'));
    }

    public function testShouldRenderStrokeAlpha()
    {
        $obj = new SVGRectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', 'none');
        $context->setStyle('stroke', 'rgba(255, 0, 0, 0.5)');
        $context->setStyle('stroke-width', '3px');

        $rasterizer = new SVGRasterizer('20px', '20px', null, 40, 40);
        $obj->render($rasterizer, array(
            'x' => '2px', 'y' => '4px',
            'width' => '10px', 'height' => '8px',
        ), $context);
        $img = $rasterizer->finish();

        $this->assertThat($img,
            new GDSimilarityConstraint('./tests/images/renderer-rect-stroke-alpha.png'));
    }

    public function testShouldRenderFill()
    {
        $obj = new SVGRectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', '#FF0000');
        $context->setStyle('stroke', 'none');

        $rasterizer = new SVGRasterizer('20px', '20px', null, 40, 40);
        $obj->render($rasterizer, array(
            'x' => '2px', 'y' => '4px',
            'width' => '10px', 'height' => '8px',
        ), $context);
        $img = $rasterizer->finish();

        $this->assertThat($img,
            new GDSimilarityConstraint('./tests/images/renderer-rect-fill.png'));
    }

    public function testShouldRenderFillAlpha()
    {
        $obj = new SVGRectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', 'rgba(255, 0, 0, 0.5)');
        $context->setStyle('stroke', 'none');

        $rasterizer = new SVGRasterizer('20px', '20px', null, 40, 40);
        $obj->render($rasterizer, array(
            'x' => '2px', 'y' => '4px',
            'width' => '10px', 'height' => '8px',
        ), $context);
        $img = $rasterizer->finish();

        $this->assertThat($img,
            new GDSimilarityConstraint('./tests/images/renderer-rect-fill-alpha.png'));
    }

    public function testShouldRenderStrokeAndFill()
    {
        $obj = new SVGRectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', 'rgba(255, 255, 255, 0.5)');
        $context->setStyle('stroke', 'rgba(0, 0, 0, 0.5)');
        $context->setStyle('stroke-width', '5px');

        $rasterizer = new SVGRasterizer('40px', '40px', null, 40, 40);
        $obj->render($rasterizer, array(
            'x' => '4px', 'y' => '8px',
            'width' => '20px', 'height' => '16px',
        ), $context);
        $img = $rasterizer->finish();

        $this->assertThat($img,
            new GDSimilarityConstraint('./tests/images/renderer-rect-stroke-fill.png'));
    }

    public function testShouldRenderStrokeRounded()
    {
        $obj = new SVGRectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', 'none');
        $context->setStyle('stroke', '#FF0000');
        $context->setStyle('stroke-width', '1px');

        $rasterizer = new SVGRasterizer('40px', '40px', null, 40, 40);
        $obj->render($rasterizer, array(
            'x' => '4px', 'y' => '8px',
            'width' => '20px', 'height' => '16px',
            'rx' => '10%', 'ry' => '10%',
        ), $context);
        $img = $rasterizer->finish();

        $this->assertThat($img,
            new GDSimilarityConstraint('./tests/images/renderer-rect-stroke-rounded.png'));
    }

    public function testShouldRenderFillRounded()
    {
        $obj = new SVGRectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', '#FF0000');
        $context->setStyle('stroke', 'none');

        $rasterizer = new SVGRasterizer('40px', '40px', null, 40, 40);
        $obj->render($rasterizer, array(
            'x' => '4px', 'y' => '8px',
            'width' => '20px', 'height' => '16px',
            'rx' => '10%', 'ry' => '10%',
        ), $context);
        $img = $rasterizer->finish();

        $this->assertThat($img,
            new GDSimilarityConstraint('./tests/images/renderer-rect-fill-rounded.png'));
    }

    public function testDoesNotRenderIfWidthZero()
    {
        $obj = new SVGRectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', '#FF0000');
        $context->setStyle('stroke', '#0000FF');
        $context->setStyle('stroke-width', '1px');

        $rasterizer = new SVGRasterizer('40px', '40px', null, 40, 40);
        $obj->render($rasterizer, array(
            'x' => '4px', 'y' => '8px',
            'width' => '0', 'height' => '16px',
            'rx' => '10%', 'ry' => '10%',
        ), $context);
        $img = $rasterizer->finish();

        $this->assertThat($img,
            new GDSimilarityConstraint('./tests/images/renderer-rect-empty.png'));
    }

    public function testDoesNotRenderIfHeightZero()
    {
        $obj = new SVGRectRenderer();

        $context = $this->getMockForAbstractClass('\SVG\Nodes\SVGNode');
        $context->setStyle('fill', '#FF0000');
        $context->setStyle('stroke', '#0000FF');
        $context->setStyle('stroke-width', '1px');

        $rasterizer = new SVGRasterizer('40px', '40px', null, 40, 40);
        $obj->render($rasterizer, array(
            'x' => '4px', 'y' => '8px',
            'width' => '20px', 'height' => '0',
            'rx' => '10%', 'ry' => '10%',
        ), $context);
        $img = $rasterizer->finish();

        $this->assertThat($img,
            new GDSimilarityConstraint('./tests/images/renderer-rect-empty.png'));
    }
}
