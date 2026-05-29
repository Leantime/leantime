<?php

namespace Unit\app\Domain\Files\Services;

use Leantime\Domain\Files\Services\Files;
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
}
