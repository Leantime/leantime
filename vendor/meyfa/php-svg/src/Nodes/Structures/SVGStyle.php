<?php

namespace SVG\Nodes\Structures;

use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'style'.
 * Has the attribute 'type' and the CSS content.
 */
class SVGStyle extends SVGNode
{
    const TAG_NAME = 'style';

    private $css = '';

    /**
     * @param string $css The CSS data rules.
     * @param string $type The style type attribute.
     */
    public function __construct($css = '', $type = 'text/css')
    {
        parent::__construct();

        $this->css = $css;
        $this->setAttribute('type', $type);
    }

    public static function constructFromAttributes($attr)
    {
        $cdata = trim(preg_replace('/^\s*\/\/<!\[CDATA\[([\s\S]*)\/\/\]\]>\s*\z/', '$1', $attr));

        return new static($cdata);
    }

    /**
     * @return string The type attribute.
     */
    public function getType()
    {
        return $this->getAttribute('type');
    }

    /**
     * @param $type string The type attribute.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setType($type)
    {
        return $this->setAttribute('type', $type);
    }

    /**
     * @return string The CSS content.
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * Sets the CSS content.
     *
     * @param $css string The new cdata content
     *
     * @return $this The node instance for call chaining
     */
    public function setCss($css)
    {
        $this->css = $css;

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param SVGRasterizer $rasterizer
     */
    public function rasterize(SVGRasterizer $rasterizer)
    {
        // Nothing to rasterize.
        // All properties passed through container's global styles.
    }
}
