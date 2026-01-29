<?php

namespace Leantime\Domain\Wiki\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Audit\Repositories\Audit;
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

    private Audit $auditRepo;

    public function init(Wiki $wikiService, Audit $auditRepo): void
    {
        $this->wikiService = $wikiService;
        $this->auditRepo = $auditRepo;
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
        $article->parent = $existingArticle->parent;
        $article->tags = $tags !== null ? $tags : $existingArticle->tags;
        $article->data = $icon !== null ? $icon : $existingArticle->data;
        $article->status = $status !== null ? $status : $existingArticle->status;
        $article->milestoneId = $milestoneId !== null ? (int) ($milestoneId !== '' ? $milestoneId : 0) : $existingArticle->milestoneId;
        $article->parent = $parent !== null ? $parent : $existingArticle->parent;
        $article->sortindex = $existingArticle->sortindex;

        if ($this->wikiService->updateArticle($article)) {

            // Record audit events for changed fields
            $this->recordChanges($existingArticle, $article, $content, $title, $status, $icon, $tags, $milestoneId, $parent);

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
            // Record article creation in audit log
            $this->auditRepo->storeEvent(
                action: 'article.create',
                values: json_encode(['title' => $article->title]),
                entity: 'article',
                entityId: (int) $id,
                userId: (int) session('userdata.id'),
                projectId: (int) session('currentProject')
            );

            $response = new Response('', 200);
            $response->headers->set('HX-Redirect', BASE_URL.'/wiki/show/'.$id);

            return $response;
        }

        return new Response('Failed to create article', 500);
    }

    /**
     * Record audit events for changed fields.
     */
    private function recordChanges(
        object $existing,
        Article $updated,
        ?string $content,
        ?string $title,
        ?string $status,
        ?string $icon,
        ?string $tags,
        ?string $milestoneId,
        ?string $parent
    ): void {
        $userId = (int) session('userdata.id');
        $projectId = (int) session('currentProject');
        $articleId = (int) $updated->id;

        // Track specific field changes (not content - too noisy)
        if ($title !== null && $title !== $existing->title) {
            $this->auditRepo->storeEvent(
                action: 'article.title',
                values: json_encode(['from' => $existing->title, 'to' => $title]),
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }

        if ($status !== null && $status !== $existing->status) {
            $this->auditRepo->storeEvent(
                action: 'article.status',
                values: json_encode(['from' => $existing->status, 'to' => $status]),
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }

        if ($parent !== null && (int) $parent !== (int) $existing->parent) {
            $this->auditRepo->storeEvent(
                action: 'article.parent',
                values: json_encode(['from' => $existing->parent, 'to' => $parent]),
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }

        if ($milestoneId !== null && (int) ($milestoneId !== '' ? $milestoneId : 0) !== (int) $existing->milestoneId) {
            $this->auditRepo->storeEvent(
                action: 'article.milestone',
                values: json_encode(['from' => $existing->milestoneId, 'to' => $milestoneId]),
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }

        if ($icon !== null && $icon !== ($existing->data ?? '')) {
            $this->auditRepo->storeEvent(
                action: 'article.icon',
                values: json_encode(['from' => $existing->data, 'to' => $icon]),
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }

        if ($tags !== null && $tags !== ($existing->tags ?? '')) {
            $this->auditRepo->storeEvent(
                action: 'article.tags',
                values: json_encode(['from' => $existing->tags, 'to' => $tags]),
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }

        // Content edits - just record that it happened, not the diff
        if ($content !== null && $content !== $existing->description) {
            $this->auditRepo->storeEvent(
                action: 'article.edit',
                values: '',
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }
    }
}
