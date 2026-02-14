<?php

namespace Leantime\Domain\Notifications\Models;

class Notification
{
    /**
     * Notification category to module value mapping.
     *
     * The 'boards' category is a catch-all for any module ending in 'canvas'
     * that is not 'goalcanvas'. Its array is empty because matching is done
     * via the getCategoryForModule() suffix check.
     *
     * @var array<string, array<string>>
     */
    public const NOTIFICATION_CATEGORIES = [
        'tasks' => ['tickets'],
        'comments' => ['comments'],
        'goals' => ['goalcanvas'],
        'ideas' => ['ideas'],
        'projects' => ['projects'],
        'boards' => [],
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
     * Maps a notification module value to its user-facing category key.
     *
     * @param  string  $module  The module value from the notification (e.g. 'tickets', 'comments', 'goalcanvas').
     * @return string|null The category key (e.g. 'tasks', 'goals', 'boards') or null if no match.
     */
    public static function getCategoryForModule(string $module): ?string
    {
        foreach (self::NOTIFICATION_CATEGORIES as $category => $modules) {
            if (in_array($module, $modules, true)) {
                return $category;
            }
        }

        // Catch-all: any module ending in 'canvas' that wasn't matched above falls into 'boards'
        if (str_ends_with($module, 'canvas')) {
            return 'boards';
        }

        return null;
    }
}
