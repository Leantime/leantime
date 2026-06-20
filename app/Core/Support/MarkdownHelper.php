<?php

namespace Leantime\Core\Support;

use Illuminate\Support\Str;

/**
 * Converts PHP arrays into formatted markdown strings for LLM consumption.
 *
 * Delegates to the Str::toMarkdown() macro for the actual conversion logic.
 * This class provides a static API for use in entity formatters and other contexts.
 */
class MarkdownHelper
{
    /**
     * Converts a PHP array into a formatted markdown string.
     *
     * @param  mixed  $data  The data to convert to markdown
     * @param  int  $headerLevel  Starting header level (1-6)
     * @return string Markdown formatted string
     */
    public static function encode(mixed $data, int $headerLevel = 2): string
    {
        if (! is_array($data)) {
            return self::sanitizeForMarkdown((string) $data);
        }

        return Str::toMarkdown($data, $headerLevel);
    }

    /**
     * Check if an array is associative (has string keys) or sequential (numeric keys).
     *
     * @param  mixed  $array  The array to check
     * @return bool True if associative, false if sequential
     */
    public static function isAssociativeArray(mixed $array): bool
    {
        if (! is_array($array) || empty($array)) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Sanitize a value for safe markdown formatting.
     *
     * @param  mixed  $value  The value to sanitize
     * @return string The sanitized string
     */
    public static function sanitizeForMarkdown(mixed $value): string
    {
        if ($value === null) {
            return '*null*';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return LLMStringSanitizer::sanitizeForLLM((string) $value);
    }
}
