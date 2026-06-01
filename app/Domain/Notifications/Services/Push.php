<?php

namespace Leantime\Domain\Notifications\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;

/**
 * Push — sends mobile push notifications via FCM HTTP v1 and Expo Push.
 *
 * Sibling to Messengers (Slack/Discord/Mattermost/Zulip webhook
 * dispatcher). Lives in the same Notifications service folder and is
 * meant to be wired into the same notification trigger paths.
 *
 * Token source: zp_access_tokens rows where push_token is set and
 * push_invalidated_at is null. Each row's push_provider drives which
 * delivery API we hit ('fcm' → Firebase HTTP v1, 'expo' → Expo Push).
 *
 * Token lifecycle:
 *   - Mobile registers on login → push_token populated on the bearer's
 *     zp_access_tokens row.
 *   - Provider rejects token with UNREGISTERED / DeviceNotRegistered /
 *     INVALID_ARGUMENT → we set push_invalidated_at on that row.
 *     Subsequent sends skip it.
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
 * Without these set, FCM sends silently no-op. Expo sends always work
 * since Expo Push doesn't require server-side auth.
 */
class Push
{
    private const FCM_OAUTH_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    private const FCM_OAUTH_TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const FCM_SEND_URL_TEMPLATE = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';

    private const EXPO_SEND_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Cache key for the FCM OAuth access token. Tokens are valid for
     * 3600s; we cache for 3540s (one minute of safety margin).
     */
    private const FCM_TOKEN_CACHE_KEY = 'leantime.push.fcm.oauth_token';

    private const FCM_TOKEN_CACHE_TTL = 3540;

    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
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
     * @param  string  $body   Banner body.
     * @param  array  $data   Custom payload routed to the mobile deeplink
     *                       router. Common shape:
     *                         module          => 'tickets'|'comments'|...
     *                         moduleId        => entity id (string|int)
     *                         parentTicketId  => for comments: their ticket id
     *                         url             => web fallback URL
     *                       Values get string-cast for FCM compatibility
     *                       (FCM data fields must be strings).
     */
    public function send(array $userIds, string $title, string $body, array $data = []): void
    {
        if (empty($userIds)) {
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

        foreach ($rows as $row) {
            try {
                if ($row->push_provider === 'fcm') {
                    $this->sendFcm($row, $title, $body, $data);
                } elseif ($row->push_provider === 'expo') {
                    $this->sendExpo($row, $title, $body, $data);
                }
            } catch (\Throwable $e) {
                // One bad row doesn't kill the broadcast. The provider-
                // specific code marks push_invalidated_at on known-dead
                // tokens (UNREGISTERED / DeviceNotRegistered); anything
                // else gets logged for ops to look at later.
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

        $data = [
            'module' => $notification->entity ?? '',
            'moduleId' => isset($notification->moduleId) ? (string) $notification->moduleId : '',
            'url' => $notification->url ?? '',
        ];

        $this->send($userIds, $title, $body, $data);
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

        // FCM data fields must be strings. Cast everything explicitly
        // — easy to forget and surface as "InvalidValue" errors later.
        $stringData = [];
        foreach ($data as $k => $v) {
            $stringData[(string) $k] = is_scalar($v) ? (string) $v : json_encode($v);
        }

        $payload = [
            'message' => [
                'token' => $row->push_token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $stringData,
            ],
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
            $bodyText = $response !== null ? (string) $response->getBody() : '';

            // FCM signals dead tokens via UNREGISTERED (404) or
            // INVALID_ARGUMENT (400 with that specific error code).
            // Mark the row invalidated so we stop trying.
            if (
                str_contains($bodyText, 'UNREGISTERED')
                || str_contains($bodyText, 'INVALID_ARGUMENT')
                || ($response !== null && $response->getStatusCode() === 404)
            ) {
                $this->invalidateToken((int) $row->id);

                return;
            }

            throw $e;
        }
    }

    /**
     * Expo Push send. No server-side auth — Expo's HTTP API is open
     * (the push tokens themselves are the capability). Just POST.
     */
    private function sendExpo($row, string $title, string $body, array $data): void
    {
        $payload = [
            'to' => $row->push_token,
            'sound' => 'default',
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ];

        try {
            $response = $this->httpClient->post(self::EXPO_SEND_URL, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 10,
            ]);

            // Expo returns 200 even for invalid tokens — the per-ticket
            // status lives in the response body. Parse it to detect
            // DeviceNotRegistered.
            $bodyText = (string) $response->getBody();
            if (str_contains($bodyText, 'DeviceNotRegistered')) {
                $this->invalidateToken((int) $row->id);
            }
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($response !== null && $response->getStatusCode() === 400) {
                $this->invalidateToken((int) $row->id);

                return;
            }

            throw $e;
        }
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
