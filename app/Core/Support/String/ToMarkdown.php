<?php

namespace Leantime\Core\Support\String;

use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Support\Stringable
 */
class ToMarkdown
{
    /**
     * Converts a PHP array into a formatted markdown string.
     *
     * @param  int  $headerLevel  Starting header level (1-6)
     * @return callable A function that converts data to markdown format
     */
    public function toMarkdown($headerLevel = 2)
    {
        $sanitizeForMarkdown = function ($value)
        {
            if ($value === null) {
                return '*null*';
            }

            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            $string = (string) $value;

            // Use the sanitizeForLLM macro for consistent sanitization
            $string = Str::sanitizeForLLM($string);

            return $string;
        };

        return function ($data) use ($headerLevel, $sanitizeForMarkdown) {
            if (! is_array($data)) {

                if (is_bool($data)) {
                    return $data ? 'true' : 'false';
                }

                $string = (string) $data;

                // Use the sanitizeForLLM macro for consistent sanitization
                return Str::sanitizeForLLM($string);

            }

            $result = '';
            $indentLevel = 0;

            // Internal function to process array recursively
            $processArray = function ($array, $level, $indent) use (&$processArray, &$result, $sanitizeForMarkdown) {
                foreach ($array as $key => $value) {
                    // Skip numeric keys for sequential arrays if they're just indices
                    $skipKey = is_int($key) && $key === count($array) - count($array);

                    if (! $skipKey) {
                        $data = preg_split('/(?=[A-Z])/', $key);
                        $string = implode(' ', $data);
                        $string = ucwords($string);

                        $result .= str_repeat('  ', $indent).'**'.$sanitizeForMarkdown($string).':**';
                    }

                    if (is_array($value)) {
                        // Handle nested arrays
                        if (empty($value)) {
                            $result .= str_repeat('  ', $indent)."*Empty*\n\n";
                        } elseif (array_keys($array) !== range(0, count($array) - 1)) {
                            // Associative array - process recursively
                            $processArray($value, $level + 1, $indent + 1);
                        } else {
                            // Sequential array - create a list
                            foreach ($value as $item) {
                                if (is_array($item)) {
                                    // Nested array item
                                    $result .= str_repeat('  ', $indent).'- ';
                                    $nestedResult = '';
                                    $processArray($item, $level + 2, 0);

                                    // Format the nested result as an indented block
                                    $lines = explode("\n", trim($nestedResult));
                                    $result .= array_shift($lines)."\n";
                                    foreach ($lines as $line) {
                                        $result .= str_repeat('  ', $indent + 1).$line."\n";
                                    }
                                } else {
                                    // Simple item
                                    $result .= str_repeat('  ', $indent).'- '.$sanitizeForMarkdown($item)."\n";
                                }
                            }
                            $result .= "\n";
                        }
                    } elseif (is_bool($value)) {
                        // Handle boolean values
                        $result .= str_repeat('  ', $indent).($value ? '✅ Yes' : '❌ No')."\n\n";
                    } elseif ($value === null) {
                        // Handle null values
                        $result .= str_repeat('  ', $indent)."*Not provided*\n\n";
                    } else {
                        // Handle scalar values
                        $formattedValue = $sanitizeForMarkdown($value);

                        // Check if value is multi-line and format accordingly
                        if (strpos($formattedValue, "\n") !== false) {
                            $result .= str_repeat('  ', $indent)."```\n".$formattedValue."\n```\n\n";
                        } else {
                            $result .= str_repeat('  ', $indent).$formattedValue."\n\n";
                        }
                    }
                }

                return $result;
            };

            // Start processing
            $processArray($data, $headerLevel, $indentLevel);

            // Clean up and return result
            return trim($result);
        };
    }

}
