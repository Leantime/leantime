<?php

namespace Tests\Unit\app\Domain\Mcp\Services;

use Leantime\Core\Mcp\Auth\McpAuthenticator;
use Leantime\Core\Mcp\Auth\McpPrincipal;
use Leantime\Core\Mcp\McpException;
use Leantime\Domain\Mcp\Repositories\Mcp as McpRepository;
use Leantime\Domain\Mcp\Services\McpServer;
use Leantime\Domain\Mcp\Services\ToolExecutor;
use Leantime\Domain\Mcp\Services\ToolRegistry;

class McpServerTest extends \Unit\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'mysql']);
    }

    public function test_initialize_returns_server_capabilities(): void
    {
        $authenticator = $this->createMock(McpAuthenticator::class);
        $registry = $this->createMock(ToolRegistry::class);
        $executor = $this->createMock(ToolExecutor::class);
        $repo = $this->createMock(McpRepository::class);

        $principal = new McpPrincipal(1, 40, 'admin', 12, 'agent', ['*'], 2);
        $authenticator->method('authenticate')->willReturn($principal);
        $repo->method('createRequestLog')->willReturn(10);

        $server = new McpServer($authenticator, $registry, $executor, $repo);
        $request = \Leantime\Core\Http\IncomingRequest::create(
            '/mcp',
            'POST',
            [],
            [],
            [],
            [],
            json_encode([
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'initialize',
                'params' => ['protocolVersion' => '2025-03-26'],
            ])
        );
        $request->headers->set('Authorization', 'Bearer token');

        $response = $server->handle($request);
        $payload = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('2.0', $payload['jsonrpc']);
        $this->assertSame('2025-03-26', $payload['result']['protocolVersion']);
        $this->assertArrayHasKey('tools', $payload['result']['capabilities']);
    }

    public function test_tools_list_uses_registry_definitions(): void
    {
        $authenticator = $this->createMock(McpAuthenticator::class);
        $registry = $this->createMock(ToolRegistry::class);
        $executor = $this->createMock(ToolExecutor::class);
        $repo = $this->createMock(McpRepository::class);

        $principal = new McpPrincipal(1, 40, 'admin', 12, 'agent', ['*'], 2);
        $authenticator->method('authenticate')->willReturn($principal);
        $repo->method('createRequestLog')->willReturn(11);
        $registry->method('definitions')->willReturn([
            ['name' => 'projects.list', 'title' => 'List Projects'],
        ]);

        $server = new McpServer($authenticator, $registry, $executor, $repo);
        $request = \Leantime\Core\Http\IncomingRequest::create(
            '/mcp',
            'POST',
            [],
            [],
            [],
            [],
            json_encode([
                'jsonrpc' => '2.0',
                'id' => 'abc',
                'method' => 'tools/list',
            ])
        );
        $request->headers->set('Authorization', 'Bearer token');

        $response = $server->handle($request);
        $payload = json_decode($response->getContent(), true);

        $this->assertSame('projects.list', $payload['result']['tools'][0]['name']);
    }

    public function test_unauthorized_request_returns_jsonrpc_error(): void
    {
        $authenticator = $this->createMock(McpAuthenticator::class);
        $registry = $this->createMock(ToolRegistry::class);
        $executor = $this->createMock(ToolExecutor::class);
        $repo = $this->createMock(McpRepository::class);

        $authenticator->method('authenticate')->willThrowException(new McpException('Missing bearer token', -32001, 401));

        $server = new McpServer($authenticator, $registry, $executor, $repo);
        $request = \Leantime\Core\Http\IncomingRequest::create(
            '/mcp',
            'POST',
            [],
            [],
            [],
            [],
            json_encode([
                'jsonrpc' => '2.0',
                'id' => 99,
                'method' => 'initialize',
            ])
        );

        $response = $server->handle($request);
        $payload = json_decode($response->getContent(), true);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame(-32001, $payload['error']['code']);
    }
}
