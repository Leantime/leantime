<?php

namespace Leantime\Domain\Wiki\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Wiki\Services\Wiki;

/**
 * HTMX Controller for wiki article activity feed
 */
class ArticleActivity extends HtmxController
{
    protected static string $view = 'wiki::partials.activityFeed';

    private Wiki $wikiService;

    public function init(Wiki $wikiService): void
    {
        $this->wikiService = $wikiService;
    }

    /**
     * Get the activity feed for an article.
     */
    public function get(): void
    {
        $articleId = (int) $this->incomingRequest->query->get('articleId', 0);

        if ($articleId <= 0) {
            $this->tpl->assign('activity', []);
            $this->tpl->assign('articleId', 0);

            return;
        }

        // Get the article for created/modified fallback
        $article = $this->wikiService->getArticle($articleId);

        $activity = $this->wikiService->getArticleActivity($articleId, 20);

        // Always append the article's created date as the final entry
        if ($article && ! empty($article->created)) {
            $activity[] = [
                'type' => 'baseline',
                'action' => 'article.create',
                'date' => $article->created,
                'firstname' => $article->firstname ?? '',
                'lastname' => $article->lastname ?? '',
                'profileId' => $article->profileId ?? '',
                'values' => [],
            ];
        }

        $this->tpl->assign('activity', $activity);
        $this->tpl->assign('articleId', $articleId);
    }
}
