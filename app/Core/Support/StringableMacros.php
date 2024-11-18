<?php

namespace Leantime\Core\Support;

/** @mixin \Illuminate\Support\Stringable */
class StringableMacros
{
    /**
     * Returns an alphanumeric string with all special characters stripped
     *
     * @return \Closure
     */
    public function alphaNumeric($removeSpaces = false)
    {

        return function () use ($removeSpaces) {

            // Step 1: Remove all special characters except letters and digits
            $cleaned = preg_replace('/[^A-Za-z0-9 ]/', '', $this->value);

            if ($removeSpaces) {
                $cleaned = str_replace(' ', '', $cleaned);
            } else {
                // Step 2: Replace multiple spaces with a single space
                $cleaned = preg_replace('/\s+/', ' ', $cleaned);
            }

            // Step 3: Trim leading and trailing spaces
            $cleaned = trim($cleaned);

            return new self($cleaned);
        };

    }
}
