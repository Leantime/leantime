<?php

namespace Acceptance\API;

use Codeception\Attribute\Group;
use Codeception\Scenario;
use PHPUnit\Framework\Assert;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Install;

/**
 * MCP server contract test (laravel/mcp 1.x over the /mcp HTTP transport).
 *
 * Sibling to BearerApiCest: same Bearer-token mint (a row in zp_access_tokens), but against the
 * MCP endpoint the McpServer plugin registers instead of the JSON-RPC API. Exists because the MCP
 * tool layer is exercised by AI clients (Claude Code, MCP inspector), not by the web UI, so
 * nothing else in CI notices when it breaks — e.g. tools calling since-removed service methods, or
 * the protocol-version handshake rejecting current clients (both happened in 2026-07 and were only
 * caught by live testing).
 *
 * Two client eras are covered: legacy clients (initialize handshake, 2025-11-25 and older) and
 * 2026-07-28 clients (no handshake; protocol version + capabilities in every request's _meta,
 * mirrored in the MCP-Protocol-Version / Mcp-Method / Mcp-Name headers).
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

    private const MODERN_PROTOCOL = '2026-07-28';

    /**
     * Every core tool (the Tools folders in app/Domain) plus the McpServer plugin's own docs search.
     * PgmPro's seedResourcesFromProjects is only registered when that plugin is present.
     */
    private const EXPECTED_TOOLS = [
        'getStatusLabels', 'getAllStatusLabelsByUserId', 'findTasks', 'getTicket', 'editTask', 'addTask',
        'addSubtask', 'bulkAddTasks', 'bulkEditTasks', 'bulkScheduleTasks', 'createSubtasksForTask',
        'getTaskStatusSummary', 'findMilestones', 'getMilestone', 'editMilestone', 'addMilestone',
        'addMilestonesForProject', 'getAllProjects', 'getFullProjectOverview', 'getProject',
        'getProjectsAssignedToUser', 'addProject', 'editProject', 'patchProject', 'getUsersAssignedToProject',
        'findProject', 'getAllGoals', 'getGoal', 'createGoal', 'editGoal', 'createGoalboard',
        'getGoalsByMilestone', 'getChildGoals', 'getParentKPIs', 'getCalendar', 'addEvent', 'editEvent',
        'deleteEvent', 'getICalUrl', 'scheduleTaskOnCalendar', 'bulkAddEvents', 'bulkEditEvents',
        'scheduleDay', 'breakdownTask', 'getComments', 'addComment', 'getAllProjectComments',
        'addProjectStatusUpdate', 'pollComments', 'startTimer', 'stopTimer', 'getUserTimesheets',
        'getProjectTimesheets', 'logTime', 'getTimesheetSummary', 'getWeeklyTimesheets', 'search',
        'searchLeantimeDocumentation',
    ];

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

        // Clients that still open with initialize get the revision they asked for, if supported.
        $response = $this->mcp($I, $this->initializeRequest('2025-06-18'));
        Assert::assertSame('2025-06-18', $response['result']['protocolVersion'] ?? null, 'initialize failed: '.json_encode($response));
        Assert::assertNotEmpty($response['result']['serverInfo']['name'] ?? null);

        // Regression gate for the Claude Code handshake: current clients request 2025-11-25.
        $response = $this->mcp($I, $this->initializeRequest('2025-11-25'));
        Assert::assertSame('2025-11-25', $response['result']['protocolVersion'] ?? null, 'Claude Code cannot connect: '.json_encode($response));
    }

    #[Group('mcp')]
    public function mcpDiscoveryAdvertisesTheCurrentProtocol(AcceptanceTester $I, Scenario $scenario)
    {
        $this->enableMcpPluginOrSkip($I, $scenario);
        $this->mintBearerToken($I);

        $response = $this->modernMcp($I, 'server/discover');

        Assert::assertContains(self::MODERN_PROTOCOL, $response['result']['supportedVersions'] ?? [], 'server/discover: '.json_encode($response));
        Assert::assertSame('complete', $response['result']['resultType'] ?? null);
    }

    #[Group('mcp')]
    public function mcpListsAllRegisteredTools(AcceptanceTester $I, Scenario $scenario)
    {
        $this->enableMcpPluginOrSkip($I, $scenario);
        $this->mintBearerToken($I);

        $response = $this->modernMcp($I, 'tools/list');
        Assert::assertArrayNotHasKey('error', $response, 'tools/list failed: '.json_encode($response));

        // One page holds every tool: clients that ignore nextCursor must still see them all.
        Assert::assertArrayNotHasKey('nextCursor', $response['result'], 'tools/list paginated');

        // The list is the same for every user, but /mcp is authenticated: cacheable privately only.
        Assert::assertSame('complete', $response['result']['resultType'] ?? null);
        Assert::assertSame('private', $response['result']['cacheScope'] ?? null);
        Assert::assertGreaterThan(0, $response['result']['ttlMs'] ?? 0);

        $toolNames = array_column($response['result']['tools'] ?? [], 'name');
        foreach (self::EXPECTED_TOOLS as $expected) {
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

        // An unknown tool is a JSON-RPC error (invalid params), answered with HTTP 400.
        $response = $this->mcp($I, $this->toolCallRequest('definitelyNotATool', []), 400);
        Assert::assertSame(-32602, $response['error']['code'] ?? null, 'Unknown tool should be a JSON-RPC error: '.json_encode($response));
    }

    #[Group('mcp')]
    public function mcpCallsToolsWithTheCurrentProtocol(AcceptanceTester $I, Scenario $scenario)
    {
        $this->enableMcpPluginOrSkip($I, $scenario);
        $this->mintBearerToken($I);

        $response = $this->modernMcp($I, 'tools/call', ['name' => 'getAllProjects', 'arguments' => new \stdClass]);
        Assert::assertFalse($response['result']['isError'] ?? true, 'getAllProjects errored: '.json_encode($response));

        // The Mcp-Name header has to match the tool named in the body.
        $response = $this->modernMcp($I, 'tools/call', ['name' => 'getAllProjects', 'arguments' => new \stdClass], 400, 'getProject');
        Assert::assertSame(-32020, $response['error']['code'] ?? null, 'Header mismatch not rejected: '.json_encode($response));
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
    private function mcp(AcceptanceTester $I, array $payload, int $expectedStatus = 200): array
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendPost(self::MCP_PATH, json_encode($payload, JSON_THROW_ON_ERROR));
        $I->seeResponseCodeIs($expectedStatus);
        $I->seeResponseIsJson();

        return json_decode($I->grabResponse(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Send a 2026-07-28 request: protocol version and client capabilities in params._meta,
     * mirrored in the MCP headers (Mcp-Name defaults to the tool named in the body).
     */
    private function modernMcp(AcceptanceTester $I, string $method, array $params = [], int $expectedStatus = 200, ?string $nameHeader = null): array
    {
        $params['_meta'] = [
            'io.modelcontextprotocol/protocolVersion' => self::MODERN_PROTOCOL,
            'io.modelcontextprotocol/clientCapabilities' => new \stdClass,
        ];

        $I->haveHttpHeader('MCP-Protocol-Version', self::MODERN_PROTOCOL);
        $I->haveHttpHeader('Mcp-Method', $method);
        $nameHeader ??= $params['name'] ?? null;
        if ($nameHeader !== null) {
            $I->haveHttpHeader('Mcp-Name', $nameHeader);
        }

        try {
            return $this->mcp($I, ['jsonrpc' => '2.0', 'id' => 4, 'method' => $method, 'params' => $params], $expectedStatus);
        } finally {
            // The headers stick to the REST module; a legacy request carrying them would be refused.
            $I->deleteHeader('MCP-Protocol-Version');
            $I->deleteHeader('Mcp-Method');
            $I->deleteHeader('Mcp-Name');
        }
    }

    /**
     * Invoke an MCP tool via tools/call (legacy client: no _meta, no MCP headers).
     */
    private function callTool(AcceptanceTester $I, string $name, array $arguments): array
    {
        return $this->mcp($I, $this->toolCallRequest($name, $arguments));
    }

    /**
     * A legacy tools/call request.
     */
    private function toolCallRequest(string $name, array $arguments): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => 3,
            'method' => 'tools/call',
            'params' => [
                'name' => $name,
                'arguments' => $arguments === [] ? new \stdClass : $arguments,
            ],
        ];
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
