<?php

namespace SVG\Nodes\Embedded;

use SVG\Nodes\SVGNode;
use SVG\Rasterization\SVGRasterizer;

/**
 * Represents the SVG tag 'image'.
 * Has the special attributes xlink:href, x, y, width, height.
 */
class SVGImage extends SVGNode
{
    const TAG_NAME = 'image';

    /**
     * @param string|null $href   The image path, URL or URI.
     * @param string|null $x      The x coordinate of the upper left corner.
     * @param string|null $y      The y coordinate of the upper left corner.
     * @param string|null $width  The width.
     * @param string|null $height The height.
     */
    public function __construct($href = null, $x = null, $y = null, $width = null, $height = null)
    {
        parent::__construct();

        $this->setAttribute('xlink:href', $href);
        $this->setAttribute('x', $x);
        $this->setAttribute('y', $y);
        $this->setAttribute('width', $width);
        $this->setAttribute('height', $height);
    }

    /**
     * Creates a new SVGImage directly from file
     *
     * @param string     $path
     * @param string     $mimeType
     * @param float|null $x
     * @param float|null $y
     * @param float|null $width
     * @param float|null $height
     *
     * @return self
     */
    public static function fromFile($path, $mimeType, $x = null, $y = null, $width = null, $height = null)
    {
        $imageContent = file_get_contents($path);
        if ($imageContent === false) {
            throw new \RuntimeException('Image file "' . $path . '" could not be read.');
        }

        return self::fromString(
            $imageContent,
            $mimeType,
            $x,
            $y,
            $width,
            $height
        );
    }

    /**
     * Creates a new SVGImage directly from a raw binary image string
     *
     * @param string     $imageContent
     * @param string     $mimeType
     * @param float|null $x
     * @param float|null $y
     * @param float|null $width
     * @param float|null $height
     *
     * @return self
     */
    public static function fromString(
        $imageContent,
        $mimeType,
        $x = null,
        $y = null,
        $width = null,
        $height = null
    ) {
        return new self(
            sprintf(
                'data:%s;base64,%s',
                $mimeType,
                base64_encode($imageContent)
            ),
            $x,
            $y,
            $width,
            $height
        );
    }

    /**
     * @return string The image path, URL or URI.
     */
    public function getHref()
    {
        return $this->getAttribute('xlink:href') ?: $this->getAttribute('href');
    }

    /**
     * Sets this image's path, URL or URI.
     *
     * @param string $href The new image hyperreference.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setHref($href)
    {
        return $this->setAttribute('xlink:href', $href);
    }

    /**
     * @return string The x coordinate of the upper left corner.
     */
    public function getX()
    {
        return $this->getAttribute('x');
    }

    /**
     * Sets the x coordinate of the upper left corner.
     *
     * @param string $x The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setX($x)
    {
        return $this->setAttribute('x', $x);
    }

    /**
     * @return string The y coordinate of the upper left corner.
     */
    public function getY()
    {
        return $this->getAttribute('y');
    }

    /**
     * Sets the y coordinate of the upper left corner.
     *
     * @param string $y The new coordinate.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setY($y)
    {
        return $this->setAttribute('y', $y);
    }

    /**
     * @return string The width.
     */
    public function getWidth()
    {
        return $this->getAttribute('width');
    }

    /**
     * @param string $width The new width.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setWidth($width)
    {
        return $this->setAttribute('width', $width);
    }

    /**
     * @return string The height.
     */
    public function getHeight()
    {
        return $this->getAttribute('height');
    }

    /**
     * @param string $height The new height.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setHeight($height)
    {
        return $this->setAttribute('height', $height);
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

        $rasterizer->render('image', array(
            'href'      => $this->getHref(),
            'x'         => $this->getX(),
            'y'         => $this->getY(),
            'width'     => $this->getWidth(),
            'height'    => $this->getHeight(),
        ), $this);
    }
}
