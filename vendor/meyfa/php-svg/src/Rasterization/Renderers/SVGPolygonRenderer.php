<?php

namespace SVG\Rasterization\Renderers;

use SVG\Rasterization\SVGRasterizer;

/**
 * This renderer can draw polygons and polylines.
 * The points are provided as arrays with 2 entries: 0 => x coord, 1 => y coord.
 *
 * Options:
 * - bool open: if true, leaves first and last point disconnected (-> polyline)
 * - array[] points: array of coordinate tuples (i.e., array of array of float)
 */
class SVGPolygonRenderer extends SVGRenderer
{
    protected function prepareRenderParams(SVGRasterizer $rasterizer, array $options)
    {
        $scaleX = $rasterizer->getScaleX();
        $scaleY = $rasterizer->getScaleY();

        $offsetX = $rasterizer->getOffsetX();
        $offsetY = $rasterizer->getOffsetY();

        $points = array();
        foreach ($options['points'] as $point) {
            $points[] = $point[0] * $scaleX + $offsetX;
            $points[] = $point[1] * $scaleY + $offsetY;
        }

        return array(
            'open'      => isset($options['open']) ? $options['open'] : false,
            'points'    => $points,
            'numpoints' => count($options['points']),
        );
    }

    protected function renderFill($image, array $params, $color)
    {
        if ($params['numpoints'] < 3) {
            return;
        }

        // somehow imagesetthickness() affects the polygon drawing. reset to 0.
        imagesetthickness($image, 0);
        imagefilledpolygon($image, $params['points'], $params['numpoints'], $color);
    }

    protected function renderStroke($image, array $params, $color, $strokeWidth)
    {
        imagesetthickness($image, $strokeWidth);

        if ($params['open']) {
            $this->renderStrokeOpen($image, $params['points'], $color);
            return;
        }

        imagepolygon($image, $params['points'], $params['numpoints'], $color);
    }

    private function renderStrokeOpen($image, array $points, $color)
    {
        $px = $points[0];
        $py = $points[1];

        for ($i = 2, $n = count($points); $i < $n; $i += 2) {
            $x = $points[$i];
            $y = $points[$i + 1];
            imageline($image, $px, $py, $x, $y, $color);
            $px = $x;
            $py = $y;
        }
    }
}
