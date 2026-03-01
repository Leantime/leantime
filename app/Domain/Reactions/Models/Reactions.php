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
            'upvote' => "<span class='material-symbols-outlined'>arrow_upward</span>",
            'downvote' => "<span class='material-symbols-outlined'>arrow_downward</span>",
        ],
        'entityReactions' => [
            'favorite' => "<span class='material-symbols-outlined'>star</span>",
            'watch' => "<span class='material-symbols-outlined'>visibility</span>",
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
