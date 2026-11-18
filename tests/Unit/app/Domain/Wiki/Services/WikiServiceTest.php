<?php

namespace Unit\app\Domain\Wiki\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language;
use Leantime\Domain\Audit\Repositories\Audit as AuditRepository;
use Leantime\Domain\Wiki\Models\Article;
use Leantime\Domain\Wiki\Models\Wiki as WikiModel;
use Leantime\Domain\Wiki\Repositories\Wiki as WikiRepository;
use Leantime\Domain\Wiki\Services\Wiki as WikiService;
use Unit\TestCase;

/**
 * Unit tests for the Wiki service's project-scoped authorization. Wiki articles and notebooks are
 * project-scoped; mutations and single-entity reads authorize against the entity's REAL project
 * (entityScoped), closing the IDORs where the id alone identified the row.
 */
class WikiServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private function makeService(
        ?WikiRepository $wikiRepo = null,
        ?AuditRepository $auditRepo = null,
    ): WikiService {
        return new WikiService(
            $wikiRepo ?? $this->make(WikiRepository::class),
            $this->make(Language::class),
            $auditRepo ?? $this->make(AuditRepository::class),
        );
    }

    private function allowingPermissions(): PermissionService
    {
        return $this->make(PermissionService::class, ['authorize' => fn () => null]);
    }

    private function denyingPermissions(): PermissionService
    {
        return $this->make(PermissionService::class, [
            'authorize' => function (): void {
                throw new AuthorizationException;
            },
        ]);
    }

    // ---------------------------------------------------------------------
    // Reads: single-entity-by-id reads fence against the entity's project.
    // ---------------------------------------------------------------------

    public function test_get_wiki_is_denied_when_user_cannot_view_its_project(): void
    {
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getWiki' => fn () => $this->make(WikiModel::class, ['id' => 3, 'projectId' => 9]),
        ]));
        $service->setPermissionService($this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->getWiki(3);
    }

    public function test_get_wiki_returns_false_for_unknown_id_without_authorizing(): void
    {
        // A missing wiki short-circuits to false BEFORE authorize — no enumeration oracle.
        $authorizeCalls = 0;
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getWiki' => fn () => false,
        ]));
        $service->setPermissionService($this->make(PermissionService::class, [
            'authorize' => function () use (&$authorizeCalls): void {
                $authorizeCalls++;
            },
        ]));

        $this->assertFalse($service->getWiki(999));
        $this->assertSame(0, $authorizeCalls, 'A non-existent wiki must short-circuit before authorize');
    }

    public function test_get_all_wiki_headlines_is_denied_for_foreign_wiki(): void
    {
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getWiki' => fn () => $this->make(WikiModel::class, ['id' => 3, 'projectId' => 9]),
        ]));
        $service->setPermissionService($this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->getAllWikiHeadlines(3, 1);
    }

    // ---------------------------------------------------------------------
    // Drafts (#4056): only the author can open a draft until it is published.
    // ---------------------------------------------------------------------

    private function article(int $id, string $status, int $author): Article
    {
        $article = new Article;
        $article->id = $id;
        $article->status = $status;
        $article->author = $author;
        $article->projectId = 9;
        $article->created = '2026-11-01 10:00:00';

        return $article;
    }

    /**
     * A repository that holds one article (id 42) and no featured article.
     */
    private function repoWithArticle(Article $article): WikiRepository
    {
        return $this->make(WikiRepository::class, [
            'getArticle' => fn (int $id) => $id === (int) $article->id ? $article : false,
            'getArticleProjectId' => fn () => 9,
        ]);
    }

    private function serviceWithAuditEvents(Article $article): WikiService
    {
        $service = $this->makeService(
            wikiRepo: $this->repoWithArticle($article),
            auditRepo: $this->make(AuditRepository::class, [
                'getEventsForEntity' => fn () => [
                    ['action' => 'article.title', 'date' => '2026-11-02 10:00:00', 'values' => '{"from":"a","to":"b"}'],
                ],
            ]),
        );
        $service->setPermissionService($this->allowingPermissions());

        return $service;
    }

    public function test_get_article_hides_another_users_draft(): void
    {
        session(['userdata.id' => 7, 'currentProject' => 9]);
        $service = $this->makeService(wikiRepo: $this->repoWithArticle($this->article(42, 'draft', 3)));
        $service->setPermissionService($this->allowingPermissions());

        $this->assertFalse($service->getArticle(42, 9));
        $this->assertFalse($service->setCurrentArticle(42, 7), 'The page (wiki/show) must not open it either');
    }

    public function test_get_article_returns_own_draft_to_its_author(): void
    {
        session(['userdata.id' => 3]);
        $draft = $this->article(42, 'draft', 3);
        $service = $this->makeService(wikiRepo: $this->repoWithArticle($draft));
        $service->setPermissionService($this->allowingPermissions());

        $this->assertSame($draft, $service->getArticle(42, 9));
    }

    public function test_get_article_returns_published_article_to_everyone(): void
    {
        session(['userdata.id' => 7]);
        $published = $this->article(42, 'published', 3);
        $service = $this->makeService(wikiRepo: $this->repoWithArticle($published));
        $service->setPermissionService($this->allowingPermissions());

        $this->assertSame($published, $service->getArticle(42, 9));
    }

    public function test_get_article_activity_is_empty_for_another_users_draft(): void
    {
        session(['userdata.id' => 7]);
        $service = $this->serviceWithAuditEvents($this->article(42, 'draft', 3));

        $this->assertSame([], $service->getArticleActivity(42));
    }

    public function test_get_article_activity_is_returned_for_own_draft_and_published_articles(): void
    {
        session(['userdata.id' => 3]);
        $ownDraftActivity = $this->serviceWithAuditEvents($this->article(42, 'draft', 3))->getArticleActivity(42);

        session(['userdata.id' => 7]);
        $publishedActivity = $this->serviceWithAuditEvents($this->article(42, 'published', 3))->getArticleActivity(42);

        $this->assertCount(1, $ownDraftActivity);
        $this->assertCount(1, $publishedActivity);
        $this->assertSame('article.title', $publishedActivity[0]['action']);
    }

    // ---------------------------------------------------------------------
    // Mutations: authorize against the entity's real project before writing.
    // ---------------------------------------------------------------------

    public function test_create_article_is_denied_without_create_permission(): void
    {
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            // createArticle resolves the project from the target wiki (canvasId) to authorize.
            'getWiki' => fn () => $this->make(WikiModel::class, ['id' => 3, 'projectId' => 9]),
            'createArticle' => function () {
                throw new \RuntimeException('create must not be reached when denied');
            },
        ]));
        $service->setPermissionService($this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $article = new Article;
        $article->canvasId = 3;
        $service->createArticle($article);
    }

    public function test_create_article_fails_closed_when_canvas_is_not_a_wiki(): void
    {
        // canvasId does not resolve to a wiki -> refuse before authorize, never write (no falling
        // back to the session project, which would let a foreign/non-wiki canvasId be created).
        $created = false;
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getWiki' => fn () => false,
            'createArticle' => function () use (&$created) {
                $created = true;

                return '1';
            },
        ]));
        $service->setPermissionService($this->allowingPermissions());

        $article = new Article;
        $article->canvasId = 999;

        $this->assertFalse($service->createArticle($article));
        $this->assertFalse($created, 'A non-wiki canvasId must never create an article');
    }

    public function test_update_article_is_denied_without_edit_permission(): void
    {
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getArticleProjectId' => fn () => 9,
            'updateArticle' => function () {
                throw new \RuntimeException('update must not be reached when denied');
            },
        ]));
        $service->setPermissionService($this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $article = new Article;
        $article->id = 42;
        $service->updateArticle($article);
    }

    public function test_create_wiki_is_denied_without_create_permission(): void
    {
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'createWiki' => function () {
                throw new \RuntimeException('create must not be reached when denied');
            },
        ]));
        $service->setPermissionService($this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $wiki = new WikiModel;
        $wiki->projectId = 9;
        $service->createWiki($wiki);
    }

    public function test_update_article_returns_false_for_unknown_id_without_authorizing(): void
    {
        // FAIL CLOSED: zp_canvas_items is a shared table (one id sequence across all canvas types),
        // so an unresolved project (non-article / unknown id) must refuse BEFORE authorize and never
        // reach the repo write — otherwise a non-article id would overwrite a goal/SWOT/risk row.
        $authorizeCalls = 0;
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getArticleProjectId' => fn () => null,
            'updateArticle' => function (): bool {
                throw new \RuntimeException('update must not run for an unresolved/non-article id');
            },
        ]));
        $service->setPermissionService($this->make(PermissionService::class, [
            'authorize' => function () use (&$authorizeCalls): void {
                $authorizeCalls++;
            },
        ]));

        $article = new Article;
        $article->id = 999;

        $this->assertFalse($service->updateArticle($article));
        $this->assertSame(0, $authorizeCalls, 'A non-article id must short-circuit before authorize');
    }

    public function test_update_wiki_returns_false_for_unknown_wiki_without_authorizing(): void
    {
        // FAIL CLOSED: zp_canvas is shared across canvas types, so a non-wiki / unknown id must
        // refuse BEFORE authorize and never reach the repo title write.
        $authorizeCalls = 0;
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getWiki' => fn () => false,
            'updateWiki' => function (): bool {
                throw new \RuntimeException('update must not run for a non-wiki id');
            },
        ]));
        $service->setPermissionService($this->make(PermissionService::class, [
            'authorize' => function () use (&$authorizeCalls): void {
                $authorizeCalls++;
            },
        ]));

        $wiki = new WikiModel;

        $this->assertFalse($service->updateWiki($wiki, 999));
        $this->assertSame(0, $authorizeCalls, 'A non-wiki id must short-circuit before authorize');
    }

    // ---------------------------------------------------------------------
    // Delete: new service methods that fence the previously controller->repo IDOR.
    // ---------------------------------------------------------------------

    public function test_delete_article_is_denied_and_does_not_delete_without_permission(): void
    {
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getArticleProjectId' => fn () => 9,
            'delArticle' => function (): void {
                throw new \RuntimeException('delete must not be reached when denied');
            },
        ]));
        $service->setPermissionService($this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->deleteArticle(42);
    }

    public function test_delete_article_deletes_and_audits_when_authorized(): void
    {
        $deletedId = null;
        $auditedAction = null;

        $service = $this->makeService(
            wikiRepo: $this->make(WikiRepository::class, [
                'getArticleProjectId' => fn () => 9,
                'getArticle' => fn () => $this->article(42, 'published', 3),
                'delArticle' => function ($id) use (&$deletedId): void {
                    $deletedId = $id;
                },
            ]),
            auditRepo: $this->make(AuditRepository::class, [
                // Accept the full storeEvent signature (variadic) — deleteArticle calls it with
                // several named args; only the action is asserted.
                'storeEvent' => function (string $action, ...$rest) use (&$auditedAction) {
                    $auditedAction = $action;
                },
            ]),
        );
        $service->setPermissionService($this->allowingPermissions());

        $this->assertTrue($service->deleteArticle(42));
        $this->assertSame(42, $deletedId);
        $this->assertSame('article.delete', $auditedAction);
    }

    public function test_delete_article_returns_false_for_unknown_id_without_authorizing(): void
    {
        $authorizeCalls = 0;
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getArticleProjectId' => fn () => null,
            'delArticle' => function (): void {
                throw new \RuntimeException('delete must not run for a non-existent article');
            },
        ]));
        $service->setPermissionService($this->make(PermissionService::class, [
            'authorize' => function () use (&$authorizeCalls): void {
                $authorizeCalls++;
            },
        ]));

        $this->assertFalse($service->deleteArticle(999));
        $this->assertSame(0, $authorizeCalls);
    }

    public function test_delete_wiki_is_denied_without_permission(): void
    {
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getWiki' => fn () => $this->make(WikiModel::class, ['id' => 3, 'projectId' => 9]),
            'delWiki' => function (): void {
                throw new \RuntimeException('delete must not be reached when denied');
            },
        ]));
        $service->setPermissionService($this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->deleteWiki(3);
    }

    public function test_get_article_project_id_authorizes_view_against_the_articles_own_project(): void
    {
        $authorizedProjectId = null;
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getArticleProjectId' => fn () => 9,
        ]));
        $service->setPermissionService($this->make(PermissionService::class, [
            'authorize' => function ($permission, $projectId = null) use (&$authorizedProjectId): void {
                $authorizedProjectId = $projectId;
            },
        ]));

        $this->assertSame(9, $service->getArticleProjectId(42));
        $this->assertSame(9, $authorizedProjectId);
    }

    public function test_get_article_project_id_is_denied_when_user_cannot_view_the_project(): void
    {
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getArticleProjectId' => fn () => 9,
        ]));
        $service->setPermissionService($this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->getArticleProjectId(42);
    }

    public function test_get_article_project_id_returns_null_for_unknown_id(): void
    {
        $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
            'getArticleProjectId' => fn () => null,
        ]));
        $service->setPermissionService($this->denyingPermissions());

        $this->assertNull($service->getArticleProjectId(999));
    }

    // ---------------------------------------------------------------------
    // Drafts (#4056): what the user cannot open, they cannot change or delete either.
    // ---------------------------------------------------------------------

    public function test_update_article_refuses_another_users_draft(): void
    {
        session(['userdata.id' => 7]);
        $repo = $this->make(WikiRepository::class, [
            'getArticle' => fn () => $this->article(42, 'draft', 3),
            'getArticleProjectId' => fn () => 9,
            'updateArticle' => fn () => $this->fail('another user\'s draft must not be overwritten'),
        ]);
        $service = $this->makeService(wikiRepo: $repo);
        $service->setPermissionService($this->allowingPermissions());

        $article = new Article;
        $article->id = 42;
        $article->title = 'Overwritten';

        $this->assertFalse($service->updateArticle($article));
    }

    public function test_update_article_saves_own_draft_and_published_articles(): void
    {
        foreach ([[3, 'draft'], [7, 'published'], [7, null]] as [$userId, $status]) {
            session(['userdata.id' => $userId]);
            $saved = false;
            $existing = $this->article(42, 'published', 3);
            $existing->status = $status;
            $repo = $this->make(WikiRepository::class, [
                'getArticle' => fn () => $existing,
                'getArticleProjectId' => fn () => 9,
                'updateArticle' => function () use (&$saved): bool {
                    $saved = true;

                    return true;
                },
            ]);
            $service = $this->makeService(wikiRepo: $repo);
            $service->setPermissionService($this->allowingPermissions());

            $article = new Article;
            $article->id = 42;

            $this->assertTrue($service->updateArticle($article), "user $userId, status ".var_export($status, true));
            $this->assertTrue($saved);
        }
    }

    public function test_delete_article_refuses_another_users_draft(): void
    {
        session(['userdata.id' => 7]);
        $repo = $this->make(WikiRepository::class, [
            'getArticle' => fn () => $this->article(42, 'draft', 3),
            'getArticleProjectId' => fn () => 9,
            'delArticle' => fn () => $this->fail('another user\'s draft must not be deleted'),
        ]);
        $service = $this->makeService(wikiRepo: $repo);
        $service->setPermissionService($this->allowingPermissions());

        $this->assertFalse($service->deleteArticle(42));
    }

    public function test_is_article_visible_to_current_user_follows_the_draft_rule(): void
    {
        session(['userdata.id' => 7]);

        $visibility = function (?Article $article): bool {
            $service = $this->makeService(wikiRepo: $this->make(WikiRepository::class, [
                'getArticle' => fn () => $article ?? false,
                'getArticleProjectId' => fn () => $article ? 9 : null,
            ]));

            return $service->isArticleVisibleToCurrentUser(42);
        };

        $templateArticle = $this->article(42, 'published', 3);
        $templateArticle->status = null;

        $this->assertFalse($visibility($this->article(42, 'draft', 3)), 'another user\'s draft');
        $this->assertTrue($visibility($this->article(42, 'draft', 7)), 'own draft');
        $this->assertTrue($visibility($this->article(42, 'published', 3)), 'published');
        $this->assertTrue($visibility($templateArticle), 'no status (created from a template)');
        $this->assertFalse($visibility(null), 'unknown id');
    }
}
