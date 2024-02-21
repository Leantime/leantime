<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\ElementInterface;

interface ConverterInterface
{
    public function convert(ElementInterface $element): string;

    /**
     * @return string[]
     */
    public function getSupportedTags(): array;
}
