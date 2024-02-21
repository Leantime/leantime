<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\ElementInterface;

class BlockquoteConverter implements ConverterInterface
{
    public function convert(ElementInterface $element): string
    {
        // Contents should have already been converted to Markdown by this point,
        // so we just need to add '>' symbols to each line.

        $markdown = '';

        $quoteContent = \trim($element->getValue());

        $lines = \preg_split('/\r\n|\r|\n/', $quoteContent);
        \assert(\is_array($lines));

        $totalLines = \count($lines);

        foreach ($lines as $i => $line) {
            $markdown .= '> ' . $line . "\n";
            if ($i + 1 === $totalLines) {
                $markdown .= "\n";
            }
        }

        return $markdown;
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['blockquote'];
    }
}
