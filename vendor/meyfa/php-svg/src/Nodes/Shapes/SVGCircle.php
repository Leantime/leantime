<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'circle'.
 * Has the special attributes cx, cy, r.
 */
class SVGCircle extends SVGNode
{
    const TAG_NAME = 'circle';

    /**
     * @param string|null $cx The center's x coordinate.
     * @param string|null $cy The center's y coordinate.
     * @param string|null $r  The radius.
     */
    public function __construct($cx = null, $cy = null, $r = null)
    {
        parent::__construct();

        $this->setAttribute('cx', $cx);
        $this->setAttribute('cy', $cy);
        $this->setAttribute('r', $r);
    }

    /**
     * @return string The center's x coordinate.
     */
    public function getCenterX()
    {
        return $this->getAttribute('cx');
    }

    /**
     * Sets the center's x coordinate.
     *
     * @param string $cx The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setCenterX($cx)
    {
        return $this->setAttribute('cx', $cx);
    }

    /**
     * @return string The center's y coordinate.
     */
    public function getCenterY()
    {
        return $this->getAttribute('cy');
    }

    /**
     * Sets the center's y coordinate.
     *
     * @param string $cy The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setCenterY($cy)
    {
        return $this->setAttribute('cy', $cy);
    }

    /**
     * @return string The radius.
     */
    public function getRadius()
    {
        return $this->getAttribute('r');
    }

    /**
     * Sets the radius.
     *
     * @param string $r The new radius.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setRadius($r)
    {
        return $this->setAttribute('r', $r);
    }

    public function rasterize(SVGRasterizer $rasterizer)
    {
        if ($this->getComputedStyle('display') === 'none') {
            return;
        }

        $visibility = $this->getComputedStyle('visibility');
        if ($visibility === 'hidden' || $visibility === 'collapse') {
            return;
        }

        $r = $this->getRadius();
        $rasterizer->render('ellipse', array(
            'cx'    => $this->getCenterX(),
            'cy'    => $this->getCenterY(),
            'rx'    => $r,
            'ry'    => $r,
        ), $this);
    }
}
