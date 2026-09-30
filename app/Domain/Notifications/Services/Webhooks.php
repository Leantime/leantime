<?php

namespace Leantime\Domain\Notifications\Services;

use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Log;
use Leantime\Domain\Notifications\Jobs\DeliverPersonalWebhooks;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Queue\Workers\Workers;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * Webhooks — delivers project notifications to the personal webhook endpoint a
 * user opted into on their own profile (one JSON setting, usersettings.{id}.webhook,
 * see settingKey()).
 *
 * Sibling to Messengers (per-project Slack/Discord/… hooks configured by
 * managers) and Push (mobile). Deliberately independent of both: a personal
 * endpoint never changes what the project messengers send, and vice versa.
 *
 * Two steps. queueToUsers() runs in the request that raised the notification and
 * only writes zp_queue rows on the WEBHOOKS channel, one DeliverPersonalWebhooks
 * job per recipient — no HTTP, no DNS. The scheduler's WebhookQueue later runs
 * those rows (independently of the DEFAULT queue); each calls sendToUsers() to
 * post through WebhookTransport.
 *
 * Both steps check every recipient themselves (see eligibleEndpoints()): the ids
 * they receive include mention/collaborator bypasses that skipped the project's
 * member filters, and a lot can change before the worker runs.
 *
 * Intentionally carries no @api tags: exposing queueToUsers()/sendToUsers() over
 * JSON-RPC would let any authenticated caller push arbitrary payloads to other
 * users' endpoints.
 */
class Webhooks
{
    public function __construct(
        private WebhookTransport $transport,
        private SettingRepository $settingsRepo,
        private UserRepository $userRepo,
        private ProjectRepository $projectRepo,
        private QueueRepository $queueRepo,
    ) {}

    /**
     * The setting key holding a user's personal webhook. URL and opt-in live in
     * this one JSON value so they are always written together: a failed save can
     * never update one and leave the other behind.
     *
     * @param  int  $userId  The webhook owner.
     * @return string The setting key.
     */
    public static function settingKey(int $userId): string
    {
        return 'usersettings.'.$userId.'.webhook';
    }

    /**
     * Encodes a personal webhook for storage under settingKey().
     *
     * @param  string  $url  The endpoint ('' when none is set).
     * @param  bool  $enabled  Whether delivery is switched on.
     * @return string The JSON value to store.
     */
    public static function encodeSetting(string $url, bool $enabled): string
    {
        return json_encode(['url' => $url, 'enabled' => $enabled], JSON_THROW_ON_ERROR);
    }

    /**
     * Decodes a value stored under settingKey(). Anything missing or unreadable
     * counts as "no webhook", and a webhook is only enabled while it has a URL.
     *
     * @param  mixed  $storedValue  The raw setting value (false/null when unset).
     * @return array{url: string, enabled: bool}
     */
    public static function decodeSetting(mixed $storedValue): array
    {
        $decoded = is_string($storedValue) ? json_decode($storedValue, true) : null;
        $url = is_array($decoded) && is_string($decoded['url'] ?? null) ? $decoded['url'] : '';

        return [
            'url' => $url,
            'enabled' => $url !== '' && ($decoded['enabled'] ?? false) === true,
        ];
    }

    /**
     * Syntax-level check for a personal webhook URL, used both when a user
     * saves the URL and again right before delivery. Delegates to
     * WebhookTransport::isValidEndpointUrl(), the rule the transport itself
     * enforces, so a URL the transport would refuse can never be saved: https
     * only, no embedded credentials, and a public IP literal or a plain
     * hostname (see there).
     *
     * Does not resolve DNS — WebhookTransport checks the resolved addresses at
     * delivery time, since a hostname's addresses can change after the URL was saved.
     *
     * @param  string  $url  The URL to check.
     * @return bool True when the URL is acceptable as a personal webhook endpoint.
     */
    public static function isValidEndpointUrl(string $url): bool
    {
        return WebhookTransport::isValidEndpointUrl($url);
    }

    /**
     * Queues the notification for the personal webhooks of the given users:
     * one WEBHOOKS-channel DeliverPersonalWebhooks row per recipient who may
     * receive it right now (see eligibleEndpoints()), nothing when there is
     * none. Reads settings, users and the project; never contacts an endpoint
     * or resolves DNS.
     *
     * @param  NotificationModel  $notification  The notification being dispatched.
     * @param  array<int|string>  $userIds  Recipient user ids after the project's notification filters.
     */
    public function queueToUsers(NotificationModel $notification, array $userIds): void
    {
        foreach (array_keys($this->eligibleEndpoints($notification->projectId ?? 0, $userIds, useCache: true)) as $recipientId) {
            $this->queueRepo->addMessageToQueue(
                channel: Workers::WEBHOOKS,
                subject: DeliverPersonalWebhooks::class,
                message: serialize(DeliverPersonalWebhooks::payload($notification, [$recipientId])),
                userId: $recipientId,
                projectId: $notification->projectId ?? 0,
            );
        }
    }

