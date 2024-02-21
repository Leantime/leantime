<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\ElementInterface;

class TextConverter implements ConverterInterface
{
    public function convert(ElementInterface $element): string
    {
        $markdown = $element->getValue();

        // Remove leftover \n at the beginning of the line
        $markdown = \ltrim($markdown, "\n");

        // Replace sequences of invisible characters with spaces
        $markdown = \preg_replace('~\s+~u', ' ', $markdown);
        \assert(\is_string($markdown));

        // Escape the following characters: '*', '_', '[', ']' and '\'
        if (($parent = $element->getParent()) && $parent->getTagName() !== 'div') {
            $markdown = \preg_replace('~([*_\\[\\]\\\\])~u', '\\\\$1', $markdown);
            \assert(\is_string($markdown));
        }

        $markdown = \preg_replace('~^#~u', '\\\\#', $markdown);
        \assert(\is_string($markdown));

        if ($markdown === ' ') {
            $next = $element->getNext();
            if (! $next || $next->isBlock()) {
                $markdown = '';
            }
        }

        return \htmlspecialchars($markdown, ENT_NOQUOTES, 'UTF-8');
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['#text'];
    }
}
