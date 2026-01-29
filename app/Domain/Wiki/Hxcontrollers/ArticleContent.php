<?php

namespace Leantime\Domain\Wiki\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Wiki\Models\Article;
use Leantime\Domain\Wiki\Services\Wiki;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTMX Controller for inline wiki article content editing
 */
class ArticleContent extends HtmxController
{
    protected static string $view = 'wiki::partials.articleContent';

    private Wiki $wikiService;

    public function init(Wiki $wikiService): void
    {
        $this->wikiService = $wikiService;
    }

    /**
     * Save article content via HTMX (called on auto-save or blur)
     */
    public function save(): Response
    {
        // Check permissions
        if (! Auth::userIsAtLeast(Roles::$editor)) {
            return new Response('Unauthorized', 403);
        }

        $articleId = $this->incomingRequest->query->get('articleId');
        $content = $this->incomingRequest->request->get('description');
        $title = $this->incomingRequest->request->get('title');
        $status = $this->incomingRequest->request->get('status');
        $icon = $this->incomingRequest->request->get('icon');
        $tags = $this->incomingRequest->request->get('tags');
        $milestoneId = $this->incomingRequest->request->get('milestoneId');

        if (! $articleId) {
            return new Response('Article ID required', 400);
        }

        // Get the existing article
        $existingArticle = $this->wikiService->getArticle($articleId);

        if (! $existingArticle) {
            return new Response('Article not found', 404);
        }

        // Create article model with updated fields
        $article = new Article;
        $article->id = $articleId;
        $article->title = $title !== null ? $title : $existingArticle->title;
        $article->description = $content !== null ? $content : $existingArticle->description;
        $article->canvasId = $existingArticle->canvasId;
        $article->parent = $existingArticle->parent;
        $article->tags = $tags !== null ? $tags : $existingArticle->tags;
        $article->data = $icon !== null ? $icon : $existingArticle->data;
        $article->status = $status !== null ? $status : $existingArticle->status;
        $article->milestoneId = $milestoneId !== null ? $milestoneId : $existingArticle->milestoneId;
        $article->sortindex = $existingArticle->sortindex;

        if ($this->wikiService->updateArticle($article)) {
            // Return a simple success response for auto-save
            return new Response(json_encode([
                'success' => true,
                'message' => 'Saved',
                'timestamp' => date('Y-m-d H:i:s'),
                'title' => $article->title,
                'status' => $article->status,
            ]), 200, ['Content-Type' => 'application/json']);
        }

        return new Response(json_encode([
            'success' => false,
            'message' => 'Failed to save',
        ]), 500, ['Content-Type' => 'application/json']);
    }
}
