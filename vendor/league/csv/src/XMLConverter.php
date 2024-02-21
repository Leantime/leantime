<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Csv;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMException;

/**
 * Converts tabular data into a DOMDocument object.
 */
class XMLConverter
{
    /**
     * XML Root name.
     */
    protected string $root_name = 'csv';

    /**
     * XML Node name.
     */
    protected string $record_name = 'row';

    /**
     * XML Item name.
     */
    protected string $field_name = 'cell';

    /**
     * XML column attribute name.
     */
    protected string $column_attr = '';

    /**
     * XML offset attribute name.
     */
    protected string $offset_attr = '';

    public static function create(): self
    {
        return new self();
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated since version 9.7.0
     * @see XMLConverter::create()
     */
    public function __construct()
    {
    }

    /**
     * Convert a Record collection into a DOMDocument.
     */
    public function convert(iterable $records): DOMDocument
    {
        $doc = new DOMDocument('1.0');
        $node = $this->import($records, $doc);
        $doc->appendChild($node);

        return $doc;
    }

    /**
     * Create a new DOMElement related to the given DOMDocument.
     *
     * **DOES NOT** attach to the DOMDocument
     */
    public function import(iterable $records, DOMDocument $doc): DOMElement
    {
        $root = $doc->createElement($this->root_name);
        foreach ($records as $offset => $record) {
            $node = $this->recordToElement($doc, $record, $offset);
            $root->appendChild($node);
        }

        return $root;
    }

    /**
     * Convert a CSV record into a DOMElement and
     * adds its offset as DOMElement attribute.
     */
    protected function recordToElement(DOMDocument $doc, array $record, int $offset): DOMElement
    {
        $node = $doc->createElement($this->record_name);
        foreach ($record as $node_name => $value) {
            $item = $this->fieldToElement($doc, (string) $value, $node_name);
            $node->appendChild($item);
        }

        if ('' !== $this->offset_attr) {
            $node->setAttribute($this->offset_attr, (string) $offset);
        }

        return $node;
    }

    /**
     * Convert Cell to Item.
     *
     * Convert the CSV item into a DOMElement and adds the item offset
     * as attribute to the returned DOMElement
     *
     * @param int|string $node_name
     */
    protected function fieldToElement(DOMDocument $doc, string $value, $node_name): DOMElement
    {
        $item = $doc->createElement($this->field_name);
        $item->appendChild($doc->createTextNode($value));

        if ('' !== $this->column_attr) {
            $item->setAttribute($this->column_attr, (string) $node_name);
        }

        return $item;
    }

    /**
     * XML root element setter.
     */
    public function rootElement(string $node_name): self
    {
        $clone = clone $this;
        $clone->root_name = $this->filterElementName($node_name);

        return $clone;
    }

    /**
     * Filter XML element name.
     *
     * @throws DOMException If the Element name is invalid
     */
    protected function filterElementName(string $value): string
    {
        return (new DOMElement($value))->tagName;
    }

    /**
     * XML Record element setter.
     */
    public function recordElement(string $node_name, string $record_offset_attribute_name = ''): self
    {
        $clone = clone $this;
        $clone->record_name = $this->filterElementName($node_name);
        $clone->offset_attr = $this->filterAttributeName($record_offset_attribute_name);

        return $clone;
    }

    /**
     * Filter XML attribute name.
     *
     * @param string $value Element name
     *
     * @throws DOMException If the Element attribute name is invalid
     */
    protected function filterAttributeName(string $value): string
    {
        if ('' === $value) {
            return $value;
        }

        return (new DOMAttr($value))->name;
    }

    /**
     * XML Field element setter.
     */
    public function fieldElement(string $node_name, string $fieldname_attribute_name = ''): self
    {
        $clone = clone $this;
        $clone->field_name = $this->filterElementName($node_name);
        $clone->column_attr = $this->filterAttributeName($fieldname_attribute_name);

        return $clone;
    }
}
