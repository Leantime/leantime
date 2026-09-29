<?php

namespace Leantime\Domain\Notifications\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Support\OutboundUrlGuard;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;

/**
 * Webhooks — delivers project notifications to the personal webhook endpoint a
 * user opted into on their own profile (one JSON setting, usersettings.{id}.webhook,
 * see settingKey()).
 *
 * Sibling to Messengers (per-project Slack/Discord/… hooks configured by
 * managers) and Push (mobile). Deliberately independent of both: a personal
 * endpoint never changes what the project messengers send, and vice versa.
 *
 * Delivery runs inline in the request that raised the notification, so every
 * send uses short timeouts and swallows its own failures.
 *
 * Intentionally carries no @api tags: exposing sendToUsers() over JSON-RPC would
 * let any authenticated caller push arbitrary payloads to other users' endpoints.
 */
class Webhooks
{
    /**
     * Upper bound for a stored webhook URL.
     */
    public const MAX_URL_LENGTH = 2048;

    /**
     * Seconds to wait for the TCP/TLS connection to the endpoint.
     */
    private const CONNECT_TIMEOUT_SECONDS = 2;

    /**
     * Seconds the whole request (connect + send + response) may take.
     */
    private const TOTAL_TIMEOUT_SECONDS = 5;

    public function __construct(
        private Client $httpClient,
        private SettingRepository $settingsRepo,
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
     * saves the URL and again right before delivery. Requires https, a valid
     * host (a dotted hostname or a public IP literal), no embedded credentials,
     * and at most MAX_URL_LENGTH characters.
     *
     * Does not resolve DNS — the SSRF guard does that at delivery time, since a
     * hostname's addresses can change after the URL was saved.
     *
     * @param  string  $url  The URL to check.
     * @return bool True when the URL is acceptable as a personal webhook endpoint.
     */
    public static function isValidEndpointUrl(string $url): bool
    {
        if ($url === '' || strlen($url) > self::MAX_URL_LENGTH) {
            return false;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($url);
        if ($parts === false || strtolower($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return false;
        }

        // user:pass@host would be sent as basic auth to whoever owns the host; refuse it outright.
        if (isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        $host = trim($parts['host'], '[]');
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return OutboundUrlGuard::isIpAllowed($host);
        }

        // Single-label names (localhost, intranet) are never public endpoints.
        return str_contains($host, '.')
            && filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }

    /**
     * Posts the notification to the personal webhook of every given user who
     * opted in. Users without the opt-in, without a URL, or whose URL fails the
     * checks are skipped. Never throws for delivery problems.
     *
     * @param  NotificationModel  $notification  The notification being dispatched.
     * @param  array<int|string>  $userIds  Already-filtered recipient user ids.
     */
    public function sendToUsers(NotificationModel $notification, array $userIds): void
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds), fn (int $userId) => $userId > 0)));
        if ($userIds === []) {
            return;
        }

        $settings = $this->settingsRepo->getSettingsForKeys(array_map(fn (int $userId) => self::settingKey($userId), $userIds));

        foreach ($userIds as $userId) {
            $webhook = self::decodeSetting($settings[self::settingKey($userId)] ?? null);
            if (! $webhook['enabled']) {
                continue;
            }

            $this->deliver($webhook['url'], $this->buildPayload($notification, $userId), $userId);
        }
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
     * Sends one payload. Logs failures by host only: a webhook URL's path and
     * query commonly carry its secret, and Guzzle exception messages embed the
     * full URL, so neither the URL nor the exception message is ever logged.
     *
     * @param  string  $webhookUrl  The recipient's stored endpoint.
     * @param  array<string, mixed>  $payload  The JSON body.
     * @param  int  $recipientId  The recipient, for log correlation.
     */
    private function deliver(string $webhookUrl, array $payload, int $recipientId): void
    {
        $host = (string) parse_url($webhookUrl, PHP_URL_HOST);

        if (! self::isValidEndpointUrl($webhookUrl) || ! OutboundUrlGuard::isAllowedUrl($webhookUrl)) {
            Log::warning('Personal webhook skipped: endpoint not allowed', ['recipientId' => $recipientId, 'host' => $host]);

            return;
        }

        try {
            $this->httpClient->post($webhookUrl, [
                // Same SSRF re-check on every hop, and never downgrade to plain http.
                'allow_redirects' => array_merge(OutboundUrlGuard::redirectOptions(), ['protocols' => ['https']]),
                'connect_timeout' => self::CONNECT_TIMEOUT_SECONDS,
                'timeout' => self::TOTAL_TIMEOUT_SECONDS,
                'json' => $payload,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Personal webhook delivery failed', [
                'recipientId' => $recipientId,
                'host' => $host,
                'status' => $e instanceof BadResponseException ? $e->getResponse()->getStatusCode() : null,
                'exception' => get_class($e),
            ]);
        }
    }
}
