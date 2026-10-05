<?php

namespace Unit\app\Domain\Api\Controllers;

use Leantime\Core\Events\EventDispatcher;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Middleware\AuthenticateSession;
use Leantime\Domain\Api\Controllers\Jsonrpc;
use Leantime\Domain\Api\Events\ApiMethodCalled;
use Unit\TestCase;

/**
 * ApiMethodCalled is reported only for token-authenticated (API key / Bearer / MCP) callers —
 * never for the web UI's own session-backed RPC calls.
 */
class JsonrpcApiMethodCalledTest extends TestCase
{
    private array $dispatcherSnapshot = [];

    /** @var array<int, ApiMethodCalled> */
    private array $calls = [];

    protected function setUp(): void
    {
        parent::setUp();

        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach (['eventRegistry', 'available_hooks', 'patternMatchCache', 'compiledPatternCache', 'eventRegistryVersion'] as $prop) {
            $this->dispatcherSnapshot[$prop] = $reflection->getProperty($prop)->getValue();
        }

        $this->calls = [];
        EventDispatcher::add_event_listener(ApiMethodCalled::class, function (ApiMethodCalled $event) {
            $this->calls[] = $event;
        });
    }

    protected function tearDown(): void
    {
        $reflection = new \ReflectionClass(EventDispatcher::class);
        foreach ($this->dispatcherSnapshot as $prop => $value) {
            $reflection->getProperty($prop)->setValue(null, $value);
        }

        parent::tearDown();
    }

    /**
     * Runs the controller's reporting step for a parsed method on a request to $path.
     *
     * @param  bool|null  $tokenAuthenticated  The AuthCheck marker (null = not set, i.e. a web session).
     */
    private function reportCall(string $path, ?bool $tokenAuthenticated): void
    {
        $request = IncomingRequest::create($path, 'POST');
        if ($tokenAuthenticated !== null) {
            $request->attributes->set(AuthenticateSession::TOKEN_AUTHENTICATED, $tokenAuthenticated);
        }

        $controllerReflection = new \ReflectionClass(Jsonrpc::class);
        $controller = $controllerReflection->newInstanceWithoutConstructor();
        $controllerReflection->getProperty('incomingRequest')->setValue($controller, $request);

        $controllerReflection->getMethod('dispatchApiMethodCalled')->invoke($controller, [
            'module' => 'Tickets',
            'service' => 'Tickets',
            'method' => 'patch',
        ]);
    }

    public function test_api_key_or_bearer_calls_are_reported(): void
    {
        $this->reportCall('/api/jsonrpc', true);

        $this->assertCount(1, $this->calls);
        $this->assertSame('tickets.tickets.patch', $this->calls[0]->method);
        $this->assertSame('api', $this->calls[0]->channel);
    }

    public function test_mcp_endpoint_calls_report_the_mcp_channel(): void
    {
        $this->reportCall('/mcp', true);

        $this->assertCount(1, $this->calls);
        $this->assertSame('mcp', $this->calls[0]->channel);
    }

    public function test_browser_session_calls_are_not_reported(): void
    {
        $this->reportCall('/api/jsonrpc', null);
        $this->reportCall('/api/jsonrpc', false);

        $this->assertSame([], $this->calls);
    }
}
