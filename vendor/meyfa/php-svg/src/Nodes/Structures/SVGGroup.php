<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNodeContainer;

/**
 * Represents the SVG tag 'g'.
 */
class SVGGroup extends SVGNodeContainer
{
    const TAG_NAME = 'g';

    public function __construct()
    {
        parent::__construct();
    }
}
