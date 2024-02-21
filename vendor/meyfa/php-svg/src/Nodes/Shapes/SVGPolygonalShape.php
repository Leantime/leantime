<?php

namespace SVG\Nodes\Shapes;

use SVG\Nodes\SVGNode;

/**
 * This is the base class for polygons and polylines.
 * Offers methods for manipulating the list of points.
 */
abstract class SVGPolygonalShape extends SVGNode
{
    /** @var array[] $points List of points (float 2-tuples). */
    private $points;

    /**
     * @param array[] $points Array of points (float 2-tuples).
     */
    public function __construct($points)
    {
        parent::__construct();

        $this->points = $points;
    }

    public static function constructFromAttributes($attrs)
    {
        $points = array();

        if (isset($attrs['points'])) {
            $coords = preg_split('/[\s,]+/', trim($attrs['points']));
            for ($i = 0, $n = count($coords); $i < $n; $i += 2) {
                $points[] = array(
                    floatval($coords[$i]),
                    floatval($coords[$i + 1]),
                );
            }
        }

        return new static($points);
    }

    /**
     * Appends a new point to the end of this shape. The point can be given
     * either as a 2-tuple (1 param) or as separate x and y (2 params).
     *
     * @param float|float[] $a The point as an array, or its x coordinate.
     * @param float|null    $b The point's y coordinate, if not given as array.
     *
     * @return $this This node instance, for call chaining.
     */
    public function addPoint($a, $b = null)
    {
        if (!is_array($a)) {
            $a = array($a, $b);
        }

        $this->points[] = $a;
        return $this;
    }

    /**
     * Removes the point at the given index from this shape.
     *
     * @param int $index The index of the point to remove.
     *
     * @return $this This node instance, for call chaining.
     */
    public function removePoint($index)
    {
        array_splice($this->points, $index, 1);
        return $this;
    }

    /**
     * @return int The number of points in this shape.
     */
    public function countPoints()
    {
        return count($this->points);
    }

    /**
     * @return array[] All points in this shape (array of float 2-tuples).
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param int $index The index of the point to get.
     *
     * @return float[] The point at the given index (0 => x, 1 => y).
     */
    public function getPoint($index)
    {
        return $this->points[$index];
    }

    /**
     * Replaces the point at the given index with a different one.
     *
     * @param int     $index The index of the point to set.
     * @param float[] $point The new point.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setPoint($index, $point)
    {
        $this->points[$index] = $point;
        return $this;
    }

    public function getSerializableAttributes()
    {
        $attrs = parent::getSerializableAttributes();

        $points = '';
        for ($i = 0, $n = count($this->points); $i < $n; ++$i) {
            $point = $this->points[$i];
            if ($i > 0) {
                $points .= ' ';
            }
            $points .= $point[0].','.$point[1];
        }
        $attrs['points'] = $points;

        return $attrs;
    }
}
