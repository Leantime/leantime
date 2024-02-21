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

use DOMDocument;
use DOMElement;
use DOMException;
use function preg_match;

/**
 * Converts tabular data into an HTML Table string.
 */
class HTMLConverter
{
    /** table class attribute value. */
    protected string $class_name = 'table-csv-data';
    /** table id attribute value. */
    protected string $id_value = '';
    protected XMLConverter $xml_converter;

    public static function create(): self
    {
        return new self();
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated since version 9.7.0
     * @see HTMLConverterTest::create()
     */
    public function __construct()
    {
        $this->xml_converter = XMLConverter::create()
            ->rootElement('table')
            ->recordElement('tr')
            ->fieldElement('td')
        ;
    }

    /**
     * Converts a tabular data collection into a HTML table string.
     *
     * @param string[] $header_record An optional array of headers outputted using the`<thead>` section
     * @param string[] $footer_record An optional array of footers to output to the table using `<tfoot>` and `<th>` elements
     */
    public function convert(iterable $records, array $header_record = [], array $footer_record = []): string
    {
        $doc = new DOMDocument('1.0');
        if ([] === $header_record && [] === $footer_record) {
            $table = $this->xml_converter->import($records, $doc);
            $this->addHTMLAttributes($table);
            $doc->appendChild($table);

            /** @var string $content */
            $content = $doc->saveHTML();

            return $content;
        }

        $table = $doc->createElement('table');

        $this->addHTMLAttributes($table);
        $this->appendHeaderSection('thead', $header_record, $table);
        $this->appendHeaderSection('tfoot', $footer_record, $table);

        $table->appendChild($this->xml_converter->rootElement('tbody')->import($records, $doc));

        $doc->appendChild($table);

        /** @var string $content */
        $content = $doc->saveHTML();

        return $content;
    }

    /**
     * Creates a DOMElement representing a HTML table heading section.
     */
    protected function appendHeaderSection(string $node_name, array $record, DOMElement $table): void
    {
        if ([] === $record) {
            return;
        }

        /** @var DOMDocument $ownerDocument */
        $ownerDocument = $table->ownerDocument;
        $node = $this->xml_converter
            ->rootElement($node_name)
            ->recordElement('tr')
            ->fieldElement('th')
            ->import([$record], $ownerDocument)
        ;

        /** @var DOMElement $element */
        foreach ($node->getElementsByTagName('th') as $element) {
            $element->setAttribute('scope', 'col');
        }

        $table->appendChild($node);
    }

    /**
     * Adds class and id attributes to an HTML tag.
     */
    protected function addHTMLAttributes(DOMElement $node): void
    {
        $node->setAttribute('class', $this->class_name);
        $node->setAttribute('id', $this->id_value);
    }

    /**
     * HTML table class name setter.
     *
     * @throws DOMException if the id_value contains any type of whitespace
     */
    public function table(string $class_name, string $id_value = ''): self
    {
        if (1 === preg_match(",\s,", $id_value)) {
            throw new DOMException("the id attribute's value must not contain whitespace (spaces, tabs etc.)");
        }
        $clone = clone $this;
        $clone->class_name = $class_name;
        $clone->id_value = $id_value;

        return $clone;
    }

    /**
     * HTML tr record offset attribute setter.
     */
    public function tr(string $record_offset_attribute_name): self
    {
        $clone = clone $this;
        $clone->xml_converter = $this->xml_converter->recordElement('tr', $record_offset_attribute_name);

        return $clone;
    }

    /**
     * HTML td field name attribute setter.
     */
    public function td(string $fieldname_attribute_name): self
    {
        $clone = clone $this;
        $clone->xml_converter = $this->xml_converter->fieldElement('td', $fieldname_attribute_name);

        return $clone;
    }
}
