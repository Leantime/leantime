<?php

namespace Leantime\Domain\Wiki\Services;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Language;
use Leantime\Domain\Audit\Repositories\Audit as AuditRepository;
use Leantime\Domain\Wiki\Models\Article;
use Leantime\Domain\Wiki\Permissions\WikiPermissions;
use Leantime\Domain\Wiki\Repositories\Wiki as WikiRepository;

/**
 * @api
 */
class Wiki extends BaseService
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
     * The repository already filters by project, so this read is project-scoped: when a projectId
     * is passed it is authorized against (closing the explicit-foreign-project RPC read); when it is
     * omitted the call resolves to the caller's session project and can only read articles there.
     *
     * @api
     */
    #[RequiresPermission(WikiPermissions::VIEW, projectIdParam: 'projectId')]
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
    #[RequiresPermission(WikiPermissions::VIEW, projectIdParam: 'projectId')]
    public function getAllProjectWikis($projectId): array|false
    {

        $wikis = $this->wikiRepository->getAllProjectWikis($projectId);

        if (! $wikis || count($wikis) == 0) {

            $wiki = app()->make(\Leantime\Domain\Wiki\Models\Wiki::class);
            $wiki->title = $this->language->__('label.default');
            $wiki->projectId = $projectId;
            $wiki->author = session('userdata.id');

            // Bootstrap only: the default notebook is created as a SIDE EFFECT of viewing a
            // wiki-less project, so it must NOT go through the create-authorized createWiki() —
            // that would 403 a readonly viewer. Write straight to the repository (system action).
            $this->wikiRepository->createWiki($wiki);
            $wikis = $this->wikiRepository->getAllProjectWikis($projectId);
        }

        return $wikis;
    }

    /**
     * List the article headlines in a wiki.
     *
     * @api
     */
    #[RequiresPermission(WikiPermissions::VIEW, entityScoped: true)]
    public function getAllWikiHeadlines($wikiId, $userId): false|array
    {
        // IDOR fence: a foreign wikiId would otherwise leak another project's article titles.
        // Resolve the wiki's project and authorize VIEW there before listing.
        $wiki = $this->wikiRepository->getWiki((int) $wikiId);
        if (! $wiki) {
            return false;
        }

        $this->authorize(WikiPermissions::VIEW, (int) $wiki->projectId);

        return $this->wikiRepository->getAllWikiHeadlines($wikiId, $userId);
    }

    /**
     * Get a single wiki (notebook) by id.
     *
     * @api
     */
    #[RequiresPermission(WikiPermissions::VIEW, entityScoped: true)]
    public function getWiki($id): mixed
    {
        if ($id === null) {
            return false;
        }

        $wiki = $this->wikiRepository->getWiki((int) $id);

        if (! $wiki) {
            return false;
        }

        // IDOR fence: the id alone names any project's wiki. Authorize VIEW against the wiki's
        // ACTUAL project, not the session project — closes the cross-project read (and the
        // ?setWiki= session-switch footgun, which routes through here) on every call surface.
        $this->authorize(WikiPermissions::VIEW, (int) $wiki->projectId);

        return $wiki;
    }

    /**
     * @api
     */
    #[RequiresPermission(WikiPermissions::CREATE, entityScoped: true)]
    public function createWiki(\Leantime\Domain\Wiki\Models\Wiki $wiki): false|string
    {
        // Authorize CREATE against the target project before writing. An RPC caller could set any
        // projectId on the model, so the check is against that value (denied for non-members).
        $projectId = (int) ($wiki->projectId ?? session('currentProject'));
        $this->authorize(WikiPermissions::CREATE, $projectId);

        $wikiId = $this->wikiRepository->createWiki($wiki);

        $this->setCurrentWiki($wikiId);

        return $wikiId;
    }

    /**
     * @api
     */
    #[RequiresPermission(WikiPermissions::EDIT, entityScoped: true)]
    public function updateWiki(\Leantime\Domain\Wiki\Models\Wiki $wiki, $wikiId): bool
    {
        // IDOR fence: authorize EDIT against the EXISTING wiki's real project (the incoming model's
        // projectId is untrusted) before writing. FAIL CLOSED when the id is not a wiki — zp_canvas
        // is shared across canvas types (one id sequence), and getWiki filters type='wiki', so a
        // non-wiki id resolves to false; refuse rather than fall through to an unguarded title write
        // that would rename another project's canvas board.
        $existing = $this->wikiRepository->getWiki((int) $wikiId);
        if (! $existing) {
            return false;
        }

        $this->authorize(WikiPermissions::EDIT, (int) $existing->projectId);

        return $this->wikiRepository->updateWiki($wiki, $wikiId);
    }

    /**
     * Create a new article and record an audit event.
     *
     * @api
     */
    #[RequiresPermission(WikiPermissions::CREATE, entityScoped: true)]
    public function createArticle(Article $article): false|string
    {
        // An article inherits its wiki's project (canvasId -> zp_canvas.projectId). FAIL CLOSED if
        // the canvasId is not a wiki — never fall back to the session project, or a foreign/non-wiki
        // canvasId could be created against the caller's own project.
        $wiki = $this->wikiRepository->getWiki((int) $article->canvasId);
        if (! $wiki) {
            return false;
        }
        $projectId = (int) $wiki->projectId;
        $this->authorize(WikiPermissions::CREATE, $projectId);

        $id = $this->wikiRepository->createArticle($article);

        if ($id !== false) {
            $this->auditRepo->storeEvent(
                action: 'article.create',
                values: json_encode(['title' => $article->title], JSON_THROW_ON_ERROR),
                entity: 'article',
                entityId: (int) $id,
                userId: (int) session('userdata.id'),
                projectId: $projectId
            );
        }

        return $id;
    }

    /**
     * Update an article and record audit events for changed fields.
     *
     * @api
     */
    #[RequiresPermission(WikiPermissions::EDIT, entityScoped: true)]
    public function updateArticle(Article $article, ?Article $existingArticle = null): bool
    {
        // IDOR fence: resolve the EXISTING article's real project (by id) and authorize EDIT there
        // before writing. The incoming model's project/canvas are untrusted, so an editor in
        // project A cannot edit or relocate an article that lives in project B. FAIL CLOSED on an
        // unresolved project: zp_canvas_items is a shared table (one id sequence across ALL canvas
        // types), so a null here means the id is not an article — refuse rather than write a row we
        // could not authorize (a non-article id would otherwise overwrite a goal/SWOT/risk item).
        $projectId = $this->wikiRepository->getArticleProjectId((int) $article->id);
        if ($projectId === null) {
            return false;
        }

        $this->authorize(WikiPermissions::EDIT, $projectId);

        $result = $this->wikiRepository->updateArticle($article);

        if ($result && $existingArticle !== null) {
            $this->recordArticleChanges($existingArticle, $article);
        }

        return $result;
    }

    /**
     * Delete an article, fencing the operation against the article's project.
     *
     * @api
     */
    #[RequiresPermission(WikiPermissions::DELETE, entityScoped: true)]
    public function deleteArticle(int $id): bool
    {
        // IDOR fence: the id alone identified the row before (the controller called the repository
        // directly), so any editor could delete another project's article. Authorize DELETE against
        // the article's real project first.
        $projectId = $this->wikiRepository->getArticleProjectId($id);

        if ($projectId === null) {
            return false;
        }

        $this->authorize(WikiPermissions::DELETE, $projectId);

        $this->wikiRepository->delArticle($id);

        $this->auditRepo->storeEvent(
            action: 'article.delete',
            values: '',
            entity: 'article',
            entityId: $id,
            userId: (int) session('userdata.id'),
            projectId: $projectId
        );

        session()->forget('lastArticle');

        return true;
    }

    /**
     * Delete a wiki (notebook), fencing the operation against the wiki's project.
     *
     * @api
     */
    #[RequiresPermission(WikiPermissions::DELETE, entityScoped: true)]
    public function deleteWiki(int $id): bool
    {
        // IDOR fence: authorize DELETE against the wiki's real project before removing it.
        $wiki = $this->wikiRepository->getWiki($id);

        if (! $wiki) {
            return false;
        }

        $this->authorize(WikiPermissions::DELETE, (int) $wiki->projectId);

        $this->wikiRepository->delWiki($id);

        session()->forget('currentWiki');
        session()->forget('lastArticle');

        return true;
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
    #[RequiresPermission(WikiPermissions::VIEW, entityScoped: true)]
    public function getArticleActivity(int $articleId, int $limit = 20): array
    {
        // IDOR fence: a foreign articleId would otherwise leak another project's edit history
        // (audit values include old/new titles). Authorize VIEW against the article's real project.
        $projectId = $this->wikiRepository->getArticleProjectId($articleId);

        if ($projectId === null) {
            return [];
        }

        $this->authorize(WikiPermissions::VIEW, $projectId);

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
