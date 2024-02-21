<?php
namespace SVG\Nodes\Texts;

use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'title'.
 */
class SVGTitle extends SVGNode
{
    const TAG_NAME = 'title';

    public function __construct($text = '')
    {
        parent::__construct();
        $this->setValue($text);
    }

    /**
     * Dummy implementation
     *
     * @param SVGRasterizer $rasterizer
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        // nothing to rasterize
    }
}
