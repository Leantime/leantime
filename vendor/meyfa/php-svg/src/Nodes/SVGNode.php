<?php

namespace SVG\Nodes;

use SVG\Rasterization\SVGRasterizer;

/**
 * Represents a single element inside an SVG image (in other words, an XML tag).
 * It stores hierarchy info, as well as attributes and styles.
 */
abstract class SVGNode
{
    /** @var SVGNodeContainer $parent The parent node. */
    protected $parent;
    /** @var string[] $styles This node's set of explicit style declarations. */
    protected $styles;
    /** @var string[] $attributes This node's set of attributes. */
    protected $attributes;
    /** @var string $value This node's value */
    protected $value;

    public function __construct()
    {
        $this->styles     = array();
        $this->attributes = array();
        $this->value      = '';
    }

    /**
     * Factory function for this class, which accepts an associative array of
     * strings instead of parameters in the correct order (like `__construct`).
     *
     * By default, simply invokes the constructor with no arguments. Subclasses
     * may choose to override this if they require special behavior.
     *
     * @param string[] $attrs The attribute array (or array-like object; e.g. \SimpleXMLElement).
     *
     * @return static A new instance of the class this was called on.
     *
     * @SuppressWarnings("unused")
     */
    public static function constructFromAttributes($attrs)
    {
        return new static();
    }

    /**
     * @return string This node's tag name (e.g. 'rect' or 'g').
     */
    public function getName()
    {
        return static::TAG_NAME;
    }

    /**
     * @return SVGNodeContainer|null This node's parent node, if not root.
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Obtains the value on this node.
     *
     * @return string The node's value
     */
    public function getValue()
    {
        return isset($this->value) ? $this->value : '';
    }

    /**
     * Defines the value on this node.
     *
     * @param string $value The new node's value.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setValue($value)
    {
        if (!isset($value)) {
            unset($this->value);
            return $this;
        }
        $this->value = (string) $value;
        return $this;
    }

    /**
     * Obtains the style with the given name as specified on this node.
     *
     * @param string $name The name of the style to get.
     *
     * @return string|null The style value if specified on this node, else null.
     */
    public function getStyle($name)
    {
        return isset($this->styles[$name]) ? $this->styles[$name] : null;
    }

    /**
     * Defines a style on this node. A value of null or the empty string will
     * unset the property.
     *
     * @param string      $name  The name of the style to set.
     * @param string|null $value The new style value.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setStyle($name, $value)
    {
        $value = (string) $value;
        if (strlen($value) === 0) {
            unset($this->styles[$name]);
            return $this;
        }
        $this->styles[$name] = $value;
        return $this;
    }

    /**
     * Removes a style from this node's set of styles.
     *
     * @param string $name The name of the style to remove.
     *
     * @return $this This node instance, for call chaining.
     */
    public function removeStyle($name)
    {
        unset($this->styles[$name]);
        return $this;
    }

    /**
     * Obtains the computed style with the given name. The 'computed style' is
     * the one in effect; taking inheritance and default styles into account.
     *
     * @param string $name The name of the style to compute.
     *
     * @return string|null The style value if specified anywhere, else null.
     */
    public function getComputedStyle($name)
    {
        $style = $this->getStyle($name);

        // If no immediate style then get style from container/global style rules
        if ($style === null && isset($this->parent)) {
            $containerStyles = $this->parent->getContainerStyleForNode($this);
            $style = isset($containerStyles[$name]) ? $containerStyles[$name] : null;
        }

        // If still no style then get parent's style
        if (($style === null || $style === 'inherit') && isset($this->parent)) {
            return $this->parent->getComputedStyle($name);
        }

        // 'inherit' is not what we want. Either get the real style, or
        // nothing at all.
        return $style !== 'inherit' ? $style : null;
    }

