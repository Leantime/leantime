<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\ElementInterface;

class ParagraphConverter implements ConverterInterface
{
    public function convert(ElementInterface $element): string
    {
        $value = $element->getValue();

        $markdown = '';

        $lines = \preg_split('/\r\n|\r|\n/', $value);
        \assert($lines !== false);

        foreach ($lines as $line) {
            /*
             * Some special characters need to be escaped based on the position that they appear
             * The following function will deal with those special cases.
             */
            $markdown .= $this->escapeSpecialCharacters($line);
            $markdown .= "\n";
        }

        return \trim($markdown) !== '' ? \rtrim($markdown) . "\n\n" : '';
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['p'];
    }

    private function escapeSpecialCharacters(string $line): string
    {
        $line = $this->escapeFirstCharacters($line);
        $line = $this->escapeOtherCharacters($line);
        $line = $this->escapeOtherCharactersRegex($line);

        return $line;
    }

    private function escapeFirstCharacters(string $line): string
    {
        $escapable = [
            '>',
            '- ',
            '+ ',
            '--',
            '~~~',
            '---',
            '- - -',
        ];

        foreach ($escapable as $i) {
            if (\strpos(\ltrim($line), $i) === 0) {
                // Found a character that must be escaped, adding a backslash before
                return '\\' . \ltrim($line);
            }
        }

        return $line;
    }

    private function escapeOtherCharacters(string $line): string
    {
        $escapable = [
            '<!--',
        ];

        foreach ($escapable as $i) {
            if (($pos = \strpos($line, $i)) === false) {
                continue;
            }

            // Found an escapable character, escaping it
            $line = \substr_replace($line, '\\', $pos, 0);
        }

        return $line;
    }

    private function escapeOtherCharactersRegex(string $line): string
    {
        $regExs = [
            // Match numbers ending on ')' or '.' that are at the beginning of the line.
            // They will be escaped if immediately followed by a space or newline.
            '/^[0-9]+(?=(\)|\.)( |$))/',
        ];

        foreach ($regExs as $i) {
            if (! \preg_match($i, $line, $match)) {
                continue;
            }

            // Matched an escapable character, adding a backslash on the string before the offending character
            $line = \substr_replace($line, '\\', \strlen($match[0]), 0);
        }

        return $line;
    }
}
