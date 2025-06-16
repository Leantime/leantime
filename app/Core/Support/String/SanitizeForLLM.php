<?php

namespace Leantime\Core\Support\String;

/**
 * @mixin \Illuminate\Support\Stringable
 */
class SanitizeForLLM
{
    /**
     * Sanitizes string for safe use with LLM APIs by removing potential prompt injection patterns
     * and other problematic characters that could interfere with JSON serialization or system prompts.
     *
     * @return callable A function that sanitizes a string for LLM processing
     */
    public function sanitizeForLLM()
    {
        return function ($value) {
            if (! is_string($value)) {
                return $value ?? '';
            }

            // Step 1: Replace line breaks with space
            $result = str_replace(["\r\n", "\r"], "\n", $value);

            // Step 2: Escape JSON special characters except newlines
            $result = str_replace(
                ['\\', '"', "\t", "\f", "\b"],
                ['\\\\', '\\"', ' ', ' ', ' '],
                $result
            );

            // Step 3: Replace problematic characters with safe alternatives
            $replacements = [
                // Replace backslashes with forward slashes (for paths)
                '\\' => '/',

                // Replace double quotes with single quotes
                '"' => "'",

                // Replace special JSON characters with similar safe characters
                '{' => '(',
                '}' => ')',

                // Collapse multiple spaces into single space
                '  ' => ' ',
            ];

            $result = str_replace(array_keys($replacements), array_values($replacements), $result);

            // Step 4: Remove any remaining potentially problematic characters
            $result = preg_replace('/[\x80-\x9F]/u', '', $result);

            // Remove common delimiters that might be used to "break out" of a system prompt
            $attackPatterns = [
                // System prompt break patterns
                '/\<\/?system\>/', '/\<\/?assistant\>/', '/\<\/?user\>/', '/\<\/?human\>/',
                // XML-like tags that might be used in exploits
                '/\<\/?instructions\>/', '/\<\/?prompt\>/', '/\<\/?context\>/',
                // Special command patterns
                '/\[\[.*?\]\]/', '/\{\{.*?\}\}/',
                // Common attack prefix/suffix patterns
                '/ignore previous instructions/', '/ignore all previous commands/',
                '/disregard (previous|prior|all|your) instructions?/',
                '/forget (previous|prior|all|your) instructions?/',

                // Additional boundary markers
                '/```system/', '/```instructions/', '/```prompt/',
                '/\$\$\$system/', '/\$\$\$instructions/', '/\$\$\$prompt/',
            ];

            $result = preg_replace($attackPatterns, '', $result);

            // Step 5: Handle potential JSON serialization issues
            // Ensure the string is valid UTF-8
            if (! mb_check_encoding($result, 'UTF-8')) {
                $result = mb_convert_encoding($result, 'UTF-8', 'UTF-8');
            }

            // Step 6: Additional sanitization for special patterns
            // Remove or replace specific problematic sequences
            $result = str_replace(
                ['{{{', '}}}', '<<<', '>>>'],
                ['{ { {', '} } }', '< < <', '> > >'],
                $result
            );

            // Step 7: Remove consecutive spaces (which can occur after other replacements)
            $result = preg_replace('/ {2,}/', ' ', $result);

            return $result;
        };
    }
}
