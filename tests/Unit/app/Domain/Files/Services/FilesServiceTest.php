<?php

namespace Unit\app\Domain\Files\Services;

use Leantime\Domain\Files\Services\Files;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Unit\TestCase;

/**
 * Unit tests for the Files service logic extracted from the Files controllers
 * (Get, Browse, ShowAll) during the thin-controller refactor.
 */
class FilesServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

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

    // ---------------------------------------------------------------------
    // userCanUploadToModule() — authorizes the /files/upload (legacy /api/files)
    // endpoint, which previously forwarded any module/moduleId to upload() with
    // no access control.
    // ---------------------------------------------------------------------

    public function test_user_can_upload_to_module_allows_admin_anywhere(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'admin']]);

        /** @var Files $service */
        $service = $this->make(Files::class, [
            // Admins bypass before any project resolution happens.
            'resolveProjectId' => fn () => $this->fail('admins must short-circuit before resolving a project'),
        ]);

        $this->assertTrue($service->userCanUploadToModule('project', 999));
    }

    public function test_user_can_upload_to_module_denies_inaccessible_project(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        /** @var Files $service */
        $service = $this->make(Files::class, [
            'resolveProjectId' => fn () => 5,
            'projectRepository' => $this->make(ProjectRepository::class, [
                'isUserAssignedToProject' => fn () => false,
            ]),
        ]);

        $this->assertFalse($service->userCanUploadToModule('ticket', 42));
    }

    public function test_user_can_upload_to_module_allows_accessible_project(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        /** @var Files $service */
        $service = $this->make(Files::class, [
            'resolveProjectId' => fn () => 5,
            'projectRepository' => $this->make(ProjectRepository::class, [
                'isUserAssignedToProject' => fn () => true,
            ]),
        ]);

        $this->assertTrue($service->userCanUploadToModule('project', 5));
    }

    public function test_user_can_upload_to_module_allows_targets_with_no_project_mapping(): void
    {
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        /** @var Files $service */
        $service = $this->make(Files::class, [
            // e.g. wiki uploads resolve to no project; mirrors the read-path behaviour.
            'resolveProjectId' => fn () => null,
        ]);

        $this->assertTrue($service->userCanUploadToModule('wiki', 12));
    }
}
