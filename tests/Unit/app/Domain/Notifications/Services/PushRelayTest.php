<?php

namespace Unit\app\Domain\Notifications\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Http\TrustedAppUrl;
use Leantime\Domain\Notifications\Services\PushRelay;
use Leantime\Domain\Plugins\Models\InstalledPlugin;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Services\Users as UserService;
use Unit\TestCase;

/**
 * The relay path: on only when the administrator opted in AND AdvancedAuth is
 * enabled; authenticates with the AdvancedAuth marketplace license; honours the
 * content-free setting; chunks large fan-outs.
 */
class PushRelayTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, \Psr\Http\Message\RequestInterface> */
    private array $requests = [];

    /**
     * Constructor arguments for a relay over a mocked HTTP stack.
     *
     * @param  array<string, mixed>  $settings
     * @return array<int, mixed>
     */
    private function relayDependencies(array $settings, bool $pluginEnabled, ?string $license, array $responses = []): array
    {
        $mock = new MockHandler($responses ?: [new Response(200, [], json_encode(['results' => []]))]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->requests));

        $plugin = new InstalledPlugin;
        $plugin->foldername = 'AdvancedAuth';
        $plugin->license = $license;

        return [
            new Client(['handler' => $stack]),
            $this->make(SettingRepository::class, ['getSetting' => fn ($key) => $settings[$key] ?? false]),
            $this->make(SettingService::class, ['getCompanyId' => fn () => 'instance-uuid']),
            $this->make(PluginService::class, [
                'isEnabled' => fn () => $pluginEnabled,
                'getAllPlugins' => fn () => [$plugin],
            ]),
            $this->make(UserService::class, ['getNumberOfUsers' => fn () => 7]),
            $this->make(AppSettings::class, ['appVersion' => '3.12.0']),
            $this->make(TrustedAppUrl::class, ['get' => fn () => 'https://app.example.com/']),
        ];
    }

    private function minimalRelay(): PushRelay
    {
        return $this->relay([PushRelay::SETTING_ENABLED => '1', PushRelay::SETTING_CONTENT => 'minimal'], true, 'LIC');
    }

    /** @param array<string, mixed> $settings */
    private function relay(array $settings, bool $pluginEnabled, ?string $license, array $responses = []): PushRelay
    {
        return new PushRelay(...$this->relayDependencies($settings, $pluginEnabled, $license, $responses));
    }

    private function rows(int $n): array
    {
        return array_map(fn ($i) => (object) ['id' => $i, 'push_token' => "tok-$i", 'push_platform' => 'ios'], range(1, $n));
    }

    public function test_enabled_only_when_opted_in_and_plugin_enabled(): void
    {
        $this->assertFalse($this->relay([], true, 'key')->isEnabled(), 'off by default');
        $this->assertFalse($this->relay([PushRelay::SETTING_ENABLED => '1'], false, 'key')->isEnabled(), 'needs AdvancedAuth');
        $this->assertTrue($this->relay([PushRelay::SETTING_ENABLED => '1'], true, 'key')->isEnabled());
    }

    public function test_sends_license_and_instance_identity_with_full_content(): void
    {
        $relay = $this->relay([PushRelay::SETTING_ENABLED => '1'], true, 'LIC-123');
        $relay->send($this->rows(2), 'Fix login', 'Sam assigned this to you', ['module' => 'tickets', 'moduleId' => '9', 'threadId' => 'ticket-9']);

        $this->assertCount(1, $this->requests);
        $req = $this->requests[0]['request'];
        $this->assertSame('Bearer LIC-123', $req->getHeaderLine('Authorization'));
        $this->assertSame('instance-uuid', $req->getHeaderLine('X-Instance-Id'));
        $this->assertSame('7', $req->getHeaderLine('X-User-Count'));
        $this->assertSame('3.12.0', $req->getHeaderLine('X-Leantime-Version'));

        $body = json_decode((string) $req->getBody(), true);
        $this->assertCount(2, $body['messages']);
        $this->assertSame('tok-1', $body['messages'][0]['token']);
        $this->assertSame('Fix login', $body['messages'][0]['title']);
        $this->assertSame('Sam assigned this to you', $body['messages'][0]['body']);
        $this->assertSame('ticket-9', $body['messages'][0]['threadId']);
    }

    public function test_content_free_mode_strips_text_but_keeps_routing(): void
    {
        $relay = $this->relay([PushRelay::SETTING_ENABLED => '1', PushRelay::SETTING_CONTENT => 'minimal'], true, 'LIC');
        $relay->send($this->rows(1), 'Fix login', 'Sam: looks good', ['module' => 'comments', 'moduleId' => '4', 'parentTicketId' => '9', 'url' => 'https://x', 'tier' => '1', 'category' => 'comment']);

        $msg = json_decode((string) $this->requests[0]['request']->getBody(), true)['messages'][0];
        $this->assertSame('Leantime', $msg['title']);
        $this->assertStringNotContainsString('Sam', $msg['body']);
        $this->assertStringNotContainsString('Fix login', json_encode($msg));
        $this->assertSame('9', $msg['data']['parentTicketId']);
        $this->assertSame('comments', $msg['data']['module']);
        $this->assertSame('1', $msg['data']['tier'], 'delivery fields are not text and survive content-free mode');
        $this->assertSame('comment', $msg['data']['category']);
    }

    /**
     * A notification's own link can carry text: a comment posted over a JSON-RPC GET keeps
     * its params (base64 of the comment) in the URL. Content-free mode never forwards it.
     */
    public function test_content_free_mode_rebuilds_the_link_from_the_ids(): void
    {
        $leaky = 'https://host/api/jsonrpc?method=comments.addComment&params=eyJ0ZXh0Ijoic2VjcmV0In0=';
        $this->minimalRelay()->send($this->rows(1), 'Fix login', 'Sam: secret', ['module' => 'comments', 'moduleId' => '4', 'parentTicketId' => '42', 'url' => $leaky]);

        $posted = (string) $this->requests[0]['request']->getBody();
        $msg = json_decode($posted, true)['messages'][0];
        $this->assertSame('https://app.example.com/dashboard/home#/tickets/showTicket/42', $msg['data']['url']);
        $this->assertStringNotContainsString('jsonrpc', $posted);
        $this->assertStringNotContainsString('params=', $posted);
        $this->assertStringNotContainsString('secret', $posted);

        $this->requests = [];
        $this->minimalRelay()->send($this->rows(1), 'Fix login', 'x', ['module' => 'tickets', 'moduleId' => '7', 'url' => $leaky]);
        $this->assertSame('https://app.example.com/dashboard/home#/tickets/showTicket/7', json_decode((string) $this->requests[0]['request']->getBody(), true)['messages'][0]['data']['url']);
    }

    /**
     * Callers that predate the routing-data change send no parentTicketId for a comment;
     * the task id is read from the comment url's path (never its query) before the url is dropped.
     */
    public function test_content_free_mode_finds_a_comments_task_in_its_own_link(): void
    {
        $url = 'https://host/dashboard/home#/tickets/showTicket/42?projectId=5&text=secret';
        $this->minimalRelay()->send($this->rows(1), 'Fix login', 'Sam: secret', ['module' => 'comments', 'moduleId' => '4', 'url' => $url]);

        $posted = (string) $this->requests[0]['request']->getBody();
        $message = json_decode($posted, true)['messages'][0];
        $this->assertSame('42', $message['data']['parentTicketId']);
        $this->assertSame('https://app.example.com/dashboard/home#/tickets/showTicket/42', $message['data']['url']);
        $this->assertStringNotContainsString('secret', $posted);

        // A task id that only appears in a query parameter is not trusted.
        $this->assertNull(PushRelay::ticketIdFromUrl('https://host/api/jsonrpc?url=/tickets/showTicket/42'));
        $this->assertNull(PushRelay::ticketIdFromUrl('https://host/dashboard/home#/tickets/showTicket/42abc'));
        $this->assertSame('7', PushRelay::ticketIdFromUrl('https://host/tickets/showTicket/7'));
    }

    public function test_content_free_mode_links_the_app_itself_for_everything_else(): void
    {
        $this->minimalRelay()->send($this->rows(1), 'Goal', 'x', ['module' => 'goalcanvas', 'moduleId' => '5', 'url' => 'https://host/goals?projectId=3']);

        $this->assertSame('https://app.example.com', json_decode((string) $this->requests[0]['request']->getBody(), true)['messages'][0]['data']['url']);
    }

    public function test_content_free_mode_sends_no_link_for_an_id_that_is_not_a_number(): void
    {
        $this->minimalRelay()->send($this->rows(1), 'Fix login', 'x', ['module' => 'tickets', 'moduleId' => '7?x=1', 'url' => 'https://host/x']);

        $msg = json_decode((string) $this->requests[0]['request']->getBody(), true)['messages'][0];
        $this->assertArrayNotHasKey('url', $msg['data']);
        $this->assertStringNotContainsString('host/x', json_encode($msg));
    }

    public function test_no_license_means_no_request(): void
    {
        $this->relay([PushRelay::SETTING_ENABLED => '1'], true, null)->send($this->rows(1), 't', 'b', []);
        $this->assertCount(0, $this->requests);
    }

    public function test_large_fan_out_is_chunked(): void
    {
        $ok = new Response(200, [], json_encode(['results' => []]));
        $relay = $this->relay([PushRelay::SETTING_ENABLED => '1'], true, 'LIC', [$ok, $ok, $ok]);
        $relay->send($this->rows(250), 't', 'b', []);

        $this->assertCount(3, $this->requests);
        $sizes = array_map(fn ($r) => count(json_decode((string) $r['request']->getBody(), true)['messages']), $this->requests);
        $this->assertSame([100, 100, 50], $sizes);
    }

    public function test_relay_refusal_does_not_throw(): void
    {
        $relay = $this->relay([PushRelay::SETTING_ENABLED => '1'], true, 'LIC', [new Response(403, [], '{"error":"license"}')]);
        $relay->send($this->rows(1), 't', 'b', []);
        $this->assertCount(1, $this->requests);
    }

    /**
     * One device token can sit on several access-token rows (one per sign-in). When the relay
     * reports it dead, every row holding it is marked, not just the last one seen.
     */
    public function test_a_dead_token_invalidates_every_row_holding_it(): void
    {
        $results = ['results' => [
            ['token' => 'tok-dup', 'status' => 'invalid_token'],
            ['token' => 'tok-ok', 'status' => 'sent'],
            ['token' => 'tok-unknown', 'status' => 'invalid_token'],
        ]];
        $invalidated = null;
        $relay = $this->construct(
            PushRelay::class,
            $this->relayDependencies([PushRelay::SETTING_ENABLED => '1'], true, 'LIC', [new Response(200, [], json_encode($results))]),
            ['invalidateRows' => function (array $rowIds) use (&$invalidated) {
                $invalidated = $rowIds;
            }]
        );

        $rows = [
            (object) ['id' => 1, 'push_token' => 'tok-dup', 'push_platform' => 'ios'],
            (object) ['id' => 2, 'push_token' => 'tok-ok', 'push_platform' => 'ios'],
            (object) ['id' => 3, 'push_token' => 'tok-dup', 'push_platform' => 'android'],
        ];
        $relay->send($rows, 't', 'b', []);

        $this->assertSame([1, 3], $invalidated, 'Both rows with the dead token, nothing for the healthy or unknown ones');
    }
}
