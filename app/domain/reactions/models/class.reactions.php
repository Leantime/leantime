<?php

namespace leantime\domain\models;

use leantime\core\eventhelpers;

class reactions
{
    use eventhelpers;

    /**
     * @access public
     * @var    array $reactionTypes list of available reactions by reaction type
     */
    private static array $reactionTypes = array(
        "sentimentReactions" => array(
            "like" => "👍",
            "anger" => "😡",
            "love" => "❤",
            "support" => "💯",
            "celebrate" => "🎉",
            "interesting" => "💡",
            "sad" => "😥",
            "funny" => "😂",
        ),
        "contentReactions" => array(
            "upvote" => "<i class='fa-solid fa-up'></i>",
            "downvote" => "<i class='fa-solid fa-down'></i>",
        ),
        "entityReactions" => array(
            "favorite" => "<i class='fa fa-star'></i>",
            "watch" => "<i class='fa fa-eye'></i>",
        ),

    );

    public static string $favorite = 'favorite';
    public static string $watch = 'watch';
    public static string $downvote = 'downvote';
    public static string $upvote = 'upvote';
    public static string $funny = 'funny';

    public static string $like = 'like';
    public static string $anger = 'anger';
    public static string $love = 'love';
    public static $support = 'support';
    public static string $celebrate = 'celebrate';
    public static string $interesting = 'interesting';
    public static string $sad = 'sad';

    public static function getReactions()
    {
        return self::dispatch_filter('available_reactions', self::$reactionTypes);
    }

    public static function getReactionsByType(string $type)
    {
        $reactions = self::dispatch_filter('available_reactions', self::$reactionTypes);

        return $reactions[$type] ?? false;
    }
}
