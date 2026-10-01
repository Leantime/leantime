<?php

namespace Leantime\Domain\Notifications\Jobs;

use Illuminate\Support\Facades\Log;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Notifications\Services\Webhooks;

/**
 * DeliverPersonalWebhooks — the zp_queue job (WEBHOOKS channel) that posts one
 * notification to a recipient's personal webhook. Webhooks::queueToUsers()
 * writes one row per recipient, so no endpoint is contacted inside the request
 * that raised the notification; the scheduler's WebhookQueue runs the rows.
 *
 * The queued payload (see payload()) holds scalars only: the notification's
 * presentational fields and the recipient ids (one per row). Endpoint URLs are
 * never queued — Webhooks::sendToUsers() re-reads each recipient's endpoint,
 * opt-in, status and project access when the job runs.
 *
 * Every row runs at most once: WebhookQueue removes it whatever happens. So
 * handle() never throws — an unreadable row or a failed delivery is logged by
 * exception class only and dropped, never retried.
 */
class DeliverPersonalWebhooks
{
    public function __construct(
        private Webhooks $webhookService,
    ) {}

    /**
     * Builds the queue payload for a notification: scalars only, never the raw
     * entity or any endpoint.
     *
     * @param  NotificationModel  $notification  The notification being dispatched.
     * @param  array<int>  $recipientIds  Users whose personal webhooks should receive it.
     * @return array{projectId: int, module: string, action: string, subject: string, message: string, url: string|null, recipientIds: array<int>}
     */
    public static function payload(NotificationModel $notification, array $recipientIds): array
    {
        return [
            'projectId' => $notification->projectId ?? 0,
            'module' => $notification->module ?? '',
            'action' => $notification->action,
            'subject' => $notification->subject ?? '',
            'message' => $notification->message ?? '',
            'url' => is_array($notification->url ?? null) && isset($notification->url['url']) ? (string) $notification->url['url'] : null,
            'recipientIds' => array_values(array_map('intval', $recipientIds)),
        ];
    }

    /**
     * Runs one queued delivery. Always reports the row as handled.
     *
     * @param  mixed  $payload  The unserialized queue message, as built by payload().
     * @return bool Always true: the row is done whatever happened — it is never retried.
     */
    public function handle(mixed $payload): bool
    {
        $notification = $this->notificationFrom($payload);
        $recipientIds = is_array($payload) && is_array($payload['recipientIds'] ?? null)
            ? array_values(array_filter($payload['recipientIds'], fn ($recipientId) => is_int($recipientId) && $recipientId > 0))
            : [];

        if ($notification === null || $recipientIds === []) {
            Log::warning('Personal webhook job skipped: unreadable queue payload');

            return true;
        }

        try {
            $this->webhookService->sendToUsers($notification, $recipientIds);
        } catch (\Throwable $e) {
            // Class only: exception messages can embed an endpoint URL and its secret.
            Log::warning('Personal webhook job failed', ['projectId' => $notification->projectId, 'exception' => get_class($e)]);
        }

        return true;
    }

    /**
     * Rebuilds the minimal notification Webhooks::sendToUsers() needs from a
     * queued payload, or null when the payload is not one payload() built.
     *
     * @param  mixed  $payload  The unserialized queue message.
     */
    private function notificationFrom(mixed $payload): ?NotificationModel
    {
        if (! is_array($payload) || ! is_int($payload['projectId'] ?? null)) {
            return null;
        }

        foreach (['module', 'action', 'subject', 'message'] as $field) {
            if (! is_string($payload[$field] ?? null)) {
                return null;
            }
        }

        $url = $payload['url'] ?? null;
        if ($url !== null && ! is_string($url)) {
            return null;
        }

        $notification = new NotificationModel;
        $notification->projectId = $payload['projectId'];
        $notification->module = $payload['module'];
        $notification->action = $payload['action'];
        $notification->subject = $payload['subject'];
        $notification->message = $payload['message'];
        $notification->url = $url === null ? false : ['url' => $url, 'text' => ''];

        return $notification;
    }
}
