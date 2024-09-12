<?php

namespace Leantime\Domain\Reactions\Models;

use Leantime\Core\Events\DispatchesEvents;

class Reactions
{
    use DispatchesEvents;

    /**
     * @var array list of available reactions by reaction type
     */
    private static array $reactionTypes = [
        'sentimentReactions' => [
            'like' => '👍',
            'anger' => '😡',
            'love' => '❤',
            'support' => '💯',
            'celebrate' => '🎉',
            'interesting' => '💡',
            'sad' => '😥',
            'funny' => '😂',
        ],
        'contentReactions' => [
            'upvote' => "<i class='fa-solid fa-up'></i>",
            'downvote' => "<i class='fa-solid fa-down'></i>",
        ],
        'entityReactions' => [
            'favorite' => "<i class='fa fa-star'></i>",
            'watch' => "<i class='fa fa-eye'></i>",
        ],

    ];

    public static string $favorite = 'favorite';

    public static string $watch = 'watch';

    public static string $downvote = 'downvote';

    public static string $upvote = 'upvote';

    public static string $funny = 'funny';

    public static string $like = 'like';

    public static string $anger = 'anger';

    public static string $love = 'love';

    public static string $support = 'support';

    public static string $celebrate = 'celebrate';

    public static string $interesting = 'interesting';

    public static string $sad = 'sad';

    public static function getReactions(): mixed
    {
        return self::dispatch_filter('available_reactions', self::$reactionTypes);
    }

    /**
     * @return false|mixed
     */
    /**
     * @return false|mixed
     */
    public static function getReactionsByType(string $type): mixed
    {
        $reactions = self::dispatch_filter('available_reactions', self::$reactionTypes);

        return $reactions[$type] ?? false;
    }
}
