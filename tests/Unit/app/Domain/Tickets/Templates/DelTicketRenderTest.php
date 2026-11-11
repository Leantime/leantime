<?php

namespace Unit\app\Domain\Tickets\Templates;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Plugins\Plugins;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Unit\TestCase;

/**
 * The delete dialog offers to delete the subtasks too (#2153), but only when there are any.
 */
class DelTicketRenderTest extends TestCase
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

    private function render(int $subtaskCount): string
    {
        $ticket = new TicketModel;
        $ticket->id = 5;

        return view('tickets::delTicket', ['error' => '', 'ticket' => $ticket, 'subtaskCount' => $subtaskCount])->render();
    }

    public function test_the_dialog_offers_to_delete_the_subtasks_when_there_are_some(): void
    {
        $html = $this->render(3);

        $this->assertStringContainsString('name="deleteSubtasks"', $html);
        $this->assertStringContainsString('text.delete_subtasks_too', $html);
    }

    public function test_the_dialog_has_no_option_without_subtasks(): void
    {
        $html = $this->render(0);

        $this->assertStringNotContainsString('deleteSubtasks', $html);
        $this->assertStringContainsString('name="del"', $html);
    }
}
