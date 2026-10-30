<?php

namespace Leantime\Domain\Notifications\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;

/**
 * Push — sends mobile push notifications via FCM HTTP v1.
 *
 * Sibling to Messengers (Slack/Discord/Mattermost/Zulip webhook
 * dispatcher). Lives in the same Notifications service folder and is
 * meant to be wired into the same notification trigger paths.
 *
 * Token source: zp_access_tokens rows where push_token is set and
 * push_invalidated_at is null. The push_provider column is retained
 * for forward compatibility but only 'fcm' is dispatched today.
 *
 * Token lifecycle:
 *   - Mobile registers on login → push_token populated on the bearer's
 *     zp_access_tokens row.
 *   - FCM rejects token with UNREGISTERED / INVALID_ARGUMENT → we set
 *     push_invalidated_at on that row. Subsequent sends skip it.
 *   - Mobile logout → Sanctum revokes the access_tokens row entirely;
 *     push registration dies with it. No prune cron needed.
 *
 * Configuration (env / .env):
 *   - LEAN_PUSH_FCM_CREDENTIALS_PATH — absolute path to the Firebase
 *     service-account JSON (downloaded from Firebase Console > Project
 *     Settings > Service Accounts > Generate New Private Key).
 *   - LEAN_PUSH_FCM_PROJECT_ID      — Firebase project id (the
 *     'project_id' field inside the same JSON works; this lets
 *     operators override it cleanly).
 *
 * Without these set, sends silently no-op.
 */
class Push
{
    private const FCM_OAUTH_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    private const FCM_OAUTH_TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const FCM_SEND_URL_TEMPLATE = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';

    /**
     * Cache key for the FCM OAuth access token. Tokens are valid for
     * 3600s; we cache for 3540s (one minute of safety margin).
     */
    private const FCM_TOKEN_CACHE_KEY = 'leantime.push.fcm.oauth_token';

    private const FCM_TOKEN_CACHE_TTL = 3540;

    /**
     * Delivery tier, carried in data as 'tier'. Tier 1 is directly for the person
     * (assigned to you, mentioned, a reply, a comment on your task, added to a
     * project) and arrives with sound. Tier 2 is everything else and arrives
     * quietly (no sound, screen stays dark). Project fan-out defaults to tier 2.
     */
    public const TIER_DIRECT = '1';

    public const TIER_QUIET = '2';

    /**
     * Notification category, carried in data as 'category'. The app registers
     * action buttons per category (reply on a comment, done / start timer on a
     * task); 'none' gets no buttons.
     */
    public const CATEGORY_COMMENT = 'comment';

    public const CATEGORY_TASK = 'task';

    public const CATEGORY_NONE = 'none';

    /** Android notification channels the app creates, one per tier. */
    private const ANDROID_CHANNEL_DIRECT = 'leantime-direct';

    private const ANDROID_CHANNEL_QUIET = 'leantime-quiet';

    private Client $httpClient;

    private PushRelay $relay;

    public function __construct(Client $httpClient, PushRelay $relay)
    {
        $this->httpClient = $httpClient;
        $this->relay = $relay;
    }

    /**
     * Which path delivers push on this instance:
     *   - 'direct' — Firebase credentials are configured (Cloud)
     *   - 'relay'  — the administrator turned on Leantime's relay and AdvancedAuth is enabled
     *   - 'off'    — neither; sends no-op
     *
     * Direct wins when both are set, so Cloud never routes through the relay.
     */
    public function sendPath(): string
    {
        if ($this->fcmConfigured()) {
            return 'direct';
        }

        return $this->relay->isEnabled() ? 'relay' : 'off';
    }

    private function fcmConfigured(): bool
    {
        $credentialsPath = (string) env('LEAN_PUSH_FCM_CREDENTIALS_PATH', '');
        $projectId = (string) env('LEAN_PUSH_FCM_PROJECT_ID', '');

        return $credentialsPath !== '' && $projectId !== '' && is_readable($credentialsPath);
    }

