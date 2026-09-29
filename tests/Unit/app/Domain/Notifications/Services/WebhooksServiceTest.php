<?php

namespace Unit\app\Domain\Notifications\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Notifications\Services\Webhooks;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Unit\TestCase;

/**
 * Personal notification webhooks: who gets a POST, what it carries, and that
 * a bad or failing endpoint never escapes the service. Endpoints are public IP
 * literals so nothing here depends on live DNS; the HTTP layer is a real Guzzle
 * client over a MockHandler, so the request the endpoint would see is asserted
 * as sent.
 */
class WebhooksServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const SECRET_ENDPOINT = 'https://1.1.1.1/hooks/secret-token?sig=secret-sig';

    /**
     * Sent requests, filled by Guzzle's history middleware.
     *
     * @var array<int, array<string, mixed>>
     */
    private array $sentRequests = [];

    /**
     * Builds the service over a mocked HTTP stack and a settings map.
     *
     * @param  array<string, string>  $settings  Setting key => stored value.
     * @param  array<int, mixed>  $responses  Queued responses/exceptions for the mock handler.
     */
    private function makeService(array $settings, array $responses = []): Webhooks
    {
        $this->sentRequests = [];
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->sentRequests));

        $settingsRepo = $this->make(SettingRepository::class, [
            'getSettingsForKeys' => fn (array $keys) => array_intersect_key($settings, array_flip($keys)),
        ]);

        return new Webhooks(new Client(['handler' => $stack]), $settingsRepo);
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
        $notification->entity = ['id' => 42, 'headline' => 'Ship it', 'description' => 'internal notes', 'password' => 'never-send'];

        return $notification;
    }

    /**
     * The stored personal webhook setting: one JSON value holding URL and opt-in.
     *
     * @return array<string, string>
     */
    private function webhookSetting(int $userId, string $url, bool $enabled): array
    {
        return ['usersettings.'.$userId.'.webhook' => json_encode(['url' => $url, 'enabled' => $enabled])];
    }

    /**
     * @return array<string, string>
     */
    private function optedIn(int $userId, string $url): array
    {
        return $this->webhookSetting($userId, $url, true);
    }

    public function test_posts_json_payload_to_an_opted_in_users_endpoint(): void
    {
        $service = $this->makeService($this->optedIn(7, self::SECRET_ENDPOINT), [new Response(204)]);

        $service->sendToUsers($this->makeNotification(), [7]);

        $this->assertCount(1, $this->sentRequests);
        $request = $this->sentRequests[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(self::SECRET_ENDPOINT, (string) $request->getUri());
        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));

        $this->assertSame([
            'event' => 'notification',
            'module' => 'tickets',
            'action' => 'updated',
            'subject' => 'To-Do updated',
            'message' => 'Ada updated "Ship it"',
            'projectId' => 5,
            'url' => 'https://leantime.example.com/#/tickets/showTicket/42',
            'recipientId' => 7,
        ], json_decode((string) $request->getBody(), true), 'The payload carries presentational fields only — never the raw entity');
    }

    public function test_request_uses_short_timeouts_and_https_only_guarded_redirects(): void
    {
        $service = $this->makeService($this->optedIn(7, self::SECRET_ENDPOINT), [new Response(200)]);

        $service->sendToUsers($this->makeNotification(), [7]);

        $options = $this->sentRequests[0]['options'];
        $this->assertGreaterThan(0, $options['connect_timeout']);
        $this->assertLessThanOrEqual(2, $options['connect_timeout']);
        $this->assertGreaterThan(0, $options['timeout']);
        $this->assertLessThanOrEqual(5, $options['timeout']);
        $this->assertSame(['https'], $options['allow_redirects']['protocols']);
        $this->assertIsCallable($options['allow_redirects']['on_redirect'], 'Every redirect hop must be re-checked by the SSRF guard');
    }

    public function test_only_users_who_opted_in_and_stored_a_url_are_posted(): void
    {
        $settings = $this->optedIn(1, 'https://1.1.1.1/one')
            + $this->webhookSetting(2, 'https://1.1.1.1/two', false)
            + $this->webhookSetting(3, '', true)
            + ['usersettings.5.webhook' => 'not json']
            + ['usersettings.6.webhook' => json_encode(['url' => 'https://1.1.1.1/six', 'enabled' => '1'])];
        // User 4 has no webhook setting at all.
        $service = $this->makeService($settings, array_fill(0, 6, new Response(200)));

        $service->sendToUsers($this->makeNotification(), [1, 2, 3, 4, 5, 6]);

        $this->assertCount(1, $this->sentRequests);
        $this->assertSame('https://1.1.1.1/one', (string) $this->sentRequests[0]['request']->getUri());
        $this->assertSame(1, json_decode((string) $this->sentRequests[0]['request']->getBody(), true)['recipientId']);
    }

    public function test_each_recipient_gets_their_own_payload_once(): void
    {
        $settings = $this->optedIn(1, 'https://1.1.1.1/one') + $this->optedIn(2, 'https://8.8.8.8/two');
        $service = $this->makeService($settings, [new Response(200), new Response(200), new Response(200)]);

        $service->sendToUsers($this->makeNotification(), [1, '2', 1]);

        $this->assertCount(2, $this->sentRequests, 'Duplicate recipient ids must not produce duplicate posts');
        $recipientIds = array_map(fn ($sent) => json_decode((string) $sent['request']->getBody(), true)['recipientId'], $this->sentRequests);
        $this->assertSame([1, 2], $recipientIds);
    }

    public function test_posts_to_a_public_ipv6_endpoint(): void
    {
        $service = $this->makeService($this->optedIn(7, 'https://[2606:4700:4700::1111]/hooks/v6'), [new Response(200)]);

        $service->sendToUsers($this->makeNotification(), [7]);

        $this->assertCount(1, $this->sentRequests, 'A public bracketed IPv6 literal must pass the SSRF guard');
        $this->assertSame('https://[2606:4700:4700::1111]/hooks/v6', (string) $this->sentRequests[0]['request']->getUri());
    }

    public function test_endpoints_that_fail_the_url_checks_or_ssrf_guard_are_never_contacted(): void
    {
        // Values that could only be stored by bypassing the save-time validation.
        $settings = $this->optedIn(1, 'https://127.0.0.1/hook')
            + $this->optedIn(2, 'https://169.254.169.254/latest/meta-data')
            + $this->optedIn(3, 'https://10.0.0.5/hook')
            + $this->optedIn(4, 'http://1.1.1.1/plain-http')
            + $this->optedIn(5, 'https://user:pass@1.1.1.1/hook')
            + $this->optedIn(6, 'https://[::1]/hook')
            + $this->optedIn(7, 'https://[fd12:3456::1]/hook')
            + $this->optedIn(8, 'https://[fe80::1]/hook')
            + $this->optedIn(9, 'https://[::ffff:10.0.0.5]/hook');
        $service = $this->makeService($settings, array_fill(0, 9, new Response(200)));

        $service->sendToUsers($this->makeNotification(), [1, 2, 3, 4, 5, 6, 7, 8, 9]);

        $this->assertCount(0, $this->sentRequests);
    }

    public function test_no_recipients_is_a_no_op(): void
    {
        $lookups = 0;
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSettingsForKeys' => function () use (&$lookups) {
                $lookups++;

                return [];
            },
        ]);
        $this->sentRequests = [];
        $stack = HandlerStack::create(new MockHandler([]));
        $stack->push(Middleware::history($this->sentRequests));

        (new Webhooks(new Client(['handler' => $stack]), $settingsRepo))->sendToUsers($this->makeNotification(), []);

        $this->assertSame(0, $lookups, 'No recipients means no settings lookup');
        $this->assertCount(0, $this->sentRequests);
    }

    public function test_http_failures_are_swallowed_logged_without_the_url_secret_and_do_not_stop_other_recipients(): void
    {
        $logged = [];
        Log::shouldReceive('warning')->andReturnUsing(function ($message, $context = []) use (&$logged) {
            $logged[] = $message.' '.json_encode($context);
        });

        $settings = $this->optedIn(1, self::SECRET_ENDPOINT)
            + $this->optedIn(2, 'https://8.8.8.8/hooks/other-secret')
            + $this->optedIn(3, 'https://1.1.1.1/hooks/third');
        $service = $this->makeService($settings, [
            new Response(500),
            new ConnectException('cURL error 28: timed out for https://8.8.8.8/hooks/other-secret', new Request('POST', 'https://8.8.8.8/hooks/other-secret')),
            new Response(200),
        ]);

        $service->sendToUsers($this->makeNotification(), [1, 2, 3]);

        $this->assertCount(3, $this->sentRequests, 'A failing endpoint must not stop delivery to the next recipient');
        $this->assertCount(2, $logged, 'Each failed delivery is logged once');
        $this->assertStringContainsString('1.1.1.1', $logged[0]);
        $this->assertStringContainsString('500', $logged[0]);
        foreach ($logged as $line) {
            $this->assertStringNotContainsString('secret', $line, 'Webhook path/query must never reach the logs');
        }
    }

    /**
     * @dataProvider endpointUrlProvider
     */
    public function test_is_valid_endpoint_url(string $url, bool $expected): void
    {
        $this->assertSame($expected, Webhooks::isValidEndpointUrl($url));
    }

    public static function endpointUrlProvider(): array
    {
        return [
            'https hostname with path' => ['https://hooks.example.com/services/T000/B000/XXXX', true],
            'https with port and query' => ['https://example.com:8443/hook?token=abc', true],
            'public ipv4 literal' => ['https://1.1.1.1/hook', true],
            'public ipv6 literal' => ['https://[2606:4700:4700::1111]/hook', true],
            'exactly the length limit' => ['https://example.com/'.str_repeat('a', 2028), true],
            'over the length limit' => ['https://example.com/'.str_repeat('a', 2029), false],
            'empty' => ['', false],
            'plain http' => ['http://hooks.example.com/hook', false],
            'other scheme' => ['ftp://example.com/hook', false],
            'javascript' => ['javascript:alert(1)', false],
            'user and password' => ['https://user:pass@example.com/hook', false],
            'user only' => ['https://token@example.com/hook', false],
            'single-label host' => ['https://localhost/hook', false],
            'loopback literal' => ['https://127.0.0.1/hook', false],
            'private literal' => ['https://10.0.0.1/hook', false],
            'metadata literal' => ['https://169.254.169.254/latest', false],
            'loopback ipv6 literal' => ['https://[::1]/hook', false],
            'no host' => ['https:///hook', false],
            'space in host' => ['https://exa mple.com/hook', false],
            'not a url' => ['not a url', false],
        ];
    }
}
