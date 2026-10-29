<?php

namespace Unit\app\Domain\Notifications\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Leantime\Core\Configuration\AppSettings;
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
        ];
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
        $relay->send($this->rows(1), 'Fix login', 'Sam: looks good', ['module' => 'comments', 'moduleId' => '4', 'parentTicketId' => '9', 'url' => 'https://x']);

        $msg = json_decode((string) $this->requests[0]['request']->getBody(), true)['messages'][0];
        $this->assertSame('Leantime', $msg['title']);
        $this->assertStringNotContainsString('Sam', $msg['body']);
        $this->assertStringNotContainsString('Fix login', json_encode($msg));
        $this->assertSame('9', $msg['data']['parentTicketId']);
        $this->assertSame('comments', $msg['data']['module']);
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
