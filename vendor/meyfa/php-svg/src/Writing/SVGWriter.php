<?php

namespace SVG\Writing;

use SVG\Nodes\Structures\SVGStyle;
use SVG\Nodes\SVGNode;
use SVG\Nodes\SVGNodeContainer;

/**
 * This class is used for composing ("writing") XML strings from nodes.
 * Every instance corresponds to one output string.
 */
class SVGWriter
{
    /** @var string $outString The XML output string being written */
    private $outString = '';

    public function __construct($isStandalone = true)
    {
        if ($isStandalone) {
            $this->outString = '<?xml version="1.0" encoding="utf-8"?>';
        }
    }

    /**
     * @return string The XML output string up until the point currenly written.
     */
    public function getString()
    {
        return $this->outString;
    }

    /**
     * Converts the given node into its XML representation and appends that to
     * this writer's output string.
     *
     * The generated string contains the attributes defined via
     * SVGNode::getSerializableAttributes() and the styles defined via
     * SVGNode::getSerializableStyles().
     * Container nodes (<g></g>) and self-closing tags (<rect />) are
     * distinguished correctly.
     *
     * @param SVGNode $node The node to write.
     *
     * @return void
     */
    public function writeNode(SVGNode $node)
    {
        $this->outString .= '<'.$node->getName();

        $this->appendAttributes($node->getSerializableAttributes());
        $this->appendStyles($node->getSerializableStyles());

        $textContent = htmlspecialchars($node->getValue());

        if ($node instanceof SVGNodeContainer) {
            $this->outString .= '>';
            for ($i = 0, $n = $node->countChildren(); $i < $n; ++$i) {
                $this->writeNode($node->getChild($i));
            }
            $this->outString .= $textContent.'</'.$node->getName().'>';
            return;
        }

        if ($node instanceof SVGStyle) {
            $this->outString .= '>';
            $this->writeCdata($node->getCss());
            $this->outString .= $textContent.'</'.$node->getName().'>';
            return;
        }

        if (!empty($textContent)) {
            $this->outString .= '>' . $textContent . '</'.$node->getName().'>';
            return;
        }

        $this->outString .= ' />';
    }

    /**
     * Converts the given styles into a CSS string, then appends a 'style'
     * attribute with the value set to that string to this writer's output.
     *
     * @param string[] $styles An associative array of styles for the attribute.
     *
     * @return void
     */
    private function appendStyles(array $styles)
    {
        if (empty($styles)) {
            return;
        }

        $string = '';
        $prependSemicolon = false;
        foreach ($styles as $key => $value) {
            if ($prependSemicolon) {
                $string .= '; ';
            }
            $prependSemicolon = true;
            $string .= $key.': '.$value;
        }

        $this->appendAttribute('style', $string);
    }

    /**
     * Appends all attributes defined in the given associative array to this
     * writer's output.
     *
     * @param string[] $attrs An associative array of attribute strings.
     *
     * @return void
     */
    private function appendAttributes(array $attrs)
    {
        foreach ($attrs as $key => $value) {
            $this->appendAttribute($key, $value);
        }
    }

    /**
     * Appends a single attribute given by key and value to this writer's
     * output.
     *
     * @param string $attrName  The attribute name.
     * @param string $attrValue The attribute value.
     *
     * @return void
     */
    private function appendAttribute($attrName, $attrValue)
    {
        $xml1 = defined('ENT_XML1') ? ENT_XML1 : 16;

        $attrName = htmlspecialchars($attrName, $xml1 | ENT_COMPAT);
        $attrValue = htmlspecialchars($attrValue, $xml1 | ENT_COMPAT);

        $this->outString .= ' '.$attrName.'="'.$attrValue.'"';
    }

    /**
     * Appends CDATA content given the $cdata value to the writer's output.
     *
     * @param string $cdata The content.
     *
     * @return void
     */
    private function writeCdata($cdata)
    {
        $xml1 = defined('ENT_XML1') ? ENT_XML1 : 16;

        $cdata = htmlspecialchars($cdata, $xml1 | ENT_COMPAT);

        $this->outString .= '<![CDATA[' . $cdata . ']]>';
    }
}
