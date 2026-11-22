<?php

namespace Unit\app\Domain\Notifications\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Notifications\Services\Push;
use Leantime\Domain\Notifications\Services\PushRelay;
use Leantime\Domain\Notifications\Services\PushText;
use Unit\TestCase;

/**
 * One change reaches a phone once: no "updated this task" right after "assigned to you" for
 * the same task, and never the same push twice in a row.
 */
class PushDuplicatesTest extends TestCase
{
    /** @var array<int, array{users: array<int>, title: string, body: string}> */
    private array $sent = [];

    private Push $push;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Push::resetAssigned();
        $this->sent = [];

        $pushText = $this->createMock(PushText::class);
        $pushText->method('forNotification')->willReturnCallback(
            fn (NotificationModel $n) => ['title' => 'Pricing page', 'body' => 'Marcel '.$n->action.' this task · Sales']
        );
        $this->app->instance(PushText::class, $pushText);

        $relay = $this->createMock(PushRelay::class);
        $relay->method('contentLevel')->willReturn(PushRelay::CONTENT_FULL);
        $this->push = $this->getMockBuilder(Push::class)
            ->setConstructorArgs([$this->createMock(Client::class), $relay])
            ->onlyMethods(['send'])
            ->getMock();
        $this->push->method('send')->willReturnCallback(function (array $users, string $title, string $body) {
            $this->sent[] = ['users' => $users, 'title' => $title, 'body' => $body];
        });
    }

    private function notification(string $action = 'updated', int $ticketId = 2267): NotificationModel
    {
        $notification = new NotificationModel;
        $notification->module = 'tickets';
        $notification->action = $action;
        $notification->entity = ['id' => $ticketId, 'headline' => 'Pricing page'];
        $notification->projectId = 111;
        $notification->authorId = 7;
        $notification->subject = 'subject';
        $notification->message = 'message';
        $notification->url = false;

        return $notification;
    }

    public function test_it_sends_the_lock_screen_wording(): void
    {
        $this->push->sendFromNotification($this->notification(), [3, 4]);

        $this->assertSame([['users' => [3, 4], 'title' => 'Pricing page', 'body' => 'Marcel updated this task · Sales']], $this->sent);
    }

    public function test_the_same_push_twice_reaches_each_phone_once(): void
    {
        $this->push->sendFromNotification($this->notification(), [3, 4]);
        $this->push->sendFromNotification($this->notification(), [3, 4, 5]);

        $this->assertSame([[3, 4], [5]], array_column($this->sent, 'users'));
    }

    public function test_a_different_change_still_goes_out(): void
    {
        $this->push->sendFromNotification($this->notification('updated'), [3]);
        $this->push->sendFromNotification($this->notification('created'), [3]);

        $this->assertCount(2, $this->sent);
    }

    public function test_someone_just_told_they_were_assigned_does_not_also_get_the_update(): void
    {
        Push::markAssigned(3, 2267);

        $this->push->sendFromNotification($this->notification(), [3, 4]);

        $this->assertSame([[4]], array_column($this->sent, 'users'));
    }

    public function test_the_assignment_mark_is_per_task(): void
    {
        Push::markAssigned(3, 9999);

        $this->push->sendFromNotification($this->notification(), [3]);

        $this->assertSame([[3]], array_column($this->sent, 'users'));
    }

    public function test_two_tasks_with_the_same_wording_both_reach_the_phone(): void
    {
        $this->push->sendFromNotification($this->notification('updated', 2267), [3]);
        $this->push->sendFromNotification($this->notification('updated', 2268), [3]);

        $this->assertCount(2, $this->sent);
    }

    public function test_only_the_assignments_own_write_is_skipped(): void
    {
        Push::markAssigned(3, 2267);

        // The project notification from the assignment's own write: skipped for the assignee.
        $this->push->sendFromNotification($this->notification('updated'), [3]);
        // A separate change to the task a moment later: it goes out.
        $this->push->sendFromNotification($this->notification('due_date_changed'), [3]);

        $this->assertSame([[3]], array_column($this->sent, 'users'));
        $this->assertSame('Marcel due_date_changed this task · Sales', $this->sent[0]['body']);
    }

    public function test_nothing_is_sent_when_everyone_was_skipped(): void
    {
        Push::markAssigned(3, 2267);

        $this->push->sendFromNotification($this->notification(), [3]);

        $this->assertSame([], $this->sent);
    }

    public function test_a_direct_send_at_content_free_carries_no_text(): void
    {
        $relay = $this->createMock(PushRelay::class);
        $relay->method('contentLevel')->willReturn(PushRelay::CONTENT_MINIMAL);
        $relay->method('contentFreeData')->willReturn(['module' => 'tickets', 'moduleId' => '2267']);
        $push = new Push($this->createMock(Client::class), $relay);

        [$title, $body, $data] = $push->withContentLevel('Pricing page', 'Marcel: secret', ['module' => 'tickets', 'moduleId' => '2267', 'url' => 'https://x/?q=secret']);

        $this->assertSame('Leantime', $title);
        $this->assertStringNotContainsString('secret', $body);
        $this->assertSame(['module' => 'tickets', 'moduleId' => '2267'], $data);
    }

    public function test_a_direct_send_at_names_only_never_forwards_a_link_with_text(): void
    {
        $relay = $this->createMock(PushRelay::class);
        $relay->method('contentLevel')->willReturn(PushRelay::CONTENT_NAMES);
        $relay->expects($this->once())->method('namesOnlyData')->willReturn(['module' => 'comments', 'url' => 'https://app/clean']);
        $push = new Push($this->createMock(Client::class), $relay);

        [$title, $body, $data] = $push->withContentLevel('Pricing page', 'Marcel commented', ['module' => 'comments', 'url' => 'https://x/api/jsonrpc?params=secret']);

        $this->assertSame(['Pricing page', 'Marcel commented'], [$title, $body]);
        $this->assertSame('https://app/clean', $data['url']);
    }

    public function test_a_direct_send_at_full_or_names_is_unchanged(): void
    {
        foreach ([PushRelay::CONTENT_FULL, PushRelay::CONTENT_NAMES] as $level) {
            $relay = $this->createMock(PushRelay::class);
            $relay->method('contentLevel')->willReturn($level);
            $relay->method('namesOnlyData')->willReturnArgument(0);
            $push = new Push($this->createMock(Client::class), $relay);

            $this->assertSame(['Pricing page', 'body', ['module' => 'tickets']], $push->withContentLevel('Pricing page', 'body', ['module' => 'tickets']));
        }
    }
}