    /**
     * Dispatch a push notification to all valid push tokens for the
     * given recipient user IDs.
     *
     * @param  array<int>  $userIds  Recipient user IDs. The send is
     *                               fan-out across every (user, device)
     *                               pair — a user with N devices gets
     *                               N pushes.
     * @param  string  $title  Banner title.
     * @param  string  $body  Banner body.
     * @param  array  $data  Custom payload routed to the mobile deeplink
     *                       router. Common shape:
     *                       module          => 'tickets'|'comments'|...
     *                       moduleId        => entity id (string|int)
     *                       parentTicketId  => for comments: their ticket id
     *                       url             => web fallback URL
     *                       tier            => TIER_DIRECT | TIER_QUIET (default quiet)
     *                       category        => comment | task | none (derived from
     *                       module when missing)
     *                       Values get string-cast for FCM compatibility
     *                       (FCM data fields must be strings).
     */
    public function send(array $userIds, string $title, string $body, array $data = []): void
    {
        if (empty($userIds)) {
            return;
        }

        $path = $this->sendPath();
        if ($path === 'off') {
            return;
        }

        $rows = \Illuminate\Support\Facades\DB::table('zp_access_tokens')
            ->whereIn('tokenable_id', $userIds)
            ->whereNotNull('push_token')
            ->whereNull('push_invalidated_at')
            ->get(['id', 'push_token', 'push_provider', 'push_platform', 'tokenable_id']);

        if ($rows->isEmpty()) {
            return;
        }

        // FCM data fields must be strings; the relay forwards them as-is, so cast once here.
        $stringData = [];
        foreach ($data as $k => $v) {
            $stringData[(string) $k] = is_scalar($v) ? (string) $v : json_encode($v);
        }
        $stringData = self::withDefaults($stringData);

        if ($path === 'relay') {
            // One request for the whole fan-out; the relay reports dead tokens back.
            $this->relay->send($rows->where('push_provider', 'fcm'), $title, $body, $stringData);

            return;
        }

        foreach ($rows as $row) {
            // push_provider is retained on the row for forward compat
            // but only 'fcm' is dispatched. Unknown providers no-op
            // silently rather than throw — keeps the table flexible
            // without breaking the broadcast.
            if ($row->push_provider !== 'fcm') {
                continue;
            }

            try {
                $this->sendFcm($row, $title, $body, $stringData);
            } catch (\Throwable $e) {
                // One bad row doesn't kill the broadcast. sendFcm marks
                // push_invalidated_at on known-dead tokens (UNREGISTERED
                // / INVALID_ARGUMENT); anything else gets logged for ops
                // to look at later.
                Log::warning('Push send failed for access_token #'.$row->id.': '.$e->getMessage());
            }
        }
    }

    /**
     * Convenience for callers that already have a NotificationModel:
     * extracts title/body/data from it and forwards to send().
     */
    public function sendFromNotification(NotificationModel $notification, array $userIds): void
    {
        $title = $notification->subject !== '' ? $notification->subject : 'Leantime';
        $body = $notification->message !== '' ? $notification->message : '';

        $this->send($userIds, $title, $body, self::buildData($notification));
    }

