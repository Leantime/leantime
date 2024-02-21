<?php

namespace SVG\Reading;

use SVG\SVG;
use SVG\Nodes\SVGNode;
use SVG\Nodes\SVGNodeContainer;
use SVG\Nodes\SVGGenericNodeType;
use SVG\Utilities\SVGStyleParser;

/**
 * This class is used to read XML strings or files and turn them into instances
 * of SVG by parsing the document tree.
 *
 * In contrast to SVGWriter, a single instance can perform any number of reads.
 */
class SVGReader
{
    /**
    * @var string[] $nodeTypes Map of tag names to fully-qualified class names.
    */
    private static $nodeTypes = array(
        'svg'       => 'SVG\Nodes\Structures\SVGDocumentFragment',
        'g'         => 'SVG\Nodes\Structures\SVGGroup',
        'defs'      => 'SVG\Nodes\Structures\SVGDefs',
        'style'     => 'SVG\Nodes\Structures\SVGStyle',
        'rect'      => 'SVG\Nodes\Shapes\SVGRect',
        'circle'    => 'SVG\Nodes\Shapes\SVGCircle',
        'ellipse'   => 'SVG\Nodes\Shapes\SVGEllipse',
        'line'      => 'SVG\Nodes\Shapes\SVGLine',
        'polygon'   => 'SVG\Nodes\Shapes\SVGPolygon',
        'polyline'  => 'SVG\Nodes\Shapes\SVGPolyline',
        'path'      => 'SVG\Nodes\Shapes\SVGPath',
        'image'     => 'SVG\Nodes\Embedded\SVGImage',
        'text'      => 'SVG\Nodes\Texts\SVGText',
        'textPath'  => 'SVG\Nodes\Texts\SVGTextPath',
        'title'     => 'SVG\Nodes\Texts\SVGTitle',
    );
    /**
     * @var string[] @styleAttributes Attributes to be interpreted as styles.
     * List comes from https://www.w3.org/TR/SVG/styling.html.
     */
    private static $styleAttributes = array(
        // DEFINED IN BOTH CSS2 AND SVG
        // font properties
        'font', 'font-family', 'font-size', 'font-size-adjust', 'font-stretch',
        'font-style', 'font-variant', 'font-weight',
        // text properties
        'direction', 'letter-spacing', 'word-spacing', 'text-decoration',
        'unicode-bidi',
        // other properties for visual media
        'clip', 'color', 'cursor', 'display', 'overflow', 'visibility',
        // NOT DEFINED IN CSS2
        // clipping, masking and compositing properties
        'clip-path', 'clip-rule', 'mask', 'opacity',
        // filter effects properties
        'enable-background', 'filter', 'flood-color', 'flood-opacity',
        'lighting-color',
        // gradient properties
        'stop-color', 'stop-opacity',
        // interactivity properties
        'pointer-events',
        // color and painting properties
        'color-interpolation', 'color-interpolation-filters', 'color-profile',
        'color-rendering', 'fill', 'fill-opacity', 'fill-rule',
        'image-rendering', 'marker', 'marker-end', 'marker-mid', 'marker-start',
        'shape-rendering', 'stroke', 'stroke-dasharray', 'stroke-dashoffset',
        'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit',
        'stroke-opacity', 'stroke-width', 'text-rendering',
        // text properties
        'alignment-base', 'baseline-shift', 'dominant-baseline',
        'glyph-orientation-horizontal', 'glyph-orientation-vertical', 'kerning',
        'text-anchor', 'writing-mode',
    );

    /**
     * Parses the given string as XML and turns it into an instance of SVG.
     * Returns null when parsing fails.
     *
     * @param string $string The XML string to parse.
     *
     * @return SVG|null An image object representing the parse result.
     */
    public function parseString($string)
    {
        $xml = simplexml_load_string($string);
        return $this->parseXML($xml);
    }

    /**
     * Parses the file at the given path/URL as XML and turns it into an
     * instance of SVG.
     *
     * The path can be on the local file system, or a URL on the network.
     * Returns null when parsing fails.
     *
     * @param string $filename The path or URL of the file to parse.
     *
     * @return SVG|null An image object representing the parse result.
     */
    public function parseFile($filename)
    {
        $xml = simplexml_load_file($filename);
        return $this->parseXML($xml);
    }

