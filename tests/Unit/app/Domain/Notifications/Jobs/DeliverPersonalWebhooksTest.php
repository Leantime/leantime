<?php

namespace Unit\app\Domain\Notifications\Jobs;

use Illuminate\Support\Facades\Log;
use Leantime\Domain\Notifications\Jobs\DeliverPersonalWebhooks;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Notifications\Services\WebhookQueue;
use Leantime\Domain\Notifications\Services\Webhooks;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Queue\Workers\Workers;
use Unit\TestCase;

/**
 * The queued personal-webhook job as the WEBHOOKS queue runs it: the stored
 * payload is rebuilt into a notification for Webhooks::sendToUsers(), and
 * nothing — an unreadable row or a failing delivery — ever escapes to the
 * runner or leaves the row behind.
 *
 * Runs through the real WebhookQueue (safe_unserialize, the job resolved from
 * the container, row deletion) over a faked zp_queue table and a recording
 * Webhooks stub.
 */
class DeliverPersonalWebhooksTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Every sendToUsers() call the job made.
     *
     * @var array<int, array{notification: NotificationModel, userIds: array<int>}>
     */
    private array $deliveries = [];

    /**
     * msghashes the runner deleted from the queue.
     *
     * @var array<int, string>
     */
    private array $deletedRows = [];

    /**
     * Warning log lines, message plus JSON context.
     *
     * @var array<int, string>
     */
    private array $warnings = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->deliveries = [];
        $this->deletedRows = [];
        $this->warnings = [];

        Log::shouldReceive('warning')->andReturnUsing(function ($message, $context = []) {
            $this->warnings[] = $message.' '.json_encode($context);
        });
        // Nothing about a personal webhook is ever an error-level event.
        Log::shouldReceive('error')->never();
    }

    /**
     * Binds the Webhooks service the job resolves, optionally failing every send.
     */
    private function bindWebhooks(?\Throwable $failure = null): void
    {
        app()->instance(Webhooks::class, $this->make(Webhooks::class, [
            'sendToUsers' => function (NotificationModel $notification, array $userIds) use ($failure) {
                $this->deliveries[] = ['notification' => $notification, 'userIds' => $userIds];
                if ($failure !== null) {
                    throw $failure;
                }
            },
        ]));
    }

    /**
     * Hands one stored zp_queue message to the real WebhookQueue.
     */
    private function runQueueOn(string $storedMessage): void
    {
        $queueRepo = $this->make(QueueRepository::class, [
            'listMessageInQueue' => fn (Workers $channel) => $channel === Workers::WEBHOOKS ? [[
                'msghash' => 'row-1',
                'channel' => Workers::WEBHOOKS->value,
                'subject' => DeliverPersonalWebhooks::class,
                'message' => $storedMessage,
                'userId' => 7,
                'projectId' => 5,
                'thedate' => '2026-09-30 10:00:00',
            ]] : [],
            'deleteMessageInQueue' => function (string|array $msghashes) {
                array_push($this->deletedRows, ...(array) $msghashes);

                return true;
            },
        ]);

        (new WebhookQueue($queueRepo, app()->make(DeliverPersonalWebhooks::class)))->processQueue();
    }

    private function makeNotification(): NotificationModel
    {
        $notification = new NotificationModel;
        $notification->projectId = 5;
        $notification->authorId = 1;
        $notification->module = 'tickets';
        $notification->action = 'updated';
        $notification->subject = 'To-Do updated';
        $notification->message = 'Ada updated "Ship it"';
        $notification->url = ['url' => 'https://leantime.example.com/#/tickets/showTicket/42', 'text' => 'Open'];
        $notification->entity = ['id' => 42, 'description' => 'internal notes'];

        return $notification;
    }

    public function test_the_queued_payload_is_rebuilt_into_the_notification_and_sent(): void
    {
        $this->bindWebhooks();

        $this->runQueueOn(serialize(DeliverPersonalWebhooks::payload($this->makeNotification(), [7, 8])));

        $this->assertSame(['row-1'], $this->deletedRows);
        $this->assertCount(1, $this->deliveries);
        $this->assertSame([7, 8], $this->deliveries[0]['userIds']);
        $rebuilt = $this->deliveries[0]['notification'];
        $this->assertSame(5, $rebuilt->projectId);
        $this->assertSame('tickets', $rebuilt->module);
        $this->assertSame('updated', $rebuilt->action);
        $this->assertSame('To-Do updated', $rebuilt->subject);
        $this->assertSame('Ada updated "Ship it"', $rebuilt->message);
        $this->assertSame('https://leantime.example.com/#/tickets/showTicket/42', $rebuilt->url['url']);
        $this->assertFalse(isset($rebuilt->entity), 'The raw entity never travels through the queue');
    }

    public function test_a_notification_without_a_link_is_sent_without_one(): void
    {
        $this->bindWebhooks();
        $notification = $this->makeNotification();
        $notification->url = false;

        $this->runQueueOn(serialize(DeliverPersonalWebhooks::payload($notification, [7])));

        $this->assertFalse($this->deliveries[0]['notification']->url);
    }

    /**
     * @dataProvider unreadablePayloadProvider
     */
    public function test_an_unreadable_payload_is_dropped_without_delivery(string $storedMessage): void
    {
        $this->bindWebhooks();

        $this->runQueueOn($storedMessage);

        $this->assertSame(['row-1'], $this->deletedRows, 'An unreadable row must not stay in the queue');
        $this->assertSame([], $this->deliveries);
        $this->assertCount(1, $this->warnings);
        $this->assertStringStartsWith('Personal webhook job skipped', $this->warnings[0], 'The job rejects the row itself');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function unreadablePayloadProvider(): array
    {
        $valid = [
            'projectId' => 5,
            'module' => 'tickets',
            'action' => 'updated',
            'subject' => 'To-Do updated',
            'message' => 'Ada updated "Ship it"',
            'url' => null,
            'recipientIds' => [7],
        ];

        return [
            'not serialized' => ['garbage'],
            'truncated by the text column' => [substr(serialize($valid), 0, 40)],
            'empty array' => [serialize([])],
            'an object instead of an array' => [serialize(new \ArrayObject($valid))],
            'no recipients' => [serialize(['recipientIds' => []] + $valid)],
            'recipient ids are not integers' => [serialize(['recipientIds' => ['7', null, [8]]] + $valid)],
            'project id is not an integer' => [serialize(['projectId' => '5'] + $valid)],
            'message is not a string' => [serialize(['message' => ['nested']] + $valid)],
        ];
    }

    public function test_a_failing_delivery_never_reaches_the_worker_and_never_logs_its_message(): void
    {
        $this->bindWebhooks(new \RuntimeException('cURL error 7 for https://1.1.1.1/hooks/secret-token?sig=secret-sig'));

        $this->runQueueOn(serialize(DeliverPersonalWebhooks::payload($this->makeNotification(), [7])));

        $this->assertSame(['row-1'], $this->deletedRows, 'Handled without retry: a failed delivery must not stay in the queue');
        $this->assertCount(1, $this->warnings);
        $this->assertStringStartsWith('Personal webhook job failed', $this->warnings[0], 'The job catches the failure itself');
        $this->assertStringContainsString('RuntimeException', $this->warnings[0]);
        $this->assertStringNotContainsString('secret', $this->warnings[0]);
    }
}
