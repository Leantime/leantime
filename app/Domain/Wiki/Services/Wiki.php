<?php

namespace Leantime\Domain\Wiki\Services;

use Leantime\Core\Language;
use Leantime\Domain\Audit\Repositories\Audit as AuditRepository;
use Leantime\Domain\Wiki\Models\Article;
use Leantime\Domain\Wiki\Repositories\Wiki as WikiRepository;

/**
 * @api
 */
class Wiki
{
    private WikiRepository $wikiRepository;

    private Language $language;

    private AuditRepository $auditRepo;

    public function __construct(
        WikiRepository $wikiRepository,
        Language $language,
        AuditRepository $auditRepo
    ) {
        $this->wikiRepository = $wikiRepository;
        $this->language = $language;
        $this->auditRepo = $auditRepo;
    }

    /**
     * Get an article by ID and project.
     *
     * @api
     */
    public function getArticle(?int $id, ?int $projectId = null): mixed
    {

        if ($projectId === null) {
            $projectId = session('currentProject');
        }

        if (! is_null($id)) {
            $article = $this->wikiRepository->getArticle($id, $projectId);

            if (! $article) {
                $article = $this->wikiRepository->getArticle(-1, $projectId);
            }
        } else {
            $article = $this->wikiRepository->getArticle(-1, $projectId);
        }

        return $article;
    }

    /**
     * Gets all project wikis. Creates one if there aren't any
     *
     *
     * @api
     */
    public function getAllProjectWikis($projectId): array|false
    {

        $wikis = $this->wikiRepository->getAllProjectWikis($projectId);

        if (! $wikis || count($wikis) == 0) {

            $wiki = app()->make(\Leantime\Domain\Wiki\Models\Wiki::class);
            $wiki->title = $this->language->__('label.default');
            $wiki->projectId = $projectId;
            $wiki->author = session('userdata.id');

            $id = $this->createWiki($wiki);
            $wikis = $this->wikiRepository->getAllProjectWikis($projectId);
        }

        return $wikis;
    }

    /**
     * @api
     */
    public function getAllWikiHeadlines($wikiId, $userId): false|array
    {
        return $this->wikiRepository->getAllWikiHeadlines($wikiId, $userId);
    }

    /**
     * @api
     */
    public function getWiki($id): mixed
    {
        if ($id === null) {
            return false;
        }

        return $this->wikiRepository->getWiki((int) $id);
    }

    /**
     * @api
     */
    public function createWiki(\Leantime\Domain\Wiki\Models\Wiki $wiki): false|string
    {

        $wikiId = $this->wikiRepository->createWiki($wiki);

        $this->setCurrentWiki($wikiId);

        return $wikiId;
    }

    /**
     * @api
     */
    public function updateWiki(\Leantime\Domain\Wiki\Models\Wiki $wiki, $wikiId): bool
    {
        return $this->wikiRepository->updateWiki($wiki, $wikiId);
    }

    /**
     * Create a new article and record an audit event.
     *
     * @api
     */
    public function createArticle(Article $article): false|string
    {
        $id = $this->wikiRepository->createArticle($article);

        if ($id !== false) {
            $this->auditRepo->storeEvent(
                action: 'article.create',
                values: json_encode(['title' => $article->title], JSON_THROW_ON_ERROR),
                entity: 'article',
                entityId: (int) $id,
                userId: (int) session('userdata.id'),
                projectId: (int) session('currentProject')
            );
        }

        return $id;
    }

    /**
     * Update an article and record audit events for changed fields.
     *
     * @api
     */
    public function updateArticle(Article $article, ?Article $existingArticle = null): bool
    {
        $result = $this->wikiRepository->updateArticle($article);

        if ($result && $existingArticle !== null) {
            $this->recordArticleChanges($existingArticle, $article);
        }

        return $result;
    }

