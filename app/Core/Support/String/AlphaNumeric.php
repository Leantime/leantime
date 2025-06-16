<?php

namespace Leantime\Core\Support\String;

/**
 * @mixin \Illuminate\Support\Stringable
 */
class AlphaNumeric
{
    /**
     * Cleans a string by removing special characters and optionally spaces.
     *
     * @param  bool  $removeSpaces  Whether to remove spaces from the string.
     * @return callable A function that cleans a string based on the given parameter.
     */
    public function alphaNumeric($removeSpaces = false)
    {
        return function ($value) use ($removeSpaces) {
            $cleaned = preg_replace('/[^A-Za-z0-9 ]/', '', (string) $value);

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
