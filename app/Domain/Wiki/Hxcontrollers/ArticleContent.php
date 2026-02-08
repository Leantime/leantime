<?php

declare(strict_types=1);

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

        $articleId = (int) $this->incomingRequest->query->get('articleId', 0);
        $content = $this->incomingRequest->request->get('description');
        $title = $this->incomingRequest->request->get('title');
        $status = $this->incomingRequest->request->get('status');
        $icon = $this->incomingRequest->request->get('icon');
        $tags = $this->incomingRequest->request->get('tags');
        $milestoneId = $this->incomingRequest->request->get('milestoneId');
        $parent = $this->incomingRequest->request->get('parent');

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
        $article->tags = $tags !== null ? $tags : $existingArticle->tags;
        $article->data = $icon !== null ? $icon : $existingArticle->data;
        $article->status = $status !== null ? $status : $existingArticle->status;
        $article->milestoneId = $milestoneId !== null ? (int) ($milestoneId !== '' ? $milestoneId : 0) : $existingArticle->milestoneId;
        $article->parent = $parent !== null ? $parent : $existingArticle->parent;
        $article->sortindex = $existingArticle->sortindex;

        if ($this->wikiService->updateArticle($article, $existingArticle)) {

            return new Response(json_encode([
                'success' => true,
                'message' => 'Saved',
                'timestamp' => dtHelper()->userNow()->formatDateTimeForDb(),
                'title' => $article->title,
                'status' => $article->status,
            ], JSON_THROW_ON_ERROR), 200, ['Content-Type' => 'application/json']);
        }

        return new Response(json_encode([
            'success' => false,
            'message' => 'Failed to save',
        ], JSON_THROW_ON_ERROR), 500, ['Content-Type' => 'application/json']);
    }

    /**
     * Create a new article and redirect to it
     */
    public function create(): Response
    {
        // Check permissions
        if (! Auth::userIsAtLeast(Roles::$editor)) {
            return new Response('Unauthorized', 403);
        }

        $currentWiki = session('currentWiki');
        if (! $currentWiki) {
            return new Response('No wiki selected', 400);
        }

        // Create new article with defaults
        $article = new Article;
        $article->title = 'Untitled';
        $article->author = session('userdata.id');
        $article->canvasId = $currentWiki;
        $article->data = 'far fa-file-alt';
        $article->tags = '';
        $article->status = 'draft';
        $article->parent = 0;
        $article->description = '';

        $id = $this->wikiService->createArticle($article);

        if ($id) {
            $response = new Response('', 200);
            $response->headers->set('HX-Redirect', BASE_URL.'/wiki/show/'.$id);

            return $response;
        }

        return new Response('Failed to create article', 500);
    }
}
