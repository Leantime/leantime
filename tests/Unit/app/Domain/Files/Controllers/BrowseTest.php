<?php

namespace Tests\Unit\app\Domain\Files\Controllers;

use Leantime\Core\Application;
use Leantime\Core\Auth\Permissions\PermissionEnforcer;
use Leantime\Core\Bootstrap\LoadConfig;
use Leantime\Core\Bootstrap\SetRequestForConsole;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Leantime\Domain\Files\Controllers\Browse;
use Leantime\Domain\Files\Services\Files as FileService;

/**
 * Deleting a file in the project file browser must return to the file browser (#3780), keeping the
 * popup mode when the browser was opened as one, instead of landing on the files/showAll modal.
 */
class BrowseTest extends \Unit\TestCase
{
    private Browse $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(APP_ROOT);
        $this->app->bootstrapWith([LoadConfig::class, SetRequestForConsole::class]);
        $this->app->boot();
        $this->app['view'] = $this->createMock(\Illuminate\View\Factory::class);
        $this->app['session'] = $this->createMock(\Illuminate\Session\SessionManager::class);
        $this->app->bootstrapWith([LoadConfig::class, SetRequestForConsole::class]);
        $this->app->instance(PermissionEnforcer::class, $this->createMock(PermissionEnforcer::class));

        $fileService = $this->createMock(FileService::class);
        $fileService->method('handleFileAction')->willReturn(['action' => 'delete', 'success' => true]);
        $this->app->instance(FileService::class, $fileService);

        $this->controller = new Browse($this->app['request'], $this->createMock(Template::class), $this->createMock(Language::class));
    }

    public function test_delete_returns_to_the_file_browser(): void
    {
        $response = $this->controller->post([]);

        $this->assertSame(BASE_URL.'/files/browse', $response->headers->get('Location'));
    }

    public function test_delete_keeps_the_popup_mode(): void
    {
        $response = $this->controller->post(['modalPopUp' => 'true']);

        $this->assertSame(BASE_URL.'/files/browse?modalPopUp=true', $response->headers->get('Location'));
    }
}