    /**
     * The custom data payload for a notification: everything the app needs to route a tap
     * and group related notifications, as strings (FCM data values must be strings).
     *
     *   module          'tickets' | 'comments' | 'goalcanvas' | ...
     *   moduleId        the entity id (for comments: the comment's id)
     *   parentTicketId  comments on a task: the task's id, so a tap can open the task
     *   projectId       the project the notification belongs to
     *   type            the action that triggered it ('commented', 'assigned', ...), if set
     *   threadId        grouping key, one thread per task ('ticket-<id>'), when it has one
     *   url             web fallback URL
     *   category        'comment' | 'task' | 'none' — which action buttons the app offers
     *   tier            '2' here: project fan-out is quiet. Listeners for things directly
     *                   for one person (AssignmentNotifier) send tier '1' themselves.
     *
     * Empty values are left out so the app can tell "missing" from "zero".
     *
     * @return array<string, string>
     */
    public static function buildData(NotificationModel $notification): array
    {
        // Module is a literal string ('tickets'/'comments'/'goalcanvas'/…) on the model.
        $module = (string) ($notification->module ?? '');

        // The entity is an array (legacy) or an object (newer models); read both the same way.
        $entity = $notification->entity ?? null;
        $field = static function (string $key) use ($entity): mixed {
            if (is_array($entity)) {
                return $entity[$key] ?? null;
            }
            if (is_object($entity)) {
                return $entity->{$key} ?? null;
            }

            return null;
        };

        $moduleId = (string) ($field('id') ?? '');

        // A comment's own id doesn't open anything in the app; its host task does. Comments
        // carry the host module ('ticket' after normalization) and the host id in moduleId.
        $parentTicketId = '';
        if ($module === 'comments' && in_array($field('module'), ['ticket', 'tickets'], true)) {
            $parentTicketId = (string) ($field('moduleId') ?? '');
        }

        $taskId = $module === 'tickets' ? $moduleId : $parentTicketId;

        // url is false when there's no web link, ['url' => …, 'text' => …] when there is.
        $url = is_array($notification->url) && isset($notification->url['url'])
            ? (string) $notification->url['url']
            : '';

        $projectId = isset($notification->projectId) ? (int) $notification->projectId : 0;

        $data = [
            'module' => $module,
            'moduleId' => $moduleId,
            'parentTicketId' => $parentTicketId,
            'projectId' => $projectId > 0 ? (string) $projectId : '',
            'type' => $notification->action,
            'threadId' => $taskId !== '' ? 'ticket-'.$taskId : '',
            'url' => $url,
            'category' => self::categoryFor($module),
            'tier' => self::TIER_QUIET,
        ];

        // module and moduleId stay even when empty: the app's router expects them.
        return array_filter(
            $data,
            static fn (string $value, string $key): bool => $value !== '' || in_array($key, ['module', 'moduleId', 'url'], true),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * FCM HTTP v1 send. Requires LEAN_PUSH_FCM_CREDENTIALS_PATH and
     * LEAN_PUSH_FCM_PROJECT_ID to be set. Silently no-ops if not
     * configured — admins who haven't set up FCM shouldn't get errors
     * thrown at them; their pushes just don't deliver.
     */
    private function sendFcm($row, string $title, string $body, array $data): void
    {
        $credentialsPath = (string) env('LEAN_PUSH_FCM_CREDENTIALS_PATH', '');
        $projectId = (string) env('LEAN_PUSH_FCM_PROJECT_ID', '');
        if ($credentialsPath === '' || $projectId === '' || ! is_readable($credentialsPath)) {
            return;
        }

        $accessToken = $this->fetchFcmAccessToken($credentialsPath);
        if ($accessToken === null) {
            return;
        }

        // $data is already string-valued with tier/category defaults (see send()).
        $payload = [
            'message' => ['token' => $row->push_token]
                + self::fcmMessage((string) ($row->push_platform ?? ''), $title, $body, $data),
        ];

        try {
            $this->httpClient->post(
                sprintf(self::FCM_SEND_URL_TEMPLATE, $projectId),
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.$accessToken,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                    'timeout' => 10,
                ]
            );
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $bodyText = (string) $response->getBody();

            // FCM signals dead tokens via UNREGISTERED (404) or
            // INVALID_ARGUMENT (400 with that specific error code).
            // Mark the row invalidated so we stop trying.
            if (
                str_contains($bodyText, 'UNREGISTERED')
                || str_contains($bodyText, 'INVALID_ARGUMENT')
                || $response->getStatusCode() === 404
            ) {
                $this->invalidateToken((int) $row->id);

                return;
            }

            throw $e;
        }
    }

    /**
     * Fill the delivery fields a caller may leave out: 'tier' defaults to quiet and
     * is coerced to '1' | '2'; 'category' is derived from the module when missing
     * or unknown. Values stay strings.
     *
     * @param  array<string, string>  $data
     * @return array<string, string>
     */
    public static function withDefaults(array $data): array
    {
        $data['tier'] = ($data['tier'] ?? '') === self::TIER_DIRECT ? self::TIER_DIRECT : self::TIER_QUIET;
        if (! in_array($data['category'] ?? '', [self::CATEGORY_COMMENT, self::CATEGORY_TASK, self::CATEGORY_NONE], true)) {
            $data['category'] = self::categoryFor((string) ($data['module'] ?? ''));
        }

        return $data;
    }

    /**
     * Which action buttons a notification about this module can carry: comments
     * (incl. mentions and replies) → 'comment', tasks → 'task', anything else → 'none'.
     */
    public static function categoryFor(string $module): string
    {
        return match ($module) {
            'comments' => self::CATEGORY_COMMENT,
            'tickets' => self::CATEGORY_TASK,
            default => self::CATEGORY_NONE,
        };
    }

    /**
     * The FCM v1 message for one device, minus the token. The shape depends on the
     * device platform:
     *
     *  - Android: DATA-ONLY (no 'notification' block) at high priority, with title,
     *    body and the tier's channelId inside data. The app renders it itself so it
     *    can attach action buttons; a 'notification' block would be drawn by the
     *    system tray without them.
     *  - iOS (and unknown platforms): the usual notification + data, plus APNs
     *    fields the system needs to show buttons and rank the alert: 'category'
     *    (leantime-comment | leantime-task, omitted for 'none'), 'thread-id' for
     *    grouping, 'interruption-level' (active for tier 1, passive for tier 2 so it
     *    makes no sound and leaves the screen dark) and 'relevance-score' (what the
     *    scheduled summary features first).
     *
     * The PushRelay plugin builds the same shape for self-hosted instances; keep
     * the two in step.
     *
     * @param  array<string, string>  $data  String-valued, with tier/category set (see withDefaults())
     * @return array<string, mixed>
     */
    public static function fcmMessage(string $platform, string $title, string $body, array $data): array
    {
        $data = self::withDefaults($data);
        $direct = $data['tier'] === self::TIER_DIRECT;

        if ($platform === 'android') {
            return [
                'data' => $data + [
                    'title' => $title,
                    'body' => $body,
                    'channelId' => $direct ? self::ANDROID_CHANNEL_DIRECT : self::ANDROID_CHANNEL_QUIET,
                ],
                'android' => ['priority' => 'high'],
            ];
        }

        $aps = [
            'interruption-level' => $direct ? 'active' : 'passive',
            'relevance-score' => self::relevanceScore($data),
        ];
        if (($data['threadId'] ?? '') !== '') {
            $aps['thread-id'] = $data['threadId'];
        }
        if ($data['category'] !== self::CATEGORY_NONE) {
            $aps['category'] = 'leantime-'.$data['category'];
        }

        return [
            'notification' => ['title' => $title, 'body' => $body],
            'data' => $data,
            'apns' => ['payload' => ['aps' => $aps]],
        ];
    }

    /**
     * How prominently iOS features the alert in a scheduled summary: 1.0 for the
     * things someone is most likely waiting on (assigned, mentioned, a reply),
     * 0.5 for the rest of tier 1, 0.2 for quiet tier 2.
     *
     * @param  array<string, string>  $data
     */
    private static function relevanceScore(array $data): float
    {
        if (($data['tier'] ?? '') !== self::TIER_DIRECT) {
            return 0.2;
        }

        return in_array($data['type'] ?? '', ['assigned', 'mentioned', 'mention', 'replied', 'reply'], true) ? 1.0 : 0.5;
    }

    /**
     * Mark an access-token row's push registration as invalid. Called
     * when a provider tells us the token is dead. Doesn't touch the
     * access token itself — only the push fields.
     */
    private function invalidateToken(int $accessTokenId): void
    {
        \Illuminate\Support\Facades\DB::table('zp_access_tokens')
            ->where('id', $accessTokenId)
            ->update(['push_invalidated_at' => now()]);
    }

    /**
     * Get a Google OAuth access token for FCM. Builds a JWT with the
     * service-account credentials, exchanges it for an OAuth token,
     * and caches the token until just before expiry.
     *
     * Returns null if anything fails — caller silently skips the send.
     */
    private function fetchFcmAccessToken(string $credentialsPath): ?string
    {
        $cached = Cache::get(self::FCM_TOKEN_CACHE_KEY);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        try {
            $json = file_get_contents($credentialsPath);
            if ($json === false) {
                return null;
            }
            $creds = json_decode($json, true);
            if (! is_array($creds) || ! isset($creds['client_email'], $creds['private_key'])) {
                return null;
            }

            $now = time();
            $header = ['alg' => 'RS256', 'typ' => 'JWT'];
            $claims = [
                'iss' => $creds['client_email'],
                'scope' => self::FCM_OAUTH_SCOPE,
                'aud' => self::FCM_OAUTH_TOKEN_URL,
                'exp' => $now + 3600,
                'iat' => $now,
            ];

            $headerB64 = $this->base64UrlEncode(json_encode($header));
            $claimsB64 = $this->base64UrlEncode(json_encode($claims));
            $signingInput = $headerB64.'.'.$claimsB64;

            $signature = '';
            $privateKey = openssl_pkey_get_private($creds['private_key']);
            if ($privateKey === false) {
                return null;
            }
            $signed = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            if (! $signed) {
                return null;
            }
            $signatureB64 = $this->base64UrlEncode($signature);
            $jwt = $signingInput.'.'.$signatureB64;

            $response = $this->httpClient->post(self::FCM_OAUTH_TOKEN_URL, [
                'form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ],
                'timeout' => 10,
            ]);

            $tokenResponse = json_decode((string) $response->getBody(), true);
            if (! is_array($tokenResponse) || ! isset($tokenResponse['access_token'])) {
                return null;
            }

            $accessToken = (string) $tokenResponse['access_token'];
            Cache::put(self::FCM_TOKEN_CACHE_KEY, $accessToken, self::FCM_TOKEN_CACHE_TTL);

            return $accessToken;
        } catch (\Throwable $e) {
            Log::warning('Push: failed to fetch FCM OAuth token: '.$e->getMessage());

            return null;
        }
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
