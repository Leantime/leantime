<?php

namespace SVG\Rasterization;

use SVG\Nodes\SVGNode;
use SVG\Utilities\Units\Length;

/**
 * This class is the main entry point for the rasterization process.
 *
 * Each constructed instance represents one output image.
 * Rasterization happens by invoking `render()` with the id of a specific
 * renderer, e.g. 'ellipse' or 'polygon', which then performs the actual
 * drawing.
 * Note that renderers DO NOT correspond 1:1 to node types (e.g. there is no
 * renderer 'circle', but 'ellipse' with equal radiuses is used).
 *
 * @SuppressWarnings("coupling")
 */
class SVGRasterizer
{
    /** @var Renderers\SVGRenderer[] $renderers Map of shapes to renderers. */
    private static $renderers;
    /** @var Path\SVGPathParser The singleton path parser. */
    private static $pathParser;
    /** @var Path\SVGPathApproximator The singleton path approximator. */
    private static $pathApproximator;

    /**
     * @var int $docWidth  The original SVG document width, in pixels.
     * @var int $docHeight The original SVG document height, in pixels.
     */
    private $docWidth, $docHeight;
    /**
     * @var float[] The document's viewBox (x, y, w, h).
     */
    private $viewBox;
    /**
     * @var int $width  The output image width, in pixels.
     * @var int $height The output image height, in pixels.
     */
    private $width, $height;
    /** @var resource $outImage The output image as a GD resource. */
    private $outImage;

    /**
     * @param string $docWidth  The original SVG document width, as a string.
     * @param string $docHeight The original SVG document height, as a string.
     * @param float[] $viewBox  The document's viewBox.
     * @param int $width        The output image width, in pixels.
     * @param int $height       The output image height, in pixels.
     */
    public function __construct($docWidth, $docHeight, $viewBox, $width, $height)
    {
        $this->docWidth  = $docWidth;
        $this->docHeight = $docHeight;

        $this->viewBox = empty($viewBox) ? null : $viewBox;

        $this->width  = $width;
        $this->height = $height;

        $this->outImage = self::createImage($width, $height);

        self::createDependencies();
    }

    /**
     * Sets up a new truecolor GD image resource with the given dimensions.
     *
     * The returned image supports and is filled with transparency.
     *
     * @param int $width  The output image width, in pixels.
     * @param int $height The output image height, in pixels.
     *
     * @return resource The created GD image resource.
     */
    private static function createImage($width, $height)
    {
        $img = imagecreatetruecolor($width, $height);

        imagealphablending($img, true);
        imagesavealpha($img, true);

        imagefill($img, 0, 0, 0x7F000000);

        return $img;
    }

    /**
     * Makes sure the singleton static variables are all instantiated.
     *
     * This includes registering all of the standard renderers, as well as
     * preparing the path parser and the path approximator.
     *
     * @return void
     */
    private static function createDependencies()
    {
        if (isset(self::$renderers)) {
            return;
        }

        self::$renderers = array(
            'rect'      => new Renderers\SVGRectRenderer(),
            'line'      => new Renderers\SVGLineRenderer(),
            'ellipse'   => new Renderers\SVGEllipseRenderer(),
            'polygon'   => new Renderers\SVGPolygonRenderer(),
            'image'     => new Renderers\SVGImageRenderer(),
            'text'      => new Renderers\SVGTextRenderer(),
        );

        self::$pathParser       = new Path\SVGPathParser();
        self::$pathApproximator = new Path\SVGPathApproximator();
    }

    /**
     * Finds the renderer registered with the given id.
     *
     * @param string $id The id of a registered renderer instance.
     *
     * @return Renderers\SVGRenderer The requested renderer.
     * @throws \InvalidArgumentException If no such renderer exists.
     */
    private static function getRenderer($id)
    {
        if (!isset(self::$renderers[$id])) {
            throw new \InvalidArgumentException("no such renderer: ".$id);
        }
        return self::$renderers[$id];
    }

    /**
     * @return Path\SVGPathParser The path parser used by this instance.
     */
    // implementation note: although $pathParser is static, this method isn't,
    // to encourage access via passed instances (better for testing etc)
    public function getPathParser()
    {
        return self::$pathParser;
    }

    /**
     * @return Path\SVGPathApproximator The approximator used by this instance.
     */
    // implementation note: (see 'getPathParser()')
    public function getPathApproximator()
    {
        return self::$pathApproximator;
    }

    /**
     * Uses the specified renderer to draw an object, as described via the
     * params attribute, and by utilizing the provided node context.
     *
     * The node is required for access to things like the opacity as well as
     * stroke/fill attributes etc.
     *
     * @param string  $rendererId The id of the renderer to use.
     * @param mixed[] $params     An array of options to pass to the renderer.
     * @param SVGNode $context    The SVGNode that serves as drawing context.
     *
     * @return mixed Whatever the renderer returned (in most cases void).
     * @throws \InvalidArgumentException If no such renderer exists.
     */
    public function render($rendererId, array $params, SVGNode $context)
    {
        $renderer = self::getRenderer($rendererId);
        return $renderer->render($this, $params, $context);
    }

    /**
     * @return float The original SVG document width, in pixels.
     */
    public function getDocumentWidth()
    {
        return Length::convert($this->docWidth ?: '100%', $this->width);
    }

    /**
     * @return float The original SVG document height, in pixels.
     */
    public function getDocumentHeight()
    {
        return Length::convert($this->docHeight ?: '100%', $this->height);
    }

    /**
     * @return int The output image width, in pixels.
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return int The output image height, in pixels.
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return float The factor by which the output is scaled on the x axis.
     */
    public function getScaleX()
    {
        if (!empty($this->viewBox)) {
            return $this->width / $this->viewBox[2];
        }
        return $this->width / $this->getDocumentWidth();
    }

    /**
     * @return float The factor by which the output is scaled on the y axis.
     */
    public function getScaleY()
    {
        if (!empty($this->viewBox)) {
            return $this->height / $this->viewBox[3];
        }

        return $this->height / $this->getDocumentHeight();
    }

    /**
     * @return float The amount by which renderers must offset their drawings
     *               on the x-axis (not to be scaled).
     */
    public function getOffsetX()
    {
        if (!empty($this->viewBox)) {
            $scale = $this->getScaleX();
            return -($this->viewBox[0] * $scale);
        }
        return 0;
    }

    /**
     * @return float The amount by which renderers must offset their drawings
     *               on the y-axis (not to be scaled).
     */
    public function getOffsetY()
    {
        if (!empty($this->viewBox)) {
            $scale = $this->getScaleY();
            return -($this->viewBox[1] * $scale);
        }
        return 0;
    }

    /**
     * @return float[]|null The document's viewBox.
     */
    public function getViewBox()
    {
        return $this->viewBox;
    }

    /**
     * Applies final processing steps to the output image. It is then returned.
     *
     * @return resource The GD image resource this rasterizer is operating on.
     */
    public function finish()
    {
        return $this->outImage;
    }

    /**
     * @return resource The GD image resource this rasterizer is operating on.
     */
    public function getImage()
    {
        return $this->outImage;
    }
}