    /**
     * Parses the given XML document into an instance of SVG.
     * Returns null when parsing fails.
     *
     * @param \SimpleXMLElement $xml The root node of the SVG document to parse.
     *
     * @return SVG|null An image object representing the parse result.
     */
    public function parseXML(\SimpleXMLElement $xml)
    {
        $name = $xml->getName();
        if ($name !== 'svg') {
            return null;
        }

        $width = isset($xml['width']) ? $xml['width'] : null;
        $height = isset($xml['height']) ? $xml['height'] : null;
        $namespaces = $xml->getNamespaces(true);

        $img = new SVG($width, $height, $namespaces);

        $nsKeys = array_keys($namespaces);

        $doc = $img->getDocument();
        $this->applyAttributes($doc, $xml, $nsKeys);
        $this->applyStyles($doc, $xml);
        $this->addChildren($doc, $xml, $nsKeys);

        return $img;
    }

    /**
     * Iterates over all XML attributes and applies them to the given node.
     *
     * Since styles in SVG can also be expressed with attributes, this method
     * checks the name of each attribute and, if it matches that of a style,
     * applies it as a style instead. The actual 'style' attribute is ignored.
     *
     * @see SVGReader::$styleAttributes The attributes considered styles.
     *
     * @param SVGNode           $node       The node to apply the attributes to.
     * @param \SimpleXMLElement $xml        The attribute source.
     * @param string[]          $namespaces Array of allowed namespace prefixes.
     *
     * @return void
     */
    private function applyAttributes(SVGNode $node, \SimpleXMLElement $xml,
        array $namespaces)
    {
        // a document like <svg>...</svg> was read (no xmlns declaration)
        if (!in_array('', $namespaces, true) && !in_array(null, $namespaces, true)) {
            $namespaces[] = '';
        }

        foreach ($namespaces as $ns) {
            foreach ($xml->attributes($ns, true) as $key => $value) {
                if ($key === 'style') {
                    continue;
                }
                if (in_array($key, self::$styleAttributes)) {
                    $node->setStyle($key, $value);
                    continue;
                }
                if (!empty($ns) && $ns !== 'svg') {
                    $key = $ns . ':' . $key;
                }
                $node->setAttribute($key, $value);
            }
        }
    }

    /**
     * Parses the 'style' attribute (if it exists) and applies all styles to the
     * given node.
     *
     * This method does NOT handle styles expressed as attributes (stroke="").
     * @see SVGReader::applyAttributes() For styles expressed as attributes.
     *
     * @param SVGNode           $node The node to apply the styles to.
     * @param \SimpleXMLElement $xml  The attribute source.
     *
     * @return void
     */
    private function applyStyles(SVGNode $node, \SimpleXMLElement $xml)
    {
        if (!isset($xml['style'])) {
            return;
        }

        $styles = SVGStyleParser::parseStyles($xml['style']);
        foreach ($styles as $key => $value) {
            $node->setStyle($key, $value);
        }
    }

    /**
     * Iterates over all children, parses them into library class instances,
     * and adds them to the given node container.
     *
     * @param SVGNodeContainer  $node       The node to add the children to.
     * @param \SimpleXMLElement $xml        The XML node containing the children.
     * @param string[]          $namespaces Array of allowed namespace prefixes.
     *
     * @return void
     */
    private function addChildren(SVGNodeContainer $node, \SimpleXMLElement $xml,
        array $namespaces)
    {
        foreach ($xml->children() as $child) {
            $node->addChild($this->parseNode($child, $namespaces));
        }
    }

    /**
     * Parses the given XML element into an instance of a SVGNode subclass.
     * Unknown node types use a generic implementation.
     *
     * @param \SimpleXMLElement $xml        The XML element to parse.
     * @param string[]          $namespaces Array of allowed namespace prefixes.
     *
     * @return SVGNode The parsed node.
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function parseNode(\SimpleXMLElement $xml, array $namespaces)
    {
        $type = $xml->getName();

        if (isset(self::$nodeTypes[$type])) {
            $call = array(self::$nodeTypes[$type], 'constructFromAttributes');
            $node = call_user_func($call, $xml);
        } else {
            $node = new SVGGenericNodeType($type);
        }

        $this->applyAttributes($node, $xml, $namespaces);
        $this->applyStyles($node, $xml);
        $node->setValue($xml);

        if ($node instanceof SVGNodeContainer) {
            $this->addChildren($node, $xml, $namespaces);
        }

        return $node;
    }
}
