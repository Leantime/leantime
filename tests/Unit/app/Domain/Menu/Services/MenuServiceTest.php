<?php

namespace Unit\app\Domain\Menu\Services;

use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
use Leantime\Domain\Menu\Services\Menu;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Setting\Services\Setting;
use Leantime\Domain\Sprints\Services\Sprints as SprintService;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Users\Services\Users;
use Unit\TestCase;

/**
 * Unit tests for the pure project-selector helper logic extracted from the
 * Menu ProjectSelector HxController into the Menu service.
 */
class MenuServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a Menu service with stubbed collaborators. The helpers under test
     * (settings link + redirect url) do not touch any collaborator, so the
     * dependencies just need to exist.
     */
    private function makeService(): Menu
    {
        return new Menu(
            $this->make(ProjectService::class),
            $this->make(TimesheetService::class),
            $this->make(SprintService::class),
            $this->make(Users::class),
            $this->make(Setting::class),
            $this->make(MenuRepository::class),
        );
    }

    public function test_settings_link_is_populated_for_project_menu(): void
    {
        $service = $this->makeService();

        $link = $service->getProjectSelectorSettingsLink('project');

        $this->assertSame('projects', $link['module']);
        $this->assertSame('showProject', $link['action']);
        $this->assertArrayHasKey('label', $link);
        $this->assertArrayHasKey('settingsIcon', $link);
        $this->assertArrayHasKey('settingsTooltip', $link);
    }

    public function test_settings_link_is_populated_for_default_menu(): void
    {
        $service = $this->makeService();

        $link = $service->getProjectSelectorSettingsLink('default');

        $this->assertSame('projects', $link['module']);
        $this->assertSame('showProject', $link['action']);
    }

    public function test_settings_link_is_empty_for_other_menu_types(): void
    {
        $service = $this->makeService();

        $link = $service->getProjectSelectorSettingsLink('personal');

        $this->assertSame([
            'label' => '',
            'module' => '',
            'action' => '',
            'settingsIcon' => '',
            'settingsTooltip' => '',
        ], $link);
    }

    public function test_redirect_url_rewrites_show_project_to_dashboard(): void
    {
        $service = $this->makeService();

        $this->assertSame(
            '/dashboard/show',
            $service->getProjectSelectorRedirectUrl('/projects/showProject/5')
        );
    }

    public function test_redirect_url_is_unchanged_for_other_uris(): void
    {
        $service = $this->makeService();

        $this->assertSame(
            '/tickets/showKanban',
            $service->getProjectSelectorRedirectUrl('/tickets/showKanban')
        );
    }
}
