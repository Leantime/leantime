<?php

namespace SVG\Nodes;

use SVG\Nodes\SVGNodeContainer;
use SVG\Rasterization\SVGRasterizer;

/**
 * NOT INTENDED FOR USER ACCESS. This is the class that gets instantiated for
 * unknown nodes in input SVG.
 */
class SVGGenericNodeType extends SVGNodeContainer
{
    private $tagName;

    public function __construct($tagName)
    {
        parent::__construct();
        $this->tagName = $tagName;
    }

    public function getName()
    {
        return $this->tagName;
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        // do nothing
    }
}