    /**
     * Obtains the attribute with the given name as specified on this node.
     * For style attributes, use `getStyle($name)` instead.
     *
     * @param string $name The name of the attribute to get.
     *
     * @return string|null The attribute's value, or null.
     */
    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * Defines an attribute on this node. A value of null will unset the
     * attribute. Note that the empty string is perfectly valid.
     *
     * @param string      $name  The name of the attribute to set.
     * @param string|null $value The new attribute value.
     *
     * @return $this This node instance, for call chaining.
     */
    public function setAttribute($name, $value)
    {
        if (!isset($value)) {
            unset($this->attributes[$name]);
            return $this;
        }
        $this->attributes[$name] = (string) $value;
        return $this;
    }

    /**
     * Removes an attribute from this node's set of attributes.
     *
     * @param string $name The name of the attribute to remove.
     *
     * @return $this This node instance, for call chaining.
     */
    public function removeAttribute($name)
    {
        unset($this->attributes[$name]);
        return $this;
    }

    /**
     * Constructs a set of attributes that shall be included in generated XML.
     *
     * Subclasses MUST override this and include their own properties, if they
     * don't already use SVGNode's attribute set for storing them.
     *
     * @return string[] The set of attributes to include in generated XML.
     */
    public function getSerializableAttributes()
    {
        return $this->attributes;
    }

    /**
     * Constructs a set of styles that shall be included in generated XML.
     *
     * Subclasses MAY override this to augment or limit the styles returned
     * (in the case of SVG default values, for example).
     *
     * @return string[] The set of styles to include in generated XML.
     */
    public function getSerializableStyles()
    {
        return $this->styles;
    }

    /**
     * Constructs a regex pattern to use as the key to retrieve styles for this
     * node from its container.
     *
     * @return string|null The generated pattern.
     */
    public function getIdAndClassPattern()
    {
        $id = $this->getAttribute('id');
        $class = $this->getAttribute('class');

        $pattern = '';
        if (!empty($id)) {
            $pattern = '#'.$id.'|#'.$id;
        }
        if (!empty($class)) {
            if (!empty($pattern)) {
                $pattern .= '.'.$class.'|';
            }
            $pattern .= '.'.$class;
        }

        return empty($pattern) ? null : '/('.$pattern.')/';
    }

    /**
     * Returns the viewBox array (x, y, width, height) for the current node,
     * if one exists.
     *
     * @return float[]|null The viewbox array.
     */
    public function getViewBox()
    {
        $attr = $this->getAttribute('viewBox');
        if (empty($attr)) {
            return null;
        }

        $result = preg_split('/[\s,]+/', $attr);
        if (count($result) !== 4) {
            return null;
        }

        return array_map('floatval', $result);
    }

    /**
     * Draws this node to the given rasterizer.
     *
     * @param SVGRasterizer $rasterizer The rasterizer to draw to.
     *
     * @return void
     */
    abstract public function rasterize(SVGRasterizer $rasterizer);

    /**
     * Returns all descendants of this node (excluding this node) having the
     * given tag name. '*' matches all nodes.
     *
     * Example: getElementsByTagName('rect')
     * would return all <rect /> nodes that are descendants of this node.
     *
     * @param string $tagName The tag name to search for ('*' to match all).
     * @param SVGNode[] $result The array to fill. Can be omitted.
     *
     * @return SVGNode[] An array of matching elements.
     *
     * @SuppressWarnings("unused")
     */
    public function getElementsByTagName($tagName, array &$result = array())
    {
        return $result;
    }

    /**
     * Returns all descendants of this node (excluding this node) having the
     * given class name (or names).
     *
     * Example 1: getElementsByClassName('foo')
     * would return all nodes whose class attribute contains the item 'foo'
     * (e.g. class="foo", class="a b foo bar", etc)
     *
     * Example 2: getElementsByClassName('foo bar')
     * or alternatively: getElementsByClassName(array('foo', 'bar'))
     * would return all nodes whose class attribute contains both items
     * 'foo' and 'bar'
     * (e.g. class="a b foo qux bar", but not class="foo")
     *
     * @param string|string[] $className The class name or names to search for.
     * @param SVGNode[] $result The array to fill. Can be omitted.
     *
     * @return SVGNode[] An array of matching elements.
     *
     * @SuppressWarnings("unused")
     */
    public function getElementsByClassName($className, array &$result = array())
    {
        return $result;
    }
}