    public function setCurrentWiki($id)
    {

        // Clear cache
        $this->clearWikiCache();
        $wiki = $this->getWiki($id);

        if ($wiki) {
            // Set the session
            session(['currentWiki' => $id]);

            return true;
        }

        return false;

    }

    public function setCurrentArticle($id, $userId)
    {

        $currentArticle = $this->getArticle($id);

        if ($currentArticle && $currentArticle->id != null) {
            session(['currentWiki' => $currentArticle->canvasId]);
            session(['lastArticle' => $currentArticle->id]);

            return $currentArticle;
        }

        return false;

    }

    public function getDefaultArticleForWiki($wikiId, $userId)
    {

        $wikiHeadlines = $this->getAllWikiHeadlines(
            $wikiId,
            $userId
        );

        if (is_array($wikiHeadlines) && count($wikiHeadlines) > 0) {
            $currentArticle = $this->getArticle(
                $wikiHeadlines[0]->id
            );

            return $currentArticle;
        }

        return false;

    }

    /**
     * Get combined activity feed for an article (audit events + comments).
     *
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getArticleActivity(int $articleId, int $limit = 20): array
    {
        $activity = [];

        // Get audit events for this article
        $auditEvents = $this->auditRepo->getEventsForEntity('article', $articleId, $limit);
        foreach ($auditEvents as $event) {
            $decoded = ! empty($event['values']) ? json_decode($event['values'], true) : [];
            $values = is_array($decoded) ? $decoded : [];

            $activity[] = [
                'type' => 'audit',
                'action' => $event['action'] ?? '',
                'date' => $event['date'] ?? '',
                'firstname' => $event['firstname'] ?? '',
                'lastname' => $event['lastname'] ?? '',
                'profileId' => $event['profileId'] ?? '',
                'values' => $values,
            ];
        }

        return array_slice($activity, 0, $limit);
    }

    /**
     * Record audit events for changed fields between existing and updated articles.
     */
    private function recordArticleChanges(Article $existing, Article $updated): void
    {
        $userId = (int) session('userdata.id');
        $projectId = (int) session('currentProject');
        $articleId = (int) $updated->id;

        if ($updated->title !== $existing->title) {
            $this->auditRepo->storeEvent(
                action: 'article.title',
                values: json_encode(['from' => $existing->title, 'to' => $updated->title], JSON_THROW_ON_ERROR),
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }

        if ($updated->status !== $existing->status) {
            $this->auditRepo->storeEvent(
                action: 'article.status',
                values: json_encode(['from' => $existing->status, 'to' => $updated->status], JSON_THROW_ON_ERROR),
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }

        if ((int) $updated->parent !== (int) $existing->parent) {
            $this->auditRepo->storeEvent(
                action: 'article.parent',
                values: json_encode(['from' => $existing->parent, 'to' => $updated->parent], JSON_THROW_ON_ERROR),
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }

        if ((int) $updated->milestoneId !== (int) $existing->milestoneId) {
            $this->auditRepo->storeEvent(
                action: 'article.milestone',
                values: json_encode(['from' => $existing->milestoneId, 'to' => $updated->milestoneId], JSON_THROW_ON_ERROR),
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }

        if ($updated->data !== ($existing->data ?? '')) {
            $this->auditRepo->storeEvent(
                action: 'article.icon',
                values: json_encode(['from' => $existing->data, 'to' => $updated->data], JSON_THROW_ON_ERROR),
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }

        if ($updated->tags !== ($existing->tags ?? '')) {
            $this->auditRepo->storeEvent(
                action: 'article.tags',
                values: json_encode(['from' => $existing->tags, 'to' => $updated->tags], JSON_THROW_ON_ERROR),
                entity: 'article',
                entityId: $articleId,
                userId: $userId,
                projectId: $projectId
            );
        }

        // Content edits - just record that it happened, not the diff
        if ($updated->description !== $existing->description) {
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

    public function clearWikiCache()
    {

        session()->forget('lastArticle');
        session()->forget('currentWiki');

    }
}
