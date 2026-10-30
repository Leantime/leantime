<?php

namespace Leantime\Domain\Notifications\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Services\Users as UserService;

/**
 * PushRelay — sends push notifications through Leantime's relay instead of
 * straight to Firebase.
 *
 * A phone's push token belongs to the Firebase project built into the app,
 * so only a server holding that project's credentials can send to it. Cloud
 * holds them; self-hosted servers don't. The relay is a service on Cloud that
 * sends on a self-hosted server's behalf.
 *
 * The relay stores nothing about users or devices: each request carries the
 * tokens and messages for that one send. It authenticates the instance the
 * same way AdvancedAuth is licensed — by the plugin's marketplace license
 * key — so push works exactly where mobile sign-in works, and stops with it.
 *
 * Opt-in: an administrator turns the relay on in company settings, since it
 * means notification content leaves their server. They also choose whether
 * the relay gets full content or a content-free "New activity" message.
 * Full content forwards the notification as the instance built it, link
 * included. Content-free mode forwards no text at all: the link is rebuilt
 * from the routing ids, because a notification link can carry text itself
 * (a comment posted over a JSON-RPC GET keeps its params in the URL).
 *
 * Delivery fields ride in data and are never text: 'tier' ('1' direct with
 * sound, '2' quiet) and 'category' ('comment' | 'task' | 'none', which action
 * buttons the app shows). The relay turns them into the per-platform FCM shape
 * exactly as Push::fcmMessage() does (APNs interruption level, relevance score
 * and category; data-only messages for Android). Both survive content-free mode.
 *
 * Configuration (env / .env), optional:
 *   - LEAN_PUSH_RELAY_URL — the relay endpoint; defaults to Leantime's.
 */
class PushRelay
{
    public const SETTING_ENABLED = 'companysettings.push.relayEnabled';

    public const SETTING_CONTENT = 'companysettings.push.relayContent';

    public const CONTENT_FULL = 'full';

    public const CONTENT_MINIMAL = 'minimal';

    public const DEFAULT_RELAY_URL = 'https://accounts.leantime.io/pushRelay/send';

    /** The marketplace plugin whose license the relay verifies. */
    public const LICENSE_PLUGIN = 'AdvancedAuth';

    private const MAX_MESSAGES_PER_REQUEST = 100;

    public function __construct(
        private Client $httpClient,
        private SettingRepository $settingsRepo,
        private SettingService $settingsService,
        private PluginService $pluginService,
        private UserService $userService,
        private AppSettings $appSettings,
        private TrustedAppUrl $trustedAppUrl,
    ) {}

    /**
     * Whether this instance sends push through the relay: the administrator
     * turned it on, and the licensed plugin is enabled.
     */
    public function isEnabled(): bool
    {
        if ($this->settingsRepo->getSetting(self::SETTING_ENABLED) !== '1') {
            return false;
        }

        return $this->pluginService->isEnabled(self::LICENSE_PLUGIN);
    }

    /**
     * Content level the administrator chose: full, or minimal (no titles,
     * bodies or names leave the server; the app loads details on tap).
     */
    public function contentLevel(): string
    {
        $level = $this->settingsRepo->getSetting(self::SETTING_CONTENT);

        return $level === self::CONTENT_MINIMAL ? self::CONTENT_MINIMAL : self::CONTENT_FULL;
    }

