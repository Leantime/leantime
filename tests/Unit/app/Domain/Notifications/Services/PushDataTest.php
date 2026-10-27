<?php

namespace Unit\app\Domain\Notifications\Services;

use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Notifications\Services\Push;
use Unit\TestCase;

/**
 * The push data payload is what the mobile app routes a tap with. A comment's own id opens
 * nothing in the app, so comments on a task must carry the task id (parentTicketId).
 */
class PushDataTest extends TestCase
{
    private function notification(string $module, mixed $entity, string $action = '', int $projectId = 7): Notification
    {
        $n = new Notification;
        $n->module = $module;
        $n->entity = $entity;
        $n->action = $action;
        $n->projectId = $projectId;
        $n->url = ['url' => 'https://example.test/x', 'text' => 'Open'];

        return $n;
    }

    public function test_ticket_carries_project_type_and_thread(): void
    {
        $data = Push::buildData($this->notification('tickets', ['id' => 123, 'headline' => 'Fix login'], 'updated'));

        $this->assertSame([
            'module' => 'tickets',
            'moduleId' => '123',
            'projectId' => '7',
            'type' => 'updated',
            'threadId' => 'ticket-123',
            'url' => 'https://example.test/x',
        ], $data);
    }

    public function test_comment_on_a_task_carries_the_task_id(): void
    {
        $entity = ['id' => 456, 'module' => 'ticket', 'moduleId' => 123, 'text' => 'hi'];
        $data = Push::buildData($this->notification('comments', $entity, 'commented'));

        $this->assertSame('comments', $data['module']);
        $this->assertSame('456', $data['moduleId']);
        $this->assertSame('123', $data['parentTicketId']);
        $this->assertSame('ticket-123', $data['threadId']);
        $this->assertSame('commented', $data['type']);
    }

    public function test_comment_on_something_else_has_no_task_link(): void
    {
        $entity = ['id' => 456, 'module' => 'project', 'moduleId' => 9];
        $data = Push::buildData($this->notification('comments', $entity, 'commented'));

        $this->assertArrayNotHasKey('parentTicketId', $data);
        $this->assertArrayNotHasKey('threadId', $data);
        $this->assertSame('https://example.test/x', $data['url']);
    }

    public function test_object_entities_and_missing_values(): void
    {
        $n = $this->notification('goalcanvas', (object) ['id' => 5], '', 0);
        $n->url = false;

        $this->assertSame(['module' => 'goalcanvas', 'moduleId' => '5', 'url' => ''], Push::buildData($n));
    }
}
