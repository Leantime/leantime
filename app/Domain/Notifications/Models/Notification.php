<?php

namespace Leantime\Domain\Notifications\Models;

class Notification
{
    /**
     * Per-project notification relevance levels.
     *
     * Controls how much activity a user sees from a given project.
     */
    public const RELEVANCE_ALL = 'all';

    public const RELEVANCE_MY_WORK = 'my_work';

    public const RELEVANCE_MUTED = 'muted';

    /**
     * Available relevance levels with their label keys.
     *
     * @var array<string, string>
     */
    public const RELEVANCE_LEVELS = [
        self::RELEVANCE_ALL => 'label.notifications_all_activity',
        self::RELEVANCE_MY_WORK => 'label.notifications_my_work',
        self::RELEVANCE_MUTED => 'label.notifications_muted',
    ];

    /**
     * Notification category definitions.
     *
     * Each category maps to one or more module values and includes a
     * user-facing description key. The 'boards' category is a catch-all
     * for any module ending in 'canvas' that is not matched by another
     * category -- its modules array is empty because matching is done
     * via the getCategoryForModule() suffix check.
     *
     * @var array<string, array{modules: array<string>, description: string}>
     */
    public const NOTIFICATION_CATEGORIES = [
        'tasks' => [
            'modules' => ['tickets'],
            'description' => 'label.notification_category_tasks_description',
        ],
        'comments' => [
            'modules' => ['comments'],
            'description' => 'label.notification_category_comments_description',
        ],
        'goals' => [
            'modules' => ['goalcanvas'],
            'description' => 'label.notification_category_goals_description',
        ],
        'ideas' => [
            'modules' => ['ideas'],
            'description' => 'label.notification_category_ideas_description',
        ],
        'projects' => [
            'modules' => ['projects'],
            'description' => 'label.notification_category_projects_description',
        ],
        'boards' => [
            'modules' => [],
            'description' => 'label.notification_category_boards_description',
        ],
    ];

    public int $id;

    public string $message;

    public string $subject;

    public int $projectId;

    public int $authorId;

    public bool|array $url;

    public mixed $entity;

    public string $module;

    /**
     * The action that triggered this notification.
     *
     * Stores the verb (e.g. 'created', 'updated', 'commented', 'status_changed', 'assigned')
     * for future action-level filtering. Currently informational only.
     */
    public string $action = '';

    /**
     * Maps a notification module value to its user-facing category key.
     *
     * @param  string  $module  The module value from the notification (e.g. 'tickets', 'comments', 'goalcanvas').
     * @return string|null The category key (e.g. 'tasks', 'goals', 'boards') or null if no match.
     */
    public static function getCategoryForModule(string $module): ?string
    {
        foreach (self::NOTIFICATION_CATEGORIES as $category => $config) {
            if (in_array($module, $config['modules'], true)) {
                return $category;
            }
        }

        // Catch-all: any module ending in 'canvas' that wasn't matched above falls into 'boards'
        if (str_ends_with($module, 'canvas')) {
            return 'boards';
        }

        return null;
    }

    /**
     * Returns only the category keys, for use in validation and iteration.
     *
     * @return array<string>
     */
    public static function getCategoryKeys(): array
    {
        return array_keys(self::NOTIFICATION_CATEGORIES);
    }

    /**
     * Validates that a relevance level string is valid.
     *
     * @param  string  $level  The level to validate.
     * @return bool True if valid.
     */
    public static function isValidRelevanceLevel(string $level): bool
    {
        return array_key_exists($level, self::RELEVANCE_LEVELS);
    }
}
