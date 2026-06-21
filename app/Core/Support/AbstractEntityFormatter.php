<?php

namespace Leantime\Core\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Abstract base class for entity formatters.
 *
 * Provides common functionality for formatting domain entities into markdown,
 * including sanitization, field filtering, and utility methods.
 */
abstract class AbstractEntityFormatter implements EntityFormatterInterface
{
    /**
     * Default fields to exclude from formatting across all entities.
     */
    protected array $defaultExcludedFields = [
        'password',
        'token',
        'secret',
        'key',
        'hash',
        'salt',
    ];

    /**
     * Fields to exclude from formatting for this specific entity type.
     */
    protected array $excludedFields = [];

    /**
     * Priority order for displaying fields (higher priority = displayed first).
     */
    protected array $fieldPriority = [];

    /**
     * Format the entity using the default formatting logic.
     */
    public function format(): string
    {
        return $this->formatForContext([]);
    }

    /**
     * Format the entity with context-aware formatting.
     */
    public function formatForContext(array $context = []): string
    {
        $data = $this->prepareEntityData($context);
        $header = $this->formatHeader($data);
        $body = $this->formatBody($data, $context);

        return $header."\n".$body;
    }

    /**
     * Get a compact summary of the entity.
     */
    public function getSummary(): string
    {
        $data = $this->prepareEntityData();

        return $this->formatSummary($data);
    }

    /**
     * Prepare the entity data for formatting.
     * This method should be implemented by concrete classes to extract
     * and organize data from their specific entity types.
     */
    abstract protected function prepareEntityData(array $context = []): array;

    /**
     * Format the header section of the entity.
     */
    abstract protected function formatHeader(array $data): string;

    /**
     * Format the body section of the entity.
     */
    protected function formatBody(array $data, array $context = []): string
    {
        $filteredData = $this->filterFields($data, $context);
        $sortedData = $this->sortFields($filteredData);

        return Str::toMarkdown($sortedData);
    }

    /**
     * Format a compact summary of the entity.
     */
    abstract protected function formatSummary(array $data): string;

    /**
     * Filter out excluded fields and apply context-specific filtering.
     */
    protected function filterFields(array $data, array $context = []): array
    {
        $excludedFields = array_merge($this->defaultExcludedFields, $this->excludedFields);

        // Apply context-specific field filtering
        if (isset($context['includeFields']) && is_array($context['includeFields'])) {
            $data = array_intersect_key($data, array_flip($context['includeFields']));
        }

        if (isset($context['excludeFields']) && is_array($context['excludeFields'])) {
            $excludedFields = array_merge($excludedFields, $context['excludeFields']);
        }

        // Remove excluded fields
        foreach ($excludedFields as $field) {
            unset($data[$field]);
        }

        // Remove empty values unless specifically requested to keep them
        if (! isset($context['keepEmpty']) || ! $context['keepEmpty']) {
            $data = array_filter($data, function ($value) {
                return $value !== null && $value !== '' && $value !== [];
            });
        }

        return $data;
    }

    /**
     * Sort fields according to priority and alphabetically.
     */
    protected function sortFields(array $data): array
    {
        if (empty($this->fieldPriority)) {
            return $data;
        }

        $prioritized = [];

        // First add prioritized fields in order
        foreach ($this->fieldPriority as $field) {
            if (isset($data[$field])) {
                $prioritized[$field] = $data[$field];
                unset($data[$field]);
            }
        }

        // Then add remaining fields alphabetically
        ksort($data);

        return array_merge($prioritized, $data);
    }

    /**
     * Sanitize a value for safe LLM consumption.
     *
     * @param  mixed  $value
     */
    protected function sanitizeValue($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '[]';
        }

        $stringValue = (string) $value;

        return Str::sanitizeForLLM($stringValue);
    }

    /**
     * Format a date value in a human-readable format.
     *
     * @param  mixed  $date
     */
    protected function formatDate($date): string
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return 'Not set';
        }

        try {
            $dateTime = CarbonImmutable::parse($date);

            return $dateTime->toIso8601String();
        } catch (\Exception $e) {
            return $this->sanitizeValue($date);
        }
    }

    /**
     * Format a priority value with appropriate visual indicators.
     *
     * @param  mixed  $priority
     */
    protected function formatPriority($priority): string
    {
        return match (strtolower((string) $priority)) {
            '1', 'low' => '🔵 Low',
            '2', 'medium', 'normal' => '🟡 Medium',
            '3', 'high' => '🟠 High',
            '4', 'urgent', 'critical' => '🔴 Urgent',
            default => $this->sanitizeValue($priority) ?: 'Not set'
        };
    }

    /**
     * Format a status with appropriate visual indicators.
     *
     * @param  mixed  $status
     * @param  array  $statusLabels  Optional status label mapping
     */
    protected function formatStatus($status, array $statusLabels = []): string
    {
        if (! empty($statusLabels) && isset($statusLabels[$status])) {
            $label = $statusLabels[$status];
            $statusType = $label['statusType'] ?? '';

            $emoji = match (strtolower($statusType)) {
                'new' => '🆕',
                'inprogress' => '🔄',
                'done' => '✅',
                default => '📝'
            };

            return $emoji.' '.$label['name'];
        }

        return $this->sanitizeValue($status) ?: 'Not set';
    }
}
