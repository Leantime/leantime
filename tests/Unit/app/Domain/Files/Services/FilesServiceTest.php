<?php

namespace Unit\app\Domain\Files\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Files\FileManager;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Files\Services\Files;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * Unit tests for the Files service: the pure helpers extracted during the thin-controller refactor
 * (getImageExtensions, isOwnerRestrictedModule, handleFileAction) plus the authorization the native
 * permission engine added. The authz tests prove the four IDOR-prone @api methods fail closed:
 *  - getFilesByModule resolves the target's project and denies non-members (no enumeration)
 *  - upload authorizes against the target project (commenter+) on the JSON-RPC path too
 *  - deleteFile preserves owner-delete but scopes the non-owner path to the file's project (editor+),
 *    closing the old manager-global cross-project bypass
 *  - getFileForUser authorizes the SESSION user, never the spoofable $userId argument
 */
class FilesServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        // The current (session) user the service authorizes as.
        session(['userdata.id' => 1]);
    }

    /** Permission stub that grants everything. */
    private function allowingPermissions(): PermissionService
    {
        return $this->make(PermissionService::class, [
            'currentUserCan' => fn () => true,
            'authorize' => fn () => null,
        ]);
    }

    /** Permission stub that denies everything (authorize throws, currentUserCan is false). */
    private function denyingPermissions(): PermissionService
    {
        return $this->make(PermissionService::class, [
            'currentUserCan' => fn () => false,
            'authorize' => function (): void {
                throw new AuthorizationException;
            },
        ]);
    }

    private function makeService(
        ?FileRepository $repo = null,
        ?FileManager $fileManager = null,
        ?PermissionService $perms = null,
    ): Files {
        $service = new Files(
            $repo ?? $this->make(FileRepository::class),
            $fileManager ?? $this->make(FileManager::class),
            $this->make(LanguageCore::class),
        );
        $service->setPermissionService($perms ?? $this->allowingPermissions());

        return $service;
    }

    // ---- pure helpers -----------------------------------------------------

    public function test_get_image_extensions_returns_the_shared_whitelist(): void
    {
        /** @var Files $service */
        $service = $this->make(Files::class);

        $extensions = $service->getImageExtensions();

        $this->assertContains('jpg', $extensions);
        $this->assertContains('webp', $extensions);
        $this->assertSame(
            ['jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv', 'webp'],
            $extensions
        );
    }

    public function test_is_owner_restricted_module_flags_private_modules(): void
    {
        /** @var Files $service */
        $service = $this->make(Files::class);

        $this->assertTrue($service->isOwnerRestrictedModule(['module' => 'private']));
        $this->assertTrue($service->isOwnerRestrictedModule(['module' => 'user']));
        $this->assertTrue($service->isOwnerRestrictedModule(['module' => 'lead']));
        $this->assertTrue($service->isOwnerRestrictedModule(['module' => 'export']));
    }

    public function test_is_owner_restricted_module_allows_shared_modules(): void
    {
        /** @var Files $service */
        $service = $this->make(Files::class);

        $this->assertFalse($service->isOwnerRestrictedModule(['module' => 'project']));
        $this->assertFalse($service->isOwnerRestrictedModule(['module' => 'ticket']));
        $this->assertFalse($service->isOwnerRestrictedModule(['module' => 'client']));
        $this->assertFalse($service->isOwnerRestrictedModule([]));
    }

    // ---- handleFileAction (controller helper; delegates self-authorize) ---

    public function test_handle_file_action_deletes_when_del_file_present(): void
    {
        $captured = null;
        /** @var Files $service */
        $service = $this->make(Files::class, [
            'deleteFile' => function ($fileId) use (&$captured) {
                $captured = $fileId;

                return true;
            },
        ]);

        $result = $service->handleFileAction(['delFile' => '42'], [], 'project', 7);

        $this->assertSame('delete', $result['action']);
        $this->assertTrue($result['success']);
        $this->assertSame('42', $captured);
    }

    public function test_handle_file_action_reports_failed_delete(): void
    {
        /** @var Files $service */
        $service = $this->make(Files::class, [
            'deleteFile' => fn () => false,
        ]);

        $result = $service->handleFileAction(['delFile' => '42'], [], 'project', 7);

        $this->assertSame('delete', $result['action']);
        $this->assertFalse($result['success']);
    }

    public function test_handle_file_action_uploads_when_file_present(): void
    {
        $uploadArgs = null;
        /** @var Files $service */
        $service = $this->make(Files::class, [
            'upload' => function ($files, $module, $moduleId) use (&$uploadArgs) {
                $uploadArgs = [$files, $module, $moduleId];

                return ['fileId' => 99];
            },
        ]);

        $files = ['file' => ['name' => 'a.png']];
        $result = $service->handleFileAction(['upload' => '1'], $files, 'project', 7);

        $this->assertSame('upload', $result['action']);
        $this->assertTrue($result['success']);
        $this->assertSame([$files, 'project', 7], $uploadArgs);
    }

    public function test_handle_file_action_reports_upload_without_file(): void
    {
        /** @var Files $service */
        $service = $this->make(Files::class, [
            'upload' => fn () => $this->fail('upload should not be called when no file is present'),
        ]);

        $result = $service->handleFileAction(['upload' => '1'], [], 'project', 7);

        $this->assertSame('upload', $result['action']);
        $this->assertFalse($result['success']);
    }

    public function test_handle_file_action_returns_null_action_for_empty_payload(): void
    {
        /** @var Files $service */
        $service = $this->make(Files::class);

        $result = $service->handleFileAction([], [], 'project', 7);

        $this->assertNull($result['action']);
        $this->assertFalse($result['success']);
    }

    // ---- getFilesByModule -------------------------------------------------

    public function test_get_files_by_module_denies_non_member(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFilesByModule' => fn () => $this->fail('Repository must not be queried when files.view is denied'),
        ]);

        $service = $this->makeService($repo, null, $this->denyingPermissions());

        // module=project → projectId resolves to moduleId (5) directly; can(VIEW,5)=false → soft-deny.
        $this->assertSame([], $service->getFilesByModule('project', 5));
    }

    public function test_get_files_by_module_allows_member(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFilesByModule' => fn () => [['id' => 99, 'module' => 'project', 'moduleId' => 5]],
        ]);

        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->assertCount(1, $service->getFilesByModule('project', 5));
    }

    public function test_get_files_by_module_empty_module_returns_empty_without_dumping(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFilesByModule' => fn () => $this->fail('An empty module must never dump the file table'),
        ]);

        // Even with allow-all permissions, an empty module has no project context and must refuse.
        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->assertSame([], $service->getFilesByModule(''));
    }

    public function test_get_files_by_module_owner_restricted_denies_other_user(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFilesByModule' => fn () => $this->fail('Owner-restricted listing must not return another user\'s files'),
        ]);

        // module=user, entityId=2 (not the session user 1) → soft-deny regardless of role.
        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->assertSame([], $service->getFilesByModule('user', 2));
    }

    public function test_get_files_by_module_owner_restricted_allows_owner(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFilesByModule' => fn () => [['id' => 7, 'module' => 'user', 'moduleId' => 1]],
        ]);

        // module=user, entityId=1 == session user → allowed.
        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->assertCount(1, $service->getFilesByModule('user', 1));
    }

    // ---- deleteFile -------------------------------------------------------

    public function test_delete_file_allows_owner_even_without_permission(): void
    {
        $deleted = false;
        $repo = $this->make(FileRepository::class, [
            'getFile' => fn () => ['id' => 10, 'userId' => 1, 'module' => 'project', 'moduleId' => 5],
            'deleteFile' => function () use (&$deleted) {
                $deleted = true;

                return true;
            },
        ]);

        // Deny-all permissions: the owner path must still delete (file.userId === session user 1).
        $service = $this->makeService($repo, null, $this->denyingPermissions());

        $this->assertTrue($service->deleteFile(10));
        $this->assertTrue($deleted, 'Owner deletion should reach the repository');
    }

    public function test_delete_file_denies_non_owner_without_permission(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFile' => fn () => ['id' => 10, 'userId' => 2, 'module' => 'project', 'moduleId' => 5],
            'deleteFile' => fn () => $this->fail('A non-owner without files.delete must not delete'),
        ]);

        // File owned by user 2; session user is 1 without files.delete in project 5 → soft-deny.
        $service = $this->makeService($repo, null, $this->denyingPermissions());

        $this->assertFalse($service->deleteFile(10));
    }

    public function test_delete_file_allows_non_owner_with_project_permission(): void
    {
        $deleted = false;
        $repo = $this->make(FileRepository::class, [
            'getFile' => fn () => ['id' => 10, 'userId' => 2, 'module' => 'project', 'moduleId' => 5],
            'deleteFile' => function () use (&$deleted) {
                $deleted = true;

                return true;
            },
        ]);

        // Non-owner, but allow-all grants files.delete in the file's project (editor+).
        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->assertTrue($service->deleteFile(10));
        $this->assertTrue($deleted);
    }

    public function test_delete_file_missing_returns_false(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFile' => fn () => false,
        ]);

        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->assertFalse($service->deleteFile(999));
    }

    public function test_delete_file_owner_restricted_non_owner_denied(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFile' => fn () => ['id' => 11, 'userId' => 2, 'module' => 'user', 'moduleId' => 2],
            'deleteFile' => fn () => $this->fail('A no-project file may only be deleted by its uploader'),
        ]);

        // Owner-restricted (module=user → no project); session user 1 is not the owner (2) →
        // deny even with allow-all (the old manager-global delete of others' files is dropped).
        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->assertFalse($service->deleteFile(11));
    }

    // ---- upload -----------------------------------------------------------

    public function test_upload_throws_when_project_upload_denied(): void
    {
        $service = $this->makeService(null, null, $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        // Passes the initial validation (module/moduleId present, file is an array) and reaches the
        // project authorize() before any file is written → throws on deny.
        $service->upload(['file' => []], 'project', 5);
    }

    public function test_user_can_upload_to_module_reflects_project_permission(): void
    {
        $this->assertTrue(
            $this->makeService(null, null, $this->allowingPermissions())->userCanUploadToModule('project', 5)
        );

        $this->assertFalse(
            $this->makeService(null, null, $this->denyingPermissions())->userCanUploadToModule('project', 5)
        );
    }

    public function test_user_can_upload_to_module_nonproject_preserved(): void
    {
        // Non-project modules (user avatar, ...) have no project context; preserved as allowed even
        // under deny-all (their flows pin moduleId server-side).
        $service = $this->makeService(null, null, $this->denyingPermissions());

        $this->assertTrue($service->userCanUploadToModule('user', 9));
    }

    // ---- getFileForUser ---------------------------------------------------

    public function test_get_file_for_user_denies_non_member(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFileByEncName' => fn () => [
                'id' => 12, 'realName' => 'doc.pdf', 'extension' => 'pdf',
                'module' => 'project', 'moduleId' => 5, 'userId' => 2,
            ],
        ]);
        $fileManager = $this->make(FileManager::class, [
            'getFile' => fn () => $this->fail('A non-member must not receive file bytes'),
        ]);

        $service = $this->makeService($repo, $fileManager, $this->denyingPermissions());

        $response = $service->getFileForUser('abc123', 1);
        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_get_file_for_user_allows_member(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFileByEncName' => fn () => [
                'id' => 12, 'realName' => 'doc.pdf', 'extension' => 'pdf',
                'module' => 'project', 'moduleId' => 5, 'userId' => 2,
            ],
        ]);
        $fileManager = $this->make(FileManager::class, [
            'getFile' => fn () => new Response('bytes', 200),
        ]);

        $service = $this->makeService($repo, $fileManager, $this->allowingPermissions());

        $response = $service->getFileForUser('abc123', 1);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_get_file_for_user_owner_restricted_uses_session_user_not_arg(): void
    {
        // Owner-restricted file owned by user 2. Session user is 1. Even though the caller passes
        // userId=2 (spoofing the owner) the check uses the SESSION user (1) and denies.
        $repo = $this->make(FileRepository::class, [
            'getFileByEncName' => fn () => [
                'id' => 13, 'realName' => 'secret.txt', 'extension' => 'txt',
                'module' => 'private', 'moduleId' => 2, 'userId' => 2,
            ],
        ]);
        $fileManager = $this->make(FileManager::class, [
            'getFile' => fn () => $this->fail('Owner-restricted file must not be served to a non-owner'),
        ]);

        $service = $this->makeService($repo, $fileManager, $this->allowingPermissions());

        $response = $service->getFileForUser('enc', 2);
        $this->assertSame(403, $response->getStatusCode());
    }

    public function test_get_file_for_user_missing_returns_404(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFileByEncName' => fn () => false,
        ]);

        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->assertSame(404, $service->getFileForUser('missing', 1)->getStatusCode());
    }
}
