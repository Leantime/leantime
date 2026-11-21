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

    private function notification(string $action = 'updated'): NotificationModel
    {
        $notification = new NotificationModel;
        $notification->module = 'tickets';
        $notification->action = $action;
        $notification->entity = ['id' => 2267, 'headline' => 'Pricing page'];
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
        Cache::put(Push::assignedKey(3, 2267), 1, Push::RECENT_SECONDS);

        $this->push->sendFromNotification($this->notification(), [3, 4]);

        $this->assertSame([[4]], array_column($this->sent, 'users'));
    }

    public function test_the_assignment_mark_is_per_task(): void
    {
        Cache::put(Push::assignedKey(3, 9999), 1, Push::RECENT_SECONDS);

        $this->push->sendFromNotification($this->notification(), [3]);

        $this->assertSame([[3]], array_column($this->sent, 'users'));
    }

    public function test_nothing_is_sent_when_everyone_was_skipped(): void
    {
        Cache::put(Push::assignedKey(3, 2267), 1, Push::RECENT_SECONDS);

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

    public function test_a_direct_send_at_full_or_names_is_unchanged(): void
    {
        foreach ([PushRelay::CONTENT_FULL, PushRelay::CONTENT_NAMES] as $level) {
            $relay = $this->createMock(PushRelay::class);
            $relay->method('contentLevel')->willReturn($level);
            $push = new Push($this->createMock(Client::class), $relay);

            $this->assertSame(['Pricing page', 'body', ['module' => 'tickets']], $push->withContentLevel('Pricing page', 'body', ['module' => 'tickets']));
        }
    }
}
