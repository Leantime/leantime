<?php

namespace Unit\app\Domain\Notifications\Services;

use Leantime\Core\Language;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Notifications\Services\BulkNotifications;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Events\TicketAssigned;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * An import sends one summary per project instead of a notification per row.
 */
class BulkNotificationsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, Notification> Notifications that were sent. */
    private array $sent = [];

    private function bulk(): BulkNotifications
    {
        $this->sent = [];
        session(['userdata.id' => 1, 'userdata.name' => 'Ada']);

        return new BulkNotifications(
            $this->make(Projects::class, [
                'notifyProjectUsers' => function (Notification $notification) {
                    $this->sent[] = $notification;
                },
                'getProjectName' => fn ($id) => 'Project '.$id,
            ]),
            $this->make(TicketRepository::class, [
                // 3 new, 4 in progress, 0 done, -1 archived (DONE type).
                'getStateLabels' => fn ($projectId = null) => [
                    3 => ['statusType' => 'NEW'],
                    4 => ['statusType' => 'INPROGRESS'],
                    0 => ['statusType' => 'DONE'],
                    -1 => ['statusType' => 'DONE'],
                ],
            ]),
            $this->make(Language::class, [
                '__' => fn (string $index, string $default = '') => $index === 'email_notifications.import_closed_note'
                    ? $index.'|%1$s'
                    : $index.'|%1$s|%2$s|%3$s',
            ]),
            // Stored people of to-dos updated without their assignee in the row.
            $this->make(ProjectRepository::class, [
                'getTicketPeopleIds' => fn (int $ticketId) => $ticketId === 6 ? [12, 13] : [],
            ]),
        );
    }

    /**
     * @param  array<string, mixed>  $extra  More entity values (editorId, userId, collaborators).
     */
    private function saved(int $id, mixed $status, string $action = 'created', int $projectId = 9, string $type = 'task', array $extra = []): Notification
    {
        $notification = new Notification;
        $notification->module = 'tickets';
        $notification->action = $action;
        $notification->projectId = $projectId;
        $notification->entity = ['id' => $id, 'status' => $status, 'type' => $type, 'projectId' => $projectId] + $extra;

        return $notification;
    }

    private function assigned(int $ticketId, int $assigneeId): TicketAssigned
    {
        return new TicketAssigned(ticketId: $ticketId, projectId: 9, assigneeId: $assigneeId, previousAssigneeId: null, assignedToSelf: false);
    }

    public function test_an_import_sends_one_summary_that_counts_only_open_items(): void
    {
        $bulk = $this->bulk();

        $bulk->hold(function () {
            $this->assertTrue(BulkNotifications::record($this->saved(1, 3)));
            $this->assertTrue(BulkNotifications::record($this->saved(2, 4)));
            $this->assertTrue(BulkNotifications::record($this->saved(3, 0)));
            $this->assertTrue(BulkNotifications::record($this->saved(4, -1)));
            $this->assertTrue(BulkNotifications::recordAssignment($this->assigned(1, 7)));
            $this->assertTrue(BulkNotifications::recordAssignment($this->assigned(3, 8)));
            $this->assertSame([], $this->sent, 'nothing is sent while the rows are saved');
        });

        $this->assertCount(1, $this->sent);
        $summary = $this->sent[0];
        $this->assertSame(9, $summary->projectId);
        $this->assertSame('email_notifications.import_todos_subject|Ada|2|Project 9', $summary->subject);
        $this->assertStringContainsString('email_notifications.import_closed_note|2', $summary->message);
        $this->assertSame([7], $summary->involvedUserIds, 'the assignee of an open item is involved; the assignee of a done one is not');
        $this->assertSame([], $summary->entity['collaborators'], 'assignees go through everyone\'s preferences, including the email switch');
        $this->assertFalse(BulkNotifications::isHolding());
    }

    public function test_an_import_of_only_done_or_archived_items_sends_nothing(): void
    {
        $bulk = $this->bulk();

        $bulk->hold(function () {
            BulkNotifications::record($this->saved(1, 0));
            BulkNotifications::record($this->saved(2, -1));
        });

        $this->assertSame([], $this->sent);
    }

    public function test_each_project_gets_its_own_summary_and_an_item_counts_once(): void
    {
        $bulk = $this->bulk();

        $bulk->hold(function () {
            BulkNotifications::record($this->saved(1, 3));
            // Updated in the same import without a status: still one open item.
            BulkNotifications::record($this->saved(1, null, 'updated'));
            BulkNotifications::record($this->saved(5, 3, 'created', 10, 'milestone'));
        });

        $this->assertCount(2, $this->sent);
        $this->assertSame('email_notifications.import_todos_subject|Ada|1|Project 9', $this->sent[0]->subject);
        $this->assertSame('email_notifications.import_milestones_subject|Ada|1|Project 10', $this->sent[1]->subject);
    }

    public function test_outside_an_import_and_for_other_modules_notifications_are_sent_as_usual(): void
    {
        $bulk = $this->bulk();

        $this->assertFalse(BulkNotifications::record($this->saved(1, 3)));
        $this->assertFalse(BulkNotifications::recordAssignment($this->assigned(1, 7)));

        $bulk->hold(function () {
            $project = new Notification;
            $project->module = 'projects';
            $project->projectId = 9;
            $this->assertFalse(BulkNotifications::record($project));
        });
    }

    public function test_nested_holds_send_once_and_a_failed_import_still_sends_what_was_saved(): void
    {
        $bulk = $this->bulk();

        try {
            $bulk->hold(function () use ($bulk) {
                $bulk->hold(fn () => BulkNotifications::record($this->saved(1, 3)));
                $this->assertSame([], $this->sent, 'the inner hold does not send');

                throw new \RuntimeException('row 2 failed');
            });
            $this->fail('the exception is passed on');
        } catch (\RuntimeException $e) {
            $this->assertSame('row 2 failed', $e->getMessage());
        }

        $this->assertCount(1, $this->sent);
        $this->assertFalse(BulkNotifications::isHolding());
    }

    public function test_the_people_of_open_items_are_involved_and_collaborators_keep_their_bypass(): void
    {
        $bulk = $this->bulk();

        $bulk->hold(function () {
            // Added by Ada (1), assigned to 5, with collaborator 11.
            BulkNotifications::record($this->saved(1, 3, 'created', 9, 'task', ['editorId' => 5, 'userId' => 1, 'collaborators' => [11]]));
            // Updated: updateTicket() sends the assignee but never the creator, so the stored
            // people are looked up (to-do 6 was created by 13).
            BulkNotifications::record($this->saved(6, 4, 'updated', 9, 'task', ['editorId' => 12]));
            // Done: its people are not involved.
            BulkNotifications::record($this->saved(7, 0, 'created', 9, 'task', ['editorId' => 14, 'collaborators' => [15]]));
        });

        $summary = $this->sent[0];
        $ids = $summary->involvedUserIds;
        sort($ids);
        $this->assertSame([5, 11, 12, 13], $ids, 'the author is not involved in their own import');
        $this->assertSame([11], $summary->entity['collaborators']);
    }
}
