<?php

namespace Unit\app\Domain\Files\Services;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Files\FileManager;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Clients\Permissions\ClientsPermissions;
use Leantime\Domain\Files\Permissions\FilesPermissions;
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

    public function test_get_files_by_module_client_without_id_returns_empty(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFilesByModule' => fn () => $this->fail('client listing with no id must not dump every client\'s files'),
        ]);

        // module=client has no project mapping; with no specific client id it must refuse rather
        // than enumerate all client files (the legitimate ShowClient path always passes an id).
        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->assertSame([], $service->getFilesByModule('client'));
    }

    public function test_get_files_by_module_client_with_id_requires_clients_view(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFilesByModule' => fn () => [['id' => 8, 'module' => 'client', 'moduleId' => 3]],
        ]);

        // With clients.view the specific client's files are listed.
        $this->assertCount(1, $this->makeService($repo, null, $this->allowingPermissions())->getFilesByModule('client', 3));

        // Without it the listing is refused before the repository is queried.
        $denyRepo = $this->make(FileRepository::class, [
            'getFilesByModule' => fn () => $this->fail('client files must not be listed without clients.view'),
        ]);
        $this->assertSame([], $this->makeService($denyRepo, null, $this->denyingPermissions())->getFilesByModule('client', 3));
    }

    public function test_get_files_by_module_checks_clients_view_globally(): void
    {
        $checked = [];
        $perms = $this->make(PermissionService::class, [
            'currentUserCan' => function (string $permission, ?int $projectId = null) use (&$checked) {
                $checked[] = [$permission, $projectId];

                return true;
            },
        ]);
        $repo = $this->make(FileRepository::class, ['getFilesByModule' => fn () => []]);

        $this->makeService($repo, null, $perms)->getFilesByModule('client', 3);

        $this->assertSame([[ClientsPermissions::VIEW, null]], $checked);
    }

    public function test_get_files_by_module_unknown_module_returns_empty(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFilesByModule' => fn () => $this->fail('an unknown module must not be listed'),
        ]);

        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->assertSame([], $service->getFilesByModule('comments', 3));
        $this->assertSame([], $service->getFilesByModule('zp_file', 3));
    }

    public function test_get_files_by_module_normalizes_case_before_checking(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFilesByModule' => fn () => $this->fail('an odd-cased project listing must still be authorized'),
        ]);

        // "PROJECT" is the project module: it resolves to project 5 and needs files.view there.
        $service = $this->makeService($repo, null, $this->denyingPermissions());

        $this->assertSame([], $service->getFilesByModule('PROJECT', 5));
    }

    public function test_normalize_module_lowercases_and_folds_plurals(): void
    {
        $this->assertSame('ticket', Files::normalizeModule('Ticket'));
        $this->assertSame('ticket', Files::normalizeModule(' TICKETS '));
        $this->assertSame('project', Files::normalizeModule('Projects'));
        $this->assertSame('client', Files::normalizeModule('Client'));
        $this->assertSame('', Files::normalizeModule(null));
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

    public function test_upload_throws_for_project_scoped_module_with_unresolvable_project(): void
    {
        // A project-scoped module (ticket) whose project can't be resolved (invalid/deleted id)
        // fails closed even with allow-all permissions — no orphan-file upload bypass.
        $repo = $this->make(FileRepository::class, ['getProjectIdForFile' => fn () => null]);
        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->upload(['file' => []], 'ticket', 999);
    }

    public function test_user_can_upload_to_module_denies_project_scoped_with_unresolvable_project(): void
    {
        $repo = $this->make(FileRepository::class, ['getProjectIdForFile' => fn () => null]);
        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->assertFalse($service->userCanUploadToModule('ticket', 999));
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

    public function test_user_owned_uploads_only_accept_the_current_users_id(): void
    {
        // Session user is 1. user/private targets are keyed by a user id and need no project role.
        $service = $this->makeService(null, null, $this->denyingPermissions());

        $this->assertTrue($service->userCanUploadToModule('user', 1));
        $this->assertTrue($service->userCanUploadToModule('Private', 1));
        $this->assertFalse($service->userCanUploadToModule('user', 9));
        $this->assertFalse($service->userCanUploadToModule('private', 9));
    }

    public function test_lead_and_export_uploads_are_denied(): void
    {
        $service = $this->makeService(null, null, $this->allowingPermissions());

        $this->assertFalse($service->userCanUploadToModule('lead', 1));
        $this->assertFalse($service->userCanUploadToModule('export', 1));
    }

    public function test_upload_into_another_users_files_throws(): void
    {
        $service = $this->makeService(null, null, $this->allowingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->upload(['file' => []], 'user', 9);
    }

    public function test_user_can_upload_to_module_denies_unknown_modules(): void
    {
        // Unknown or empty modules are denied even with allow-all permissions.
        $service = $this->makeService(null, null, $this->allowingPermissions());

        $this->assertFalse($service->userCanUploadToModule('comments', 9));
        $this->assertFalse($service->userCanUploadToModule('', 9));
    }

    public function test_user_can_upload_to_module_client_requires_clients_edit(): void
    {
        $this->assertTrue($this->makeService(null, null, $this->allowingPermissions())->userCanUploadToModule('client', 3));
        $this->assertFalse($this->makeService(null, null, $this->denyingPermissions())->userCanUploadToModule('client', 3));
        $this->assertFalse($this->makeService(null, null, $this->allowingPermissions())->userCanUploadToModule('client', 0));
    }

    public function test_user_can_upload_to_module_normalizes_case(): void
    {
        // "Ticket" must hit the same project check as "ticket" instead of the old allow-by-default.
        $repo = $this->make(FileRepository::class, ['getProjectIdForFile' => fn () => 5]);

        $this->assertFalse($this->makeService($repo, null, $this->denyingPermissions())->userCanUploadToModule('Ticket', 12));
        $this->assertFalse($this->makeService(null, null, $this->denyingPermissions())->userCanUploadToModule('PROJECT', 5));
    }

    public function test_upload_throws_for_odd_cased_project_module_when_denied(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getProjectIdForFile' => fn () => 5,
            'addFile' => fn () => $this->fail('a denied upload must not be stored'),
        ]);
        $service = $this->makeService($repo, null, $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->upload(['file' => []], 'Ticket', 12);
    }

    public function test_upload_throws_for_client_module_without_clients_edit(): void
    {
        $service = $this->makeService(null, null, $this->denyingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->upload(['file' => []], 'client', 3);
    }

    public function test_upload_throws_for_unknown_module(): void
    {
        $service = $this->makeService(null, null, $this->allowingPermissions());

        $this->expectException(AuthorizationException::class);

        $service->upload(['file' => []], 'comments', 3);
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

    public function test_get_file_for_user_denies_orphaned_project_file(): void
    {
        // A ticket file whose ticket was deleted resolves to no project; rather than fall through
        // to the non-project serve path it must be denied (fail closed), even with allow-all perms.
        $repo = $this->make(FileRepository::class, [
            'getFileByEncName' => fn () => [
                'id' => 14, 'realName' => 'a.pdf', 'extension' => 'pdf',
                'module' => 'ticket', 'moduleId' => 999, 'userId' => 2,
            ],
            'getProjectIdForFile' => fn () => null,
        ]);
        $fileManager = $this->make(FileManager::class, [
            'getFile' => fn () => $this->fail('an orphaned ticket file must not be served'),
        ]);

        $service = $this->makeService($repo, $fileManager, $this->allowingPermissions());

        $this->assertSame(403, $service->getFileForUser('enc', 1)->getStatusCode());
    }

    public function test_get_file_for_user_client_file_requires_clients_view(): void
    {
        $record = [
            'id' => 15, 'realName' => 'contract.pdf', 'extension' => 'pdf',
            'module' => 'client', 'moduleId' => 3, 'userId' => 1,
        ];
        $repo = $this->make(FileRepository::class, ['getFileByEncName' => fn () => $record]);
        $fileManager = $this->make(FileManager::class, ['getFile' => fn () => new Response('bytes', 200)]);

        // Even the uploader needs clients.view to read a client file.
        $this->assertSame(403, $this->makeService($repo, $fileManager, $this->denyingPermissions())->getFileForUser('enc', 1)->getStatusCode());
        $this->assertSame(200, $this->makeService($repo, $fileManager, $this->allowingPermissions())->getFileForUser('enc', 1)->getStatusCode());
    }

    public function test_get_file_for_user_odd_cased_project_file_is_authorized(): void
    {
        // A file stored under "Ticket" must not skip the project check.
        $repo = $this->make(FileRepository::class, [
            'getFileByEncName' => fn () => [
                'id' => 16, 'realName' => 'a.pdf', 'extension' => 'pdf',
                'module' => 'Ticket', 'moduleId' => 12, 'userId' => 2,
            ],
            'getProjectIdForFile' => fn () => 5,
        ]);
        $fileManager = $this->make(FileManager::class, [
            'getFile' => fn () => $this->fail('an odd-cased ticket file must not be served to a non-member'),
        ]);

        $service = $this->makeService($repo, $fileManager, $this->denyingPermissions());

        $this->assertSame(403, $service->getFileForUser('enc', 1)->getStatusCode());
    }

    public function test_get_file_for_user_unknown_module_is_limited_to_the_uploader(): void
    {
        $record = fn (int $owner) => [
            'id' => 17, 'realName' => 'a.png', 'extension' => 'png',
            'module' => 'comments', 'moduleId' => 4, 'userId' => $owner,
        ];
        $fileManager = $this->make(FileManager::class, ['getFile' => fn () => new Response('bytes', 200)]);

        $otherUsersFile = $this->make(FileRepository::class, ['getFileByEncName' => fn () => $record(2)]);
        $this->assertSame(403, $this->makeService($otherUsersFile, $fileManager, $this->allowingPermissions())->getFileForUser('enc', 1)->getStatusCode());

        $ownFile = $this->make(FileRepository::class, ['getFileByEncName' => fn () => $record(1)]);
        $this->assertSame(200, $this->makeService($ownFile, $fileManager, $this->allowingPermissions())->getFileForUser('enc', 1)->getStatusCode());
    }

    public function test_get_file_for_user_legacy_empty_module_stays_readable_by_authenticated_users(): void
    {
        // Legacy wiki images were stored with module '' on MySQL; other users must still see them.
        $repo = $this->make(FileRepository::class, [
            'getFileByEncName' => fn () => [
                'id' => 19, 'realName' => 'diagram.png', 'extension' => 'png',
                'module' => '', 'moduleId' => 44, 'userId' => 2,
            ],
        ]);
        $fileManager = $this->make(FileManager::class, ['getFile' => fn () => new Response('bytes', 200)]);

        $this->assertSame(200, $this->makeService($repo, $fileManager, $this->denyingPermissions())->getFileForUser('enc', 1)->getStatusCode());

        // ...but never without a session user.
        session(['userdata.id' => null]);
        $this->assertSame(403, $this->makeService($repo, $fileManager, $this->allowingPermissions())->getFileForUser('enc', 0)->getStatusCode());
    }

    public function test_legacy_empty_module_stays_strict_for_list_upload_and_delete(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFilesByModule' => fn () => $this->fail('an empty module must not be listed'),
            'getFile' => fn () => ['id' => 19, 'userId' => 2, 'module' => '', 'moduleId' => 44],
            'deleteFile' => fn () => $this->fail('a non-owner must not delete a legacy file'),
        ]);
        $service = $this->makeService($repo, null, $this->allowingPermissions());

        $this->assertSame([], $service->getFilesByModule('', 44));
        $this->assertFalse($service->userCanUploadToModule('', 44));
        $this->assertFalse($service->deleteFile(19));
    }

    public function test_wiki_upload_is_authorized_against_the_articles_project(): void
    {
        $checked = [];
        $perms = $this->make(PermissionService::class, [
            'currentUserCan' => function (string $permission, ?int $projectId = null) use (&$checked) {
                $checked[] = [$permission, $projectId];

                return $projectId === 7;
            },
        ]);
        $repo = $this->make(FileRepository::class, [
            'getProjectIdForWikiArticle' => fn (int $articleId) => $articleId === 44 ? 7 : null,
        ]);
        $service = $this->makeService($repo, null, $perms);

        // Article 44 lives in project 7: files.upload is checked there (not the session project).
        $this->assertTrue($service->userCanUploadToModule('wiki', 44));
        $this->assertSame([[FilesPermissions::UPLOAD, 7]], $checked);

        // An id that is not a wiki article resolves to nothing and fails closed.
        $this->assertFalse($service->userCanUploadToModule('Wiki', 999));
    }

    public function test_wiki_upload_without_article_falls_back_to_the_session_project(): void
    {
        session(['currentProject' => 9]);
        $checked = [];
        $perms = $this->make(PermissionService::class, [
            'currentUserCan' => function (string $permission, ?int $projectId = null) use (&$checked) {
                $checked[] = [$permission, $projectId];

                return true;
            },
        ]);
        $repo = $this->make(FileRepository::class, [
            'getProjectIdForWikiArticle' => fn () => $this->fail('no article id was given'),
        ]);

        $this->assertTrue($this->makeService($repo, null, $perms)->userCanUploadToModule('wiki', 0));
        $this->assertSame([[FilesPermissions::UPLOAD, 9]], $checked);
    }

    public function test_wiki_upload_is_stored_against_the_project(): void
    {
        $stored = null;
        $repo = $this->make(FileRepository::class, [
            'getProjectIdForWikiArticle' => fn () => 7,
            'addFile' => function (array $values, string $module) use (&$stored) {
                $stored = [$module, $values['moduleId']];

                return '55';
            },
        ]);
        $fileManager = $this->make(FileManager::class, [
            'upload' => fn () => ['encName' => 'abc', 'realName' => 'img', 'extension' => 'png', 'userId' => 1],
        ]);
        $service = $this->makeService($repo, $fileManager, $this->allowingPermissions());

        $tmp = tempnam(sys_get_temp_dir(), 'wikiimg');
        file_put_contents($tmp, 'png');
        try {
            $result = $service->upload(['file' => [
                'tmp_name' => $tmp, 'name' => 'img.png', 'type' => 'image/png', 'error' => 0, 'size' => 3,
            ]], 'wiki', 44);
        } finally {
            @unlink($tmp);
        }

        // zp_file.module has no 'wiki' enum value on MySQL: the row is a project file of project 7,
        // so downloads are authorized with files.view there.
        $this->assertSame(['project', 7], $stored);
        $this->assertSame('project', $result['module']);
    }

    public function test_get_file_for_user_wiki_row_is_authorized_against_the_articles_project(): void
    {
        // Rows stored with module 'wiki' (string-column installs) resolve through the article.
        $repo = $this->make(FileRepository::class, [
            'getFileByEncName' => fn () => [
                'id' => 20, 'realName' => 'a.png', 'extension' => 'png',
                'module' => 'wiki', 'moduleId' => 44, 'userId' => 2,
            ],
            'getProjectIdForWikiArticle' => fn () => 7,
        ]);
        $fileManager = $this->make(FileManager::class, ['getFile' => fn () => new Response('bytes', 200)]);

        $this->assertSame(403, $this->makeService($repo, $fileManager, $this->denyingPermissions())->getFileForUser('enc', 1)->getStatusCode());
        $this->assertSame(200, $this->makeService($repo, $fileManager, $this->allowingPermissions())->getFileForUser('enc', 1)->getStatusCode());
    }

    public function test_delete_file_client_file_requires_clients_edit_even_for_uploader(): void
    {
        $repo = $this->make(FileRepository::class, [
            'getFile' => fn () => ['id' => 18, 'userId' => 1, 'module' => 'client', 'moduleId' => 3],
            'deleteFile' => fn () => $this->fail('a client file must not be deleted without clients.edit'),
        ]);

        $this->assertFalse($this->makeService($repo, null, $this->denyingPermissions())->deleteFile(18));
    }

    public function test_repository_resolves_project_for_odd_cased_module(): void
    {
        /** @var FileRepository $repo */
        $repo = $this->make(FileRepository::class);

        $this->assertSame(5, $repo->getProjectIdForFile(['module' => 'Project', 'moduleId' => 5]));
    }

    // ---- handleFileAction result reflects upload() outcome ----------------

    public function test_handle_file_action_reports_failure_when_upload_is_denied(): void
    {
        /** @var Files $service */
        $service = $this->make(Files::class, [
            'upload' => function () {
                throw new AuthorizationException;
            },
        ]);

        $result = $service->handleFileAction(['upload' => '1'], ['file' => ['name' => 'a.png']], 'ticket', 5);

        $this->assertSame('upload', $result['action']);
        $this->assertFalse($result['success']);
    }

    public function test_handle_file_action_reports_failure_when_upload_returns_error_string(): void
    {
        /** @var Files $service */
        $service = $this->make(Files::class, [
            'upload' => fn () => 'Error uploading file',
        ]);

        $result = $service->handleFileAction(['upload' => '1'], ['file' => ['name' => 'a.png']], 'project', 5);

        $this->assertSame('upload', $result['action']);
        $this->assertFalse($result['success']);
    }
}
