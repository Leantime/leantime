<?php

namespace Leantime\Domain\Tickets\Models;

/**
 * Design tokens for ticket visualization
 * Centralizes priority, effort, type, and status mappings
 */
class TicketDesignTokens
{
    /**
     * Priority levels with labels and color mappings
     */
    public const PRIORITIES = [
        1 => [
            'label' => 'Critical',
            'cssVar' => '--priority-critical',
            'color' => '#C73E5C',  // Design spec
            'icon' => 'thermometer-full',
            'fill' => 1.0,  // Thermometer fill level (0.0-1.0)
        ],
        2 => [
            'label' => 'High',
            'cssVar' => '--priority-high',
            'color' => '#E85A5A',  // Design spec
            'icon' => 'thermometer-three-quarters',
            'fill' => 0.8,
        ],
        3 => [
            'label' => 'Medium',
            'cssVar' => '--priority-medium',
            'color' => '#F5A623',  // Design spec
            'icon' => 'thermometer-half',
            'fill' => 0.6,
        ],
        4 => [
            'label' => 'Low',
            'cssVar' => '--priority-low',
            'color' => '#2ECC71',  // Design spec
            'icon' => 'thermometer-quarter',
            'fill' => 0.4,
        ],
        5 => [
            'label' => 'Lowest',
            'cssVar' => '--priority-lowest',
            'color' => '#6B7280',  // Design spec
            'icon' => 'thermometer-empty',
            'fill' => 0.2,
        ],
    ];

    /**
     * Effort/Story points with labels and size mappings
     */
    public const EFFORTS = [
        0.5 => ['label' => '< 2min', 'size' => 'xxs', 'tshirtLabel' => 'XXS'],
        1 => ['label' => 'XS', 'size' => 'xs', 'tshirtLabel' => 'XS'],
        2 => ['label' => 'S', 'size' => 'sm', 'tshirtLabel' => 'S'],
        3 => ['label' => 'M', 'size' => 'md', 'tshirtLabel' => 'M'],
        5 => ['label' => 'L', 'size' => 'lg', 'tshirtLabel' => 'L'],
        8 => ['label' => 'XL', 'size' => 'xl', 'tshirtLabel' => 'XL'],
        13 => ['label' => 'XXL', 'size' => 'xxl', 'tshirtLabel' => 'XXL'],
    ];

    /**
     * Ticket types with emoji icons
     */
    public const TYPES = [
        'story' => ['label' => 'Story', 'icon' => '👤', 'materialIcon' => 'auto_stories'],
        'task' => ['label' => 'Task', 'icon' => '📋', 'materialIcon' => 'check_box'],
        'subtask' => ['label' => 'Subtask', 'icon' => '📋', 'materialIcon' => 'account_tree'],
        'bug' => ['label' => 'Bug', 'icon' => '🐛', 'materialIcon' => 'bug_report'],
        'feature' => ['label' => 'Feature', 'icon' => '✨', 'materialIcon' => 'star'],
        'epic' => ['label' => 'Epic', 'icon' => '🏔️', 'materialIcon' => 'terrain'],
        'documentation' => ['label' => 'Documentation', 'icon' => '📄', 'materialIcon' => 'description'],
        'improvement' => ['label' => 'Improvement', 'icon' => '🔧', 'materialIcon' => 'build'],
        'research' => ['label' => 'Research', 'icon' => '🔬', 'materialIcon' => 'science'],
    ];

    /**
     * Get priority token by ID
     *
     * @param  int  $id  Priority ID (1-5)
     * @return array|null Priority configuration array or null if not found
     */
    public static function getPriority(int $id): ?array
    {
        return self::PRIORITIES[$id] ?? null;
    }

    /**
     * Get effort token by points
     *
     * @param  float  $points  Story points value
     * @return array|null Effort configuration array or null if not found
     */
    public static function getEffort(float $points): ?array
    {
        return self::EFFORTS[$points] ?? null;
    }

    /**
     * Get type token by name
     *
     * @param  string  $type  Ticket type name
     * @return array|null Type configuration array or null if not found
     */
    public static function getType(string $type): ?array
    {
        return self::TYPES[$type] ?? null;
    }
}
