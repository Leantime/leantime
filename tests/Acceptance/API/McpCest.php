<?php

namespace Acceptance\API;

use Codeception\Attribute\Group;
use Codeception\Scenario;
use PHPUnit\Framework\Assert;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Install;

/**
 * MCP server contract test (laravel/mcp over the /mcp HTTP transport).
 *
 * Sibling to BearerApiCest: same Bearer-token mint (a row in zp_access_tokens), but against the
 * MCP endpoint the McpServer plugin registers instead of the JSON-RPC API. Exists because the MCP
 * tool layer is exercised by AI clients (Claude Code, MCP inspector), not by the web UI, so
 * nothing else in CI notices when it breaks — e.g. tools calling since-removed service methods, or
 * the protocol-version handshake rejecting current clients (both happened in 2026-07 and were only
 * caught by live testing).
 *
 * Group `mcp` is NOT part of the default CI groups: the McpServer plugin lives in the private
 * app/Plugins submodule, which is absent in OSS CI. Every test therefore skips itself when the
 * endpoint 404s. Run locally (plugins checked out) with:
 *
 *   docker compose ... exec leantime-dev php vendor/bin/codecept run -g mcp --steps
 *
 * Each test is self-contained (token + plugin row minted per test) because the Db module rolls
 * back haveInDatabase() rows after each test.
 */
class McpCest
{
    private const MCP_PATH = '/mcp';

    public function _before(AcceptanceTester $I, Install $installPage)
    {
        // Fresh install — same fixture as ApiCest/BearerApiCest.
        $installPage->install('test@leantime.io', 'Test123456!', 'John', 'Smith', 'Smith & Co');
    }

    #[Group('mcp')]
    public function mcpEndpointRequiresAuth(AcceptanceTester $I, Scenario $scenario)
    {
        $this->enableMcpPluginOrSkip($I, $scenario);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost(self::MCP_PATH, json_encode($this->initializeRequest('2025-06-18'), JSON_THROW_ON_ERROR));
        $I->seeResponseCodeIs(401);
    }

    #[Group('mcp')]
    public function mcpHandshakeNegotiatesProtocolVersions(AcceptanceTester $I, Scenario $scenario)
    {
        $this->enableMcpPluginOrSkip($I, $scenario);
        $this->mintBearerToken($I);

        // Baseline: a revision from laravel/mcp v0.1.1's built-in list.
        $response = $this->mcp($I, $this->initializeRequest('2025-06-18'));
        Assert::assertSame('2025-06-18', $response['result']['protocolVersion'] ?? null, 'initialize failed: '.json_encode($response));
        Assert::assertNotEmpty($response['result']['serverInfo']['name'] ?? null);

        // Regression gate for the Claude Code handshake: current clients request 2025-11-25.
        // v0.1.1 rejects unknown revisions instead of downgrading (spec says downgrade), so the
        // server class must keep newer revisions in $supportedProtocolVersion (plugins#60).
        $response = $this->mcp($I, $this->initializeRequest('2025-11-25'));
        Assert::assertArrayNotHasKey(
            'error',
            $response,
            'Server rejected protocol 2025-11-25 — Claude Code cannot connect. '
                .'LeantimeMcpServer::$supportedProtocolVersion must include it: '.json_encode($response)
        );
    }

    #[Group('mcp')]
    public function mcpListsAllRegisteredTools(AcceptanceTester $I, Scenario $scenario)
    {
        $this->enableMcpPluginOrSkip($I, $scenario);
        $this->mintBearerToken($I);

        // tools/list paginates (15 per page in v0.1.1) — walk every cursor.
        $toolNames = [];
        $cursor = null;
        $guard = 0;
        do {
            $params = $cursor === null ? new \stdClass : ['cursor' => $cursor];
            $response = $this->mcp($I, [
                'jsonrpc' => '2.0',
                'id' => 2,
                'method' => 'tools/list',
                'params' => $params,
            ]);
            Assert::assertArrayNotHasKey('error', $response, 'tools/list failed: '.json_encode($response));
            foreach ($response['result']['tools'] ?? [] as $tool) {
                $toolNames[] = $tool['name'];
            }
            $cursor = $response['result']['nextCursor'] ?? null;
        } while ($cursor !== null && ++$guard < 20);

        Assert::assertGreaterThanOrEqual(56, count($toolNames), 'Expected the full tool catalog, got: '.implode(', ', $toolNames));

        // One representative per domain — catches a whole domain falling out of the registry.
        foreach (['findTasks', 'getAllProjects', 'getAllGoals', 'getCalendar', 'getComments', 'logTime'] as $expected) {
            Assert::assertContains($expected, $toolNames, "Tool {$expected} missing from tools/list");
        }
    }

