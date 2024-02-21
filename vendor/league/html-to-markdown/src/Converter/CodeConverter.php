<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\ElementInterface;

class CodeConverter implements ConverterInterface
{
    public function convert(ElementInterface $element): string
    {
        $language = '';

        // Checking for language class on the code block
        $classes = $element->getAttribute('class');

        if ($classes) {
            // Since tags can have more than one class, we need to find the one that starts with 'language-'
            $classes = \explode(' ', $classes);
            foreach ($classes as $class) {
                if (\strpos($class, 'language-') !== false) {
                    // Found one, save it as the selected language and stop looping over the classes.
                    $language = \str_replace('language-', '', $class);
                    break;
                }
            }
        }

        $markdown = '';
        $code     = \html_entity_decode($element->getChildrenAsString());

        // In order to remove the code tags we need to search for them and, in the case of the opening tag
        // use a regular expression to find the tag and the other attributes it might have
        $code = \preg_replace('/<code\b[^>]*>/', '', $code);
        \assert($code !== null);
        $code = \str_replace('</code>', '', $code);

        // Checking if it's a code block or span
        if ($this->shouldBeBlock($element, $code)) {
            // Code block detected, newlines will be added in parent
            $markdown .= '```' . $language . "\n" . $code . "\n" . '```';
        } else {
            // One line of code, wrapping it on one backtick, removing new lines
            $markdown .= '`' . \preg_replace('/\r\n|\r|\n/', '', $code) . '`';
        }

        return $markdown;
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['code'];
    }

    private function shouldBeBlock(ElementInterface $element, string $code): bool
    {
        $parent = $element->getParent();
        if ($parent !== null && $parent->getTagName() === 'pre') {
            return true;
        }

        return \preg_match('/[^\s]` `/', $code) === 1;
    }
}
