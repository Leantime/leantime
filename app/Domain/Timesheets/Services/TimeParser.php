<?php

namespace Leantime\Domain\Timesheets\Services;

use InvalidArgumentException;

/**
 * TimeParser - Parses various time input formats and converts them to decimal hours
 *
 * Supports Jira-style time tracking formats:
 * - Weeks (w): 3w
 * - Days (d): 2d
 * - Hours (h): 4h
 * - Minutes (m): 30m
 * - Plain numbers (interpreted as hours): 6
 * - Natural language: "30 minutes", "2 hours", etc.
 * - Mixed formats: "1d 2h 3m", "30m 4h" (order independent)
 *
 * Examples:
 * - "3w" -> 120 hours (assuming 40-hour work week)
 * - "2d" -> 16 hours (assuming 8-hour work day)
 * - "4h" -> 4 hours
 * - "30m" -> 0.5 hours
 * - "1d 2h 30m" -> 10.5 hours
 * - "6" -> 6 hours
 * - "30 minutes" -> 0.5 hours
 */
class TimeParser
{
    /**
     * Hours per work day (configurable based on organization settings)
     */
    private const HOURS_PER_DAY = 8;

    /**
     * Days per work week (configurable based on organization settings)
     */
    private const DAYS_PER_WEEK = 5;

    /**
     * Parse time input and convert to decimal hours
     *
     * @param string|int|float $input Time input in various formats
     * @return float Decimal hours
     * @throws InvalidArgumentException If input format is invalid
     */
    public function parseTimeToDecimal(string|int|float $input): float
    {
        // Handle numeric input directly
        if (is_numeric($input)) {
            $hours = floatval($input);
            if ($hours < 0) {
                throw new InvalidArgumentException('Time cannot be negative');
            }
            return round($hours, 4);
        }

        // Normalize the input (trim spaces, lowercase)
        $input = strtolower(trim($input));

        if (empty($input)) {
            throw new InvalidArgumentException('Time input cannot be empty');
        }

        // Try to parse as natural language first
        $naturalLanguageResult = $this->parseNaturalLanguage($input);
        if ($naturalLanguageResult !== null) {
            return round($naturalLanguageResult, 4);
        }

        // Try to parse as Jira-style format
        $jiraFormatResult = $this->parseJiraFormat($input);
        if ($jiraFormatResult !== null) {
            return round($jiraFormatResult, 4);
        }

        // If nothing matched, throw an error
        throw new InvalidArgumentException(
            'Invalid time format. Please use formats like: "6", "2h 30m", "1d 2h", "1w", "2 hours", "30 minutes", etc.'
        );
    }

    /**
     * Parse natural language time expressions
     *
     * @param string $input Input string
     * @return float|null Decimal hours or null if not matched
     */
    private function parseNaturalLanguage(string $input): ?float
    {
        // Remove extra spaces
        $input = preg_replace('/\s+/', ' ', $input);

        // Pattern: "30 minutes", "2 hours", "1 day", "2 weeks"
        $patterns = [
            '/^(\d+(?:\.\d+)?)\s*(?:minute|minutes|min|mins)$/i' => function ($matches) {
                return floatval($matches[1]) / 60;
            },
            '/^(\d+(?:\.\d+)?)\s*(?:hour|hours|hr|hrs)$/i' => function ($matches) {
                return floatval($matches[1]);
            },
            '/^(\d+(?:\.\d+)?)\s*(?:day|days)$/i' => function ($matches) {
                return floatval($matches[1]) * self::HOURS_PER_DAY;
            },
            '/^(\d+(?:\.\d+)?)\s*(?:week|weeks|wk|wks)$/i' => function ($matches) {
                return floatval($matches[1]) * self::DAYS_PER_WEEK * self::HOURS_PER_DAY;
            },
        ];

        foreach ($patterns as $pattern => $callback) {
            if (preg_match($pattern, $input, $matches)) {
                $result = $callback($matches);
                if ($result < 0) {
                    throw new InvalidArgumentException('Time cannot be negative');
                }
                return $result;
            }
        }

        return null;
    }

    /**
     * Parse Jira-style time format
     *
     * @param string $input Input string
     * @return float|null Decimal hours or null if not matched
     */
    private function parseJiraFormat(string $input): ?float
    {
        $totalHours = 0;
        $matched = false;

        // Match all occurrences of number + unit (w, d, h, m)
        // Pattern: optional number (integer or decimal) followed by w, d, h, or m
        preg_match_all('/(\d+(?:\.\d+)?)\s*([wdhm])/i', $input, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $matched = true;
            $value = floatval($match[1]);
            $unit = strtolower($match[2]);

            if ($value < 0) {
                throw new InvalidArgumentException('Time values cannot be negative');
            }

            switch ($unit) {
                case 'w': // weeks
                    $totalHours += $value * self::DAYS_PER_WEEK * self::HOURS_PER_DAY;
                    break;
                case 'd': // days
                    $totalHours += $value * self::HOURS_PER_DAY;
                    break;
                case 'h': // hours
                    $totalHours += $value;
                    break;
                case 'm': // minutes
                    $totalHours += $value / 60;
                    break;
            }
        }

        if (!$matched) {
            return null;
        }

        // Remove all matched patterns and check if there's any non-whitespace left
        $remaining = preg_replace('/\d+(?:\.\d+)?\s*[wdhm]/i', '', $input);
        $remaining = trim($remaining);
        
        if (!empty($remaining)) {
            throw new InvalidArgumentException(
                'Invalid characters in time input: "' . $remaining . '". Use formats like: 1w, 2d, 3h, 30m'
            );
        }

        if ($totalHours < 0) {
            throw new InvalidArgumentException('Time cannot be negative');
        }

        return $totalHours;
    }

    /**
     * Get hours per work day configuration
     *
     * @return int Hours per work day
     */
    public function getHoursPerDay(): int
    {
        return self::HOURS_PER_DAY;
    }

    /**
     * Get days per work week configuration
     *
     * @return int Days per work week
     */
    public function getDaysPerWeek(): int
    {
        return self::DAYS_PER_WEEK;
    }

    /**
     * Validate time input without parsing
     *
     * @param string|int|float $input Time input
     * @return bool True if valid, false otherwise
     */
    public function isValidTimeInput(string|int|float $input): bool
    {
        try {
            $this->parseTimeToDecimal($input);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Get error message for invalid input
     *
     * @param string|int|float $input Time input
     * @return string|null Error message or null if valid
     */
    public function getValidationError(string|int|float $input): ?string
    {
        try {
            $this->parseTimeToDecimal($input);
            return null;
        } catch (InvalidArgumentException $e) {
            return $e->getMessage();
        }
    }
}