    #[Group('mcp')]
    public function mcpToolCallLifecycle(AcceptanceTester $I, Scenario $scenario)
    {
        $this->enableMcpPluginOrSkip($I, $scenario);
        $this->mintBearerToken($I);

        // Create a task, read it back, patch it — the minimal write→read→write contract that
        // exercises DI-constructed tools, session auth context, and the Tickets service layer.
        $response = $this->callTool($I, 'addTask', [
            'headline' => 'MCP contract test task',
            'projectId' => 1,
        ]);
        $text = $this->toolText($response);
        Assert::assertFalse($response['result']['isError'] ?? true, 'addTask errored: '.$text);
        Assert::assertSame(1, preg_match('/ID:?\s*(\d+)/', $text, $matches), 'addTask did not return an id: '.$text);
        $taskId = (int) $matches[1];

        $response = $this->callTool($I, 'getTicket', ['id' => $taskId]);
        Assert::assertFalse($response['result']['isError'] ?? true, 'getTicket errored');
        Assert::assertStringContainsString('MCP contract test task', $this->toolText($response));

        $response = $this->callTool($I, 'editTask', [
            'id' => $taskId,
            'params' => ['headline' => 'MCP contract test task (edited)'],
        ]);
        Assert::assertFalse($response['result']['isError'] ?? true, 'editTask errored: '.$this->toolText($response));

        $response = $this->callTool($I, 'getTicket', ['id' => $taskId]);
        Assert::assertStringContainsString('(edited)', $this->toolText($response), 'edit did not persist');
    }

    #[Group('mcp')]
    public function mcpRejectsUnknownTool(AcceptanceTester $I, Scenario $scenario)
    {
        $this->enableMcpPluginOrSkip($I, $scenario);
        $this->mintBearerToken($I);

        // laravel/mcp reports unknown tools as an isError tool result ("Tool not found"),
        // not a JSON-RPC error object.
        $response = $this->callTool($I, 'definitelyNotATool', []);
        Assert::assertTrue($response['result']['isError'] ?? false, 'Unknown tool should produce an error result: '.json_encode($response));
    }

    /**
     * Enable the McpServer plugin for this test (row is rolled back by the Db module afterwards),
     * or skip when the plugin code is not present (public OSS checkout — app/Plugins is a private
     * submodule). The test runner shares the app container, so the folder check is authoritative.
     */
    private function enableMcpPluginOrSkip(AcceptanceTester $I, Scenario $scenario): void
    {
        if (! is_dir(dirname(__DIR__, 3).'/app/Plugins/McpServer')) {
            $scenario->skip('McpServer plugin not present (app/Plugins submodule not checked out)');
        }

        $I->haveInDatabase('zp_plugins', [
            'name' => 'leantime/mcpServer',
            'enabled' => 1,
            'description' => 'MCP Server (acceptance fixture)',
            'version' => '1.0.0',
            'installdate' => date('Y-m-d H:i:s'),
            'foldername' => 'McpServer',
            'homepage' => 'https://leantime.io',
            'authors' => '[]',
            'license' => '',
            'format' => 'folder',
        ]);
    }

    /**
     * Mint a Bearer token directly in the DB — sha256 of an opaque string, exactly as
     * AccessTokenRepository::createToken persists it (same approach as BearerApiCest).
     */
    private function mintBearerToken(AcceptanceTester $I): void
    {
        $userId = $I->grabFromDatabase('zp_user', 'id', ['username' => 'test@leantime.io']);
        Assert::assertNotEmpty($userId, 'Test user not found after install');

        $token = bin2hex(random_bytes(20));
        $I->haveInDatabase('zp_access_tokens', [
            'tokenable_type' => 'Leantime\\Domain\\Auth\\Services\\Auth',
            'tokenable_id' => (int) $userId,
            'name' => 'mcp-cest',
            'token' => hash('sha256', $token),
            'abilities' => json_encode(['*']),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $I->haveHttpHeader('Authorization', 'Bearer '.$token);
    }

    /**
     * POST a JSON-RPC payload to /mcp and return the decoded response.
     */
    private function mcp(AcceptanceTester $I, array $payload): array
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendPost(self::MCP_PATH, json_encode($payload, JSON_THROW_ON_ERROR));
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        return json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Invoke an MCP tool via tools/call.
     */
    private function callTool(AcceptanceTester $I, string $name, array $arguments): array
    {
        return $this->mcp($I, [
            'jsonrpc' => '2.0',
            'id' => 3,
            'method' => 'tools/call',
            'params' => [
                'name' => $name,
                'arguments' => $arguments === [] ? new \stdClass : $arguments,
            ],
        ]);
    }

    /**
     * Text content of a tools/call response ('' when the shape is unexpected).
     */
    private function toolText(array $response): string
    {
        return $response['result']['content'][0]['text'] ?? '';
    }

    /**
     * A spec-shaped initialize request for the given protocol revision.
     */
    private function initializeRequest(string $protocolVersion): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => [
                'protocolVersion' => $protocolVersion,
                'capabilities' => new \stdClass,
                'clientInfo' => ['name' => 'mcp-cest', 'version' => '1.0'],
            ],
        ];
    }
}