    /**
     * Send one message to the given access-token rows through the relay.
     *
     * @param  iterable<object>  $rows  zp_access_tokens rows (id, push_token, push_platform)
     * @param  array<string, string>  $data  String-valued data payload
     */
    public function send(iterable $rows, string $title, string $body, array $data): void
    {
        $license = $this->license();
        if ($license === null) {
            Log::warning('Push relay: no '.self::LICENSE_PLUGIN.' license key found; skipping send.');

            return;
        }

        if ($this->contentLevel() === self::CONTENT_MINIMAL) {
            $title = 'Leantime';
            $body = __('notification.push_minimal_body');
            // A comment's host task is the only thing worth linking. Callers that predate
            // the routing-data change don't send parentTicketId, but the comment's own url
            // names the task in its path, so read the id from there before the url is
            // dropped. Only the path segment counts; nothing from a query string.
            if (($data['module'] ?? '') === 'comments' && ! ctype_digit((string) ($data['parentTicketId'] ?? ''))) {
                $hostTask = self::ticketIdFromUrl((string) ($data['url'] ?? ''));
                if ($hostTask !== null) {
                    $data['parentTicketId'] = $hostTask;
                }
            }
            // Keep only what the app needs to open the right screen. The caller's url is
            // never forwarded: it can carry text (query parameters of the request that made
            // the change); a link is rebuilt from the ids instead.
            $data = array_intersect_key($data, array_flip(['module', 'moduleId', 'parentTicketId', 'projectId', 'type', 'threadId', 'tier', 'category']));
            $canonical = $this->canonicalUrl($data);
            if ($canonical !== null) {
                $data['url'] = $canonical;
            }
        }

        $messages = [];
        $rowsByToken = [];
        foreach ($rows as $row) {
            $messages[] = [
                'token' => $row->push_token,
                'platform' => (string) ($row->push_platform ?? ''),
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'threadId' => $data['threadId'] ?? '',
            ];
            // The same device token can sit on several rows (one per sign-in); a dead token kills them all.
            $rowsByToken[$row->push_token][] = (int) $row->id;
        }

        foreach (array_chunk($messages, self::MAX_MESSAGES_PER_REQUEST) as $chunk) {
            $this->post($chunk, $license, $rowsByToken);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<string, array<int, int>>  $rowsByToken  push_token → every zp_access_tokens.id holding it
     */
    private function post(array $messages, string $license, array $rowsByToken): void
    {
        $url = (string) env('LEAN_PUSH_RELAY_URL', '') ?: self::DEFAULT_RELAY_URL;

        try {
            $response = $this->httpClient->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$license,
                    // The same identity the marketplace license check carries, so the relay
                    // can verify the license exactly as core does in Plugins::validLicense.
                    'X-Instance-Id' => $this->settingsService->getCompanyId(),
                    'X-User-Count' => (string) $this->userService->getNumberOfUsers(activeOnly: true, includeApi: false),
                    'X-Leantime-Version' => $this->appSettings->appVersion,
                    'Content-Type' => 'application/json',
                ],
                'json' => ['messages' => $messages],
                // The relay forwards up to MAX_MESSAGES_PER_REQUEST messages to Firebase before it
                // answers, so this is the budget for a whole batch, not one request.
                'timeout' => 30,
            ]);
        } catch (RequestException $e) {
            $status = $e->getResponse()?->getStatusCode();
            if ($status === 401 || $status === 403) {
                Log::warning('Push relay refused this instance (HTTP '.$status.'); check the '.self::LICENSE_PLUGIN.' license.');
            } else {
                Log::warning('Push relay request failed: '.$e->getMessage());
            }

            return;
        } catch (\Throwable $e) {
            Log::warning('Push relay request failed: '.$e->getMessage());

            return;
        }

        $decoded = json_decode((string) $response->getBody(), true);
        $results = is_array($decoded['results'] ?? null) ? $decoded['results'] : [];

        // One result per token: sent | invalid_token | rate_limited | error.
        // Dead tokens are marked exactly as the direct FCM path marks them, on every row holding them.
        $dead = [];
        foreach ($results as $result) {
            if (($result['status'] ?? '') === 'invalid_token' && isset($rowsByToken[$result['token'] ?? ''])) {
                $dead = array_merge($dead, $rowsByToken[$result['token']]);
            }
        }
        if ($dead !== []) {
            $this->invalidateRows(array_values(array_unique($dead)));
        }
    }

    /**
     * The task id named in a Leantime task link's PATH (…/tickets/showTicket/<id>),
     * or null. The query string is never read, so text smuggled in parameters
     * can't become an id.
     */
    public static function ticketIdFromUrl(string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return null;
        }

        // Leantime links carry the route in the path or, on the dashboard, in the
        // fragment (…/home#/tickets/showTicket/42?projectId=5). Either way only the
        // route part is read; a fragment's own query is cut off, and the URL's
        // query string is never looked at.
        $routes = [];
        if (! empty($parts['path'])) {
            $routes[] = $parts['path'];
        }
        if (! empty($parts['fragment'])) {
            $routes[] = explode('?', $parts['fragment'], 2)[0];
        }

        foreach ($routes as $route) {
            if (preg_match('~/tickets/showTicket/(\d+)(?:/|$)~', $route, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    /**
     * A link built only from the trusted app URL and the routing ids: the task
     * for a task or a comment on one, otherwise the app itself. Null when the
     * app URL is unknown or an id isn't a plain number.
     *
     * @param  array<string, string>  $data
     */
    private function canonicalUrl(array $data): ?string
    {
        $base = rtrim((string) ($this->trustedAppUrl->get() ?? (defined('BASE_URL') ? BASE_URL : '')), '/');
        if ($base === '') {
            return null;
        }

        $taskId = (string) (($data['module'] ?? '') === 'tickets' ? ($data['moduleId'] ?? '') : ($data['parentTicketId'] ?? ''));
        if ($taskId === '') {
            return $base;
        }

        return ctype_digit($taskId) ? $base.'/dashboard/home#/tickets/showTicket/'.$taskId : null;
    }

    /**
     * Mark access-token rows' push registration dead. Only the push fields are touched.
     *
     * @param  array<int, int>  $rowIds
     */
    protected function invalidateRows(array $rowIds): void
    {
        DB::table('zp_access_tokens')->whereIn('id', $rowIds)->update(['push_invalidated_at' => now()]);
    }

    /**
     * The AdvancedAuth marketplace license key, or null when the plugin isn't
     * an installed marketplace plugin with a key.
     */
    private function license(): ?string
    {
        $plugins = $this->pluginService->getAllPlugins(true) ?: [];
        foreach ($plugins as $plugin) {
            if (strcasecmp((string) $plugin->foldername, self::LICENSE_PLUGIN) !== 0) {
                continue;
            }
            $key = trim((string) ($plugin->license ?? ''));

            return $key !== '' ? $key : null;
        }

        return null;
    }
}
