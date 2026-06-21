<?php

namespace Leantime\Core\Support;

use Illuminate\Support\Str;

/**
 * Sanitizes strings for safe LLM/AI consumption, prevents prompt injection attacks.
 *
 * Delegates to the Str::sanitizeForLLM() macro for the actual sanitization logic.
 * This class provides a static API for use in entity formatters and other contexts
 * where the Str macro call pattern is less convenient.
 */
class LLMStringSanitizer
{
    /**
     * Sanitize a string for safe use with LLM APIs.
     *
     * Removes potential prompt injection patterns and problematic characters
     * that could interfere with JSON serialization or system prompts.
     *
     * @param  mixed  $input  The value to sanitize
     * @param  bool  $removeNewlines  Whether to strip newlines from the result
     * @return string The sanitized string
     */
    public static function sanitizeForLLM(mixed $input, bool $removeNewlines = false): string
    {
        if ($input === null) {
            return '';
        }

        if (! is_string($input)) {
            return (string) $input;
        }

        return Str::sanitizeForLLM($input, $removeNewlines);
    }
}
