<?php

namespace Leantime\Domain\Wiki\Services;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Language;
use Leantime\Domain\Audit\Repositories\Audit as AuditRepository;
use Leantime\Domain\Wiki\Events\WikiArticleCreated;
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
     * A draft belongs to its author until it is published: another user's draft is not found
     * (false), the same rule getAllWikiHeadlines() applies to the Contents list. A null or unknown
     * id falls back to the project's featured article, under the same rule.
     *
     * @param  int|null  $id  Article id (null loads the featured article)
     * @param  int|null  $projectId  Project id (defaults to the session project)
     * @return Article|false The article, or false when it does not exist or is not visible
     *
     * @api
     */
    #[RequiresPermission(WikiPermissions::VIEW, projectIdParam: 'projectId')]
    public function getArticle(?int $id, ?int $projectId = null): Article|false
    {
        if ($projectId === null) {
            $projectId = session('currentProject');
        }

        if ($id !== null) {
            $article = $this->wikiRepository->getArticle($id, $projectId);

            // Found but hidden: report "not found" and do NOT fall back to another article, so a
            // caller that writes back by id (inline save) never mixes a different article's fields
            // into this draft.
            if ($article) {
                return $this->isVisibleToCurrentUser($article) ? $article : false;
            }
        }

        $featuredArticle = $this->wikiRepository->getArticle(-1, $projectId);

        if (! $featuredArticle || ! $this->isVisibleToCurrentUser($featuredArticle)) {
            return false;
        }

        return $featuredArticle;
    }

    /**
     * Whether the current user may see an article. Drafts are visible to their author only, until
     * published; everything else is visible to anyone who can view the project.
     *
     * Admins and owners get no exception, matching getAllWikiHeadlines() and search, which list
     * other users' drafts to nobody. Only the literal 'draft' status is private: articles created
     * from content templates before they got a status have none and are readable.
     *
     * @param  Article  $article  The loaded article
     */
    private function isVisibleToCurrentUser(Article $article): bool
    {
        if ($article->status !== 'draft') {
            return true;
        }

        return (int) $article->author === (int) session('userdata.id');
    }

    /**
     * Whether the current user may see the article with this id, under the draft rule of
     * getArticle(). Lets other domains (comments) hide what hangs off another user's draft.
     *
     * Not an API method and no permission check of its own: callers authorize against the
     * article's project themselves.
     *
     * @param  int  $articleId  Article id
     * @return bool False for an unknown id and for another user's draft
     */
    public function isArticleVisibleToCurrentUser(int $articleId): bool
    {
        return $this->loadVisibleArticle($articleId) !== null;
    }

    /**
     * Loads an article by id from its own project, or null when the id names no article or the
     * article is another user's draft.
     *
     * @param  int  $articleId  Article id
     */
    private function loadVisibleArticle(int $articleId): ?Article
    {
        $projectId = $this->wikiRepository->getArticleProjectId($articleId);
        if ($projectId === null) {
            return null;
        }

        $article = $this->wikiRepository->getArticle($articleId, $projectId);
        if (! $article instanceof Article || ! $this->isVisibleToCurrentUser($article)) {
            return null;
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

        if (! $wikis) {

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
            WikiArticleCreated::dispatch(articleId: (int) $id, projectId: $projectId);

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
     * Another user's draft is refused (false), like an unknown id: drafts belong to their author.
     *
     * @param  Article  $article  The article with its new values (id required)
     * @param  Article|null  $existingArticle  The article before the change, for the audit trail
     * @return bool True when the article was saved
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

        // Another user's draft is not found for this user (getArticle()), so it cannot be
        // overwritten either, whatever the caller sends.
        if ($this->loadVisibleArticle((int) $article->id) === null) {
            return false;
        }

        $result = $this->wikiRepository->updateArticle($article);

        if ($result && $existingArticle !== null) {
            $this->recordArticleChanges($existingArticle, $article);
        }

        return $result;
    }

    /**
     * Resolves the project an article belongs to and authorizes VIEW there, so callers can
     * check further permissions against the article's real project instead of the session one.
     *
     * @param  int  $id  Article id
     * @return int|null The article's project id, or null when the id names no article
     */
    public function getArticleProjectId(int $id): ?int
    {
        $projectId = $this->wikiRepository->getArticleProjectId($id);

        if ($projectId === null) {
            return null;
        }

        $this->authorize(WikiPermissions::VIEW, $projectId);

        return $projectId;
    }

    /**
     * Delete an article, fencing the operation against the article's project.
     *
     * Another user's draft is refused (false), like an unknown id: drafts belong to their author.
     *
     * @param  int  $id  Article id
     * @return bool True when the article was deleted
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

        // Another user's draft is not found for this user, so it cannot be deleted either.
        if ($this->loadVisibleArticle($id) === null) {
            return false;
        }

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
     * Empty for an unknown id and for another user's draft (drafts are author-only, see getArticle()).
     *
     * @param  int  $articleId  Article id
     * @param  int  $limit  Maximum number of entries
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

        // Another user's draft has no activity for this user: its edit history (titles, status
        // changes) is as private as the draft itself.
        if ($this->loadVisibleArticle($articleId) === null) {
            return [];
        }

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
