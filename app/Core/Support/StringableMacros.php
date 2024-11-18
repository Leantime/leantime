<?php

namespace Leantime\Core\Support;

use Illuminate\Support\Str;
/**
 * @mixin \Illuminate\Support\Stringable
 */
class StringableMacros
{
    /**
     * Cleans a string by removing special characters and optionally spaces.
     *
     * @param bool $removeSpaces Whether to remove spaces from the string.
     * @return callable A function that cleans a string based on the given parameter.
     */
    public function alphaNumeric($removeSpaces = false)
    {
        return function () use ($removeSpaces) {
            /** @var \Illuminate\Support\Stringable $this */
            $cleaned = preg_replace('/[^A-Za-z0-9 ]/', '', (string) $this);

            if ($removeSpaces) {
                $cleaned = str_replace(' ', '', $cleaned);
            } else {
                // Step 2: Replace multiple spaces with a single space
                $cleaned = preg_replace('/\s+/', ' ', $cleaned);
            }

            // Step 3: Trim leading and trailing spaces
            $cleaned = trim($cleaned);

            return $cleaned;
        };

    }
}
