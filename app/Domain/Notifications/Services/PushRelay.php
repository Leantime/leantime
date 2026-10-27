<?php

namespace Leantime\Domain\Notifications\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\AppSettings;
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
            // Keep only what the app needs to open the right screen.
            $data = array_intersect_key($data, array_flip(['module', 'moduleId', 'parentTicketId', 'projectId', 'type', 'threadId', 'url']));
        }

        $messages = [];
        $rowsById = [];
        foreach ($rows as $row) {
            $messages[] = [
                'token' => $row->push_token,
                'platform' => (string) ($row->push_platform ?? ''),
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'threadId' => $data['threadId'] ?? '',
            ];
            $rowsById[$row->push_token] = (int) $row->id;
        }

        foreach (array_chunk($messages, self::MAX_MESSAGES_PER_REQUEST) as $chunk) {
            $this->post($chunk, $license, $rowsById);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $messages
     * @param  array<string, int>  $rowsById  push_token → zp_access_tokens.id
     */
    private function post(array $messages, string $license, array $rowsById): void
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
                'timeout' => 10,
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
        // Dead tokens are marked exactly as the direct FCM path marks them.
        $dead = [];
        foreach ($results as $result) {
            if (($result['status'] ?? '') === 'invalid_token' && isset($rowsById[$result['token'] ?? ''])) {
                $dead[] = $rowsById[$result['token']];
            }
        }
        if ($dead !== []) {
            DB::table('zp_access_tokens')->whereIn('id', $dead)->update(['push_invalidated_at' => now()]);
        }
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
