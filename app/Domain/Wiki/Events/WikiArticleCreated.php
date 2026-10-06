<?php

namespace Leantime\Domain\Wiki\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a wiki article was created.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class WikiArticleCreated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $articleId  The id of the new article.
     * @param  int|null  $projectId  The wiki's project id, when known.
     */
    public function __construct(
        public readonly int $articleId,
        public readonly ?int $projectId,
    ) {}
}
