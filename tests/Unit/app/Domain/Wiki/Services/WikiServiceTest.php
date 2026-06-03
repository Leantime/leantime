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
                'delArticle' => function ($id) use (&$deletedId): void {
                    $deletedId = $id;
                },
            ]),
            auditRepo: $this->make(AuditRepository::class, [
                'storeEvent' => function (string $action) use (&$auditedAction) {
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
}