    /**
     * Posts the notification to the personal webhook of every given user who
     * may receive it now (see eligibleEndpoints()), reading each endpoint as it
     * is stored at this moment. Runs in the scheduler's WebhookQueue. Never
     * throws for delivery problems.
     *
     * Reads settings and users past the repositories' caches: one scheduler run
     * reuses this service for its whole batch, so a cached copy could predate a
     * change another request made after an earlier row was posted.
     *
     * @param  NotificationModel  $notification  The notification being dispatched.
     * @param  array<int|string>  $userIds  Recipient user ids.
     */
    public function sendToUsers(NotificationModel $notification, array $userIds): void
    {
        foreach ($this->eligibleEndpoints($notification->projectId ?? 0, $userIds, useCache: false) as $userId => $webhookUrl) {
            $this->deliver($webhookUrl, $this->buildPayload($notification, $userId), $userId);
        }
    }

    /**
     * Narrows recipients to those who may receive a personal webhook for the
     * project right now: webhook enabled with a URL, account active with
     * notifications switched on, and access to the project — which must still
     * exist. Checked here rather than trusted from the caller because the ids
     * include mention/collaborator bypasses that skipped the project's member
     * filters. Access follows the project's own rules (team, client, everyone,
     * admin/owner) for the recipient, never the session user.
     *
     * @param  int  $projectId  The project the notification belongs to.
     * @param  array<int|string>  $userIds  Candidate recipient user ids.
     * @param  bool  $useCache  False reads every setting and user row from the database.
     * @return array<int, string> Stored endpoint URL by eligible user id, in input order.
     */
    private function eligibleEndpoints(int $projectId, array $userIds, bool $useCache): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds), fn (int $userId) => $userId > 0)));
        if ($userIds === []) {
            return [];
        }

        $settings = $this->settingsRepo->getSettingsForKeys(array_map(fn (int $userId) => self::settingKey($userId), $userIds), $useCache);

        $enabledEndpoints = [];
        foreach ($userIds as $userId) {
            $webhook = self::decodeSetting($settings[self::settingKey($userId)] ?? null);
            if ($webhook['enabled']) {
                $enabledEndpoints[$userId] = $webhook['url'];
            }
        }

        // Admins and owners pass isUserAssignedToProject() for any id, so the project's existence is checked on its own.
        if ($enabledEndpoints === [] || $this->projectRepo->getProject($projectId) === false) {
            return [];
        }

        return array_filter(
            $enabledEndpoints,
            fn (int $userId) => $this->mayReceiveProjectNotifications($userId, $projectId, $useCache),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Whether the user's account currently allows project notifications to reach
     * them: it exists, is active, has notifications switched on, and can access
     * the project.
     *
     * @param  int  $userId  The recipient.
     * @param  int  $projectId  The project the notification belongs to.
     * @param  bool  $useCache  False re-reads the user row, past the repository's memo.
     */
    private function mayReceiveProjectNotifications(int $userId, int $projectId, bool $useCache): bool
    {
        // Uncached, this also refreshes the repository's memo, so when the container shares that
        // repository with isUserAssignedToProject() below, access is judged by the current role too.
        $user = $this->userRepo->getUser($userId, $useCache);

        if ($user === false
            || strtolower((string) ($user['status'] ?? '')) !== 'a'
            || (int) ($user['notifications'] ?? 0) === 0) {
            return false;
        }

        return $this->projectRepo->isUserAssignedToProject($userId, $projectId);
    }

    /**
     * Builds the JSON body for one recipient. Carries only the notification's
     * presentational fields — never the raw entity, which can hold data the
     * recipient's endpoint has no business receiving.
     *
     * @param  NotificationModel  $notification  The notification being dispatched.
     * @param  int  $recipientId  The user the payload is addressed to.
     * @return array<string, mixed> The payload.
     */
    private function buildPayload(NotificationModel $notification, int $recipientId): array
    {
        $url = is_array($notification->url ?? null) && isset($notification->url['url'])
            ? (string) $notification->url['url']
            : null;

        return [
            'event' => 'notification',
            'module' => $notification->module ?? '',
            'action' => $notification->action,
            'subject' => $notification->subject ?? '',
            'message' => $notification->message ?? '',
            'projectId' => $notification->projectId ?? null,
            'url' => $url,
            'recipientId' => $recipientId,
        ];
    }

    /**
     * Sends one payload through WebhookTransport, which resolves and checks the
     * endpoint's addresses itself. Logs failures by recipient, HTTP status and
     * exception class only: a webhook URL can carry its secret in any part — a
     * per-user token in the hostname as much as in the path or query — and
     * transport exception messages can embed the full URL, so no part of the
     * URL and no exception message is ever logged.
     *
     * @param  string  $webhookUrl  The recipient's stored endpoint.
     * @param  array<string, mixed>  $payload  The JSON body.
     * @param  int  $recipientId  The recipient, for log correlation.
     */
    private function deliver(string $webhookUrl, array $payload, int $recipientId): void
    {
        if (! self::isValidEndpointUrl($webhookUrl)) {
            Log::warning('Personal webhook skipped: endpoint not allowed', ['recipientId' => $recipientId]);

            return;
        }

        try {
            $this->transport->post($webhookUrl, $payload);
        } catch (\Throwable $e) {
            Log::warning('Personal webhook delivery failed', [
                'recipientId' => $recipientId,
                'status' => $e instanceof BadResponseException ? $e->getResponse()->getStatusCode() : null,
                'exception' => get_class($e),
            ]);
        }
    }
}
