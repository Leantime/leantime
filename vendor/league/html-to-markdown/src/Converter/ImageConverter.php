<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\ElementInterface;

class ImageConverter implements ConverterInterface
{
    public function convert(ElementInterface $element): string
    {
        $src   = $element->getAttribute('src');
        $alt   = $element->getAttribute('alt');
        $title = $element->getAttribute('title');

        if ($title !== '') {
            // No newlines added. <img> should be in a block-level element.
            return '![' . $alt . '](' . $src . ' "' . $title . '")';
        }

        return '![' . $alt . '](' . $src . ')';
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['img'];
    }
}
