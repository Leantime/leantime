<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;

/**
 * Represents the SVG tag 'a'.
 */
class SVGLinkGroup extends SVGNodeContainer
{
    const TAG_NAME = 'a';

    public function __construct()
    {
        parent::__construct();
    }
}
