<?php

namespace Unit\app\Domain\Tickets\Templates;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Plugins\Plugins;
use Leantime\Domain\Tickets\Models\TicketHistoryEntry;
use Unit\TestCase;

/**
 * The History tab partial renders user-entered history values escaped.
 */
class TicketHistoryRenderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! defined('BASE_URL')) {
            define('BASE_URL', 'http://localhost');
        }

        // The view factory discovers plugin composer paths and can() checks the permission
        // engine; both hit the database, which unit tests do not have.
        $plugins = $this->createMock(Plugins::class);
        $plugins->method('getEnabledPluginPaths')->willReturn([]);
        app()->instance(Plugins::class, $plugins);

        $permissions = $this->createMock(PermissionService::class);
        $permissions->method('currentUserCan')->willReturn(true);
        app()->instance(PermissionService::class, $permissions);
    }

    public function test_history_values_are_escaped(): void
    {
        $html = view('tickets::partials.ticketHistory', [
            'historyAvailable' => true,
            'historyEntries' => [
                new TicketHistoryEntry(
                    id: 2,
                    userId: 1,
                    userName: '<b>Jane</b>',
                    dateModified: '2026-01-02 10:00:00',
                    field: 'headline',
                    fieldLabel: 'Headline',
                    oldValue: 'Old <img src=x onerror=alert(1)>',
                    newValue: 'New <script>alert(1)</script>',
                ),
            ],
        ])->render();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
        $this->assertStringNotContainsString('<b>Jane</b>', $html);
        $this->assertStringContainsString('New &lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringContainsString('&lt;b&gt;Jane&lt;/b&gt;', $html);
    }

    public function test_unavailable_history_renders_the_not_available_note(): void
    {
        $html = view('tickets::partials.ticketHistory', [
            'historyAvailable' => false,
            'historyEntries' => [],
        ])->render();

        $this->assertStringContainsString('ticketHistory', $html);
        $this->assertStringNotContainsString('<li', $html);
    }

    public function test_history_mount_points_at_the_hx_controller_and_loads_lazily(): void
    {
        $html = \Illuminate\Support\Facades\Blade::render(
            '<x-global::hx :for="\\Leantime\\Domain\\Tickets\\Hxcontrollers\\TicketHistory::class" :id="$id" trigger="intersect once" />',
            ['id' => 12]
        );

        $this->assertStringContainsString('/hx/tickets/ticketHistory/get/12', $html);
        $this->assertStringContainsString('hx-trigger="intersect once"', $html);
    }
}
