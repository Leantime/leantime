<?php

namespace Acceptance\API;

use Codeception\Attribute\Group;
use Leantime\Core\Mcp\Auth\McpAbilityCatalog;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Tickets\Repositories\Tickets;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Install;

class McpCest
{
    private string $bearerToken = '';

    private int $ticketId = 0;

    public function _before(AcceptanceTester $I, Install $installPage): void
    {
        $installPage->install(
            'test@leantime.io',
            'Test123456!',
            'John',
            'Smith',
            'Smith & Co'
        );

        $app = $I->getApplication();
        $token = $app->make(AccessTokenRepository::class)->createToken(
            userId: 1,
            name: 'acceptance-mcp',
            abilities: McpAbilityCatalog::abilitiesForPreset('admin'),
        );

        $this->bearerToken = $token['token'];
        $this->ticketId = (int) $app->make(Tickets::class)->addTicket([
            'headline' => 'MCP Acceptance Ticket',
            'type' => 'task',
            'description' => 'Ticket created for MCP acceptance coverage',
            'date' => date('Y-m-d H:i:s'),
            'dateToFinish' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'projectId' => 1,
            'status' => 3,
            'userId' => 1,
            'editorId' => 1,
            'tags' => 'mcp',
            'sprint' => 0,
            'storypoints' => 1,
            'priority' => 3,
            'hourRemaining' => 1,
            'planHours' => 1,
            'acceptanceCriteria' => '',
            'editFrom' => null,
            'editTo' => null,
        ]);

        $usersRepository = $app->make(\Leantime\Domain\Users\Repositories\Users::class);
        if ($usersRepository->getUserByEmail('agent-helper@test.local', 'a') === false) {
            $usersRepository->addUser([
                'firstname' => 'Agent',
                'lastname' => 'Helper',
                'phone' => '',
                'user' => 'agent-helper@test.local',
                'role' => 20,
                'notifications' => 1,
                'clientId' => 1,
                'password' => 'Test123456!',
                'source' => '',
                'pwReset' => '',
                'status' => 'a',
                'createdOn' => date('Y-m-d H:i:s'),
                'jobTitle' => 'Agent',
                'jobLevel' => '',
                'department' => '',
            ]);
        }
    }

    #[Group('api')]
    public function accountSecurityTabShowsMcpTokenUi(AcceptanceTester $I): void
    {
        $I->amOnPage('/users/editOwn#security');
        $I->waitForText('MCP Access Tokens', 120);
        $I->fillField('#mcpTokenName', 'ui-mcp-token');
        $I->selectOption('#mcpTokenPreset', 'read-only');
        $I->clickWithRetry('#createMcpToken');
        $I->waitForText('New Token', 120);
        $I->see('ui-mcp-token');
    }

    #[Group('api')]
    public function authFailureReturnsUnauthorized(AcceptanceTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => ['protocolVersion' => '2025-03-26'],
        ]);

        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson([
            'jsonrpc' => '2.0',
            'id' => 1,
            'error' => [
                'code' => -32001,
            ],
        ]);
    }

    #[Group('api')]
    public function initializeReturnsCapabilities(AcceptanceTester $I): void
    {
        $this->authorize($I);
        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
            'params' => ['protocolVersion' => '2025-03-26'],
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => [
                'protocolVersion' => '2025-03-26',
            ],
        ]);
    }

    #[Group('api')]
    public function toolsListIncludesNewMcpTools(AcceptanceTester $I): void
    {
        $this->authorize($I);
        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/list',
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('tickets.search');
        $I->seeResponseContains('comments.add');
        $I->seeResponseContains('users.get');
    }

    #[Group('api')]
    public function readToolsReturnProjectAndTicketData(AcceptanceTester $I): void
    {
        $this->authorize($I);

        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 3,
            'method' => 'tools/call',
            'params' => [
                'name' => 'projects.list',
                'arguments' => ['status' => 'open'],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('My Project');

        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 4,
            'method' => 'tools/call',
            'params' => [
                'name' => 'tickets.search',
                'arguments' => [
                    'projectId' => 1,
                    'term' => 'MCP Acceptance',
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('MCP Acceptance Ticket');
    }

    #[Group('api')]
    public function projectCreateToolWorks(AcceptanceTester $I): void
    {
        $this->authorize($I);
        $I->haveHttpHeader('Idempotency-Key', 'project-create-1');

        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 11,
            'method' => 'tools/call',
            'params' => [
                'name' => 'projects.create',
                'arguments' => [
                    'name' => 'MCP Created Project',
                    'clientId' => 1,
                    'details' => 'Created by MCP acceptance test',
                    'psettings' => 'restricted',
                ],
            ],
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('MCP Created Project');
        $I->seeInDatabase('zp_projects', [
            'name' => 'MCP Created Project',
            'clientId' => 1,
        ]);
    }

    #[Group('api')]
    public function projectUpdateAndTicketCreateToolsWork(AcceptanceTester $I): void
    {
        $this->authorize($I);

        $I->haveHttpHeader('Idempotency-Key', 'project-create-2');
        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 12,
            'method' => 'tools/call',
            'params' => [
                'name' => 'projects.create',
                'arguments' => [
                    'name' => 'Project Update Target',
                    'clientId' => 1,
                    'details' => 'Original details',
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $projectId = (int) $I->grabDataFromResponseByJsonPath('$.result.structuredContent.projectId')[0];
        $expectedVersion = $I->grabDataFromResponseByJsonPath('$.result.structuredContent.project.modified')[0];

        $I->haveHttpHeader('Idempotency-Key', 'project-update-1');
        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 13,
            'method' => 'tools/call',
            'params' => [
                'name' => 'projects.update',
                'arguments' => [
                    'projectId' => $projectId,
                    'expectedVersion' => $expectedVersion,
                    'details' => 'Updated details via MCP',
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeInDatabase('zp_projects', [
            'id' => $projectId,
            'details' => 'Updated details via MCP',
        ]);

        $I->haveHttpHeader('Idempotency-Key', 'ticket-create-1');
        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 14,
            'method' => 'tools/call',
            'params' => [
                'name' => 'tickets.create',
                'arguments' => [
                    'projectId' => 1,
                    'headline' => 'Ticket Created Via MCP',
                    'description' => 'Created from acceptance test',
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeInDatabase('zp_tickets', [
            'headline' => 'Ticket Created Via MCP',
            'projectId' => 1,
        ]);
    }

    #[Group('api')]
    public function projectMembersAndTicketUpdateToolsWork(AcceptanceTester $I): void
    {
        $this->authorize($I);

        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 15,
            'method' => 'tools/call',
            'params' => [
                'name' => 'projects.get',
                'arguments' => ['projectId' => 1],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $projectVersion = $I->grabDataFromResponseByJsonPath('$.result.structuredContent.project.modified')[0];

        $I->haveHttpHeader('Idempotency-Key', 'project-members-update-1');
        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 16,
            'method' => 'tools/call',
            'params' => [
                'name' => 'projects.members.update',
                'arguments' => [
                    'projectId' => 1,
                    'expectedVersion' => $projectVersion,
                    'members' => [
                        ['userId' => 1, 'projectRoleId' => 30],
                        ['userId' => 2, 'projectRoleId' => 20],
                    ],
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeInDatabase('zp_relationuserproject', ['projectId' => 1, 'userId' => 2]);

        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 17,
            'method' => 'tools/call',
            'params' => [
                'name' => 'tickets.get',
                'arguments' => ['ticketId' => $this->ticketId],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $ticketVersion = $I->grabDataFromResponseByJsonPath('$.result.structuredContent.ticket.modified')[0];

        $I->haveHttpHeader('Idempotency-Key', 'ticket-update-1');
        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 18,
            'method' => 'tools/call',
            'params' => [
                'name' => 'tickets.update',
                'arguments' => [
                    'ticketId' => $this->ticketId,
                    'expectedVersion' => $ticketVersion,
                    'headline' => 'MCP Acceptance Ticket Updated',
                    'description' => 'Updated from MCP acceptance test',
                    'editorId' => 2,
                    'collaborators' => [1, 2],
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeInDatabase('zp_tickets', [
            'id' => $this->ticketId,
            'headline' => 'MCP Acceptance Ticket Updated',
        ]);
    }

    #[Group('api')]
    public function commentAndUserToolsWork(AcceptanceTester $I): void
    {
        $this->authorize($I);
        $idempotencyKey = 'comment-add-1';

        $I->haveHttpHeader('Idempotency-Key', $idempotencyKey);
        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 5,
            'method' => 'tools/call',
            'params' => [
                'name' => 'comments.add',
                'arguments' => [
                    'entityType' => 'ticket',
                    'entityId' => $this->ticketId,
                    'text' => 'Comment from MCP acceptance test',
                ],
            ],
        ]);

        $I->seeResponseCodeIs(200);
        $commentId = $I->grabDataFromResponseByJsonPath('$.result.structuredContent.commentId')[0];
        $I->seeInDatabase('zp_comment', [
            'module' => 'ticket',
            'moduleId' => $this->ticketId,
            'text like' => '%Comment from MCP acceptance test%',
        ]);

        $I->haveHttpHeader('Idempotency-Key', '');
        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 6,
            'method' => 'tools/call',
            'params' => [
                'name' => 'comments.list',
                'arguments' => [
                    'entityType' => 'ticket',
                    'entityId' => $this->ticketId,
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('Comment from MCP acceptance test');

        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 7,
            'method' => 'tools/call',
            'params' => [
                'name' => 'users.get',
                'arguments' => [
                    'projectId' => 1,
                    'userId' => 1,
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContains('test@leantime.io');

        $I->haveHttpHeader('Idempotency-Key', 'comment-delete-1');
        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 10,
            'method' => 'tools/call',
            'params' => [
                'name' => 'comments.delete',
                'arguments' => [
                    'commentId' => (int) $commentId,
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->dontSeeInDatabase('zp_comment', [
            'id' => (int) $commentId,
        ]);
    }

    #[Group('api')]
    public function writeToolSupportsApprovalFlow(AcceptanceTester $I): void
    {
        $this->authorize($I);
        $approvalKey = 'timesheet-approval-1';

        $I->haveHttpHeader('Idempotency-Key', $approvalKey);
        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 8,
            'method' => 'tools/call',
            'params' => [
                'name' => 'timesheets.log',
                'arguments' => [
                    'ticketId' => $this->ticketId,
                    'kind' => 'GENERAL_BILLABLE',
                    'hours' => 2,
                    'dateString' => '2026-05-15 09:00:00',
                    '_approvalMode' => 'request',
                    '_approvalReason' => 'Acceptance test approval flow',
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $approvalId = $I->grabDataFromResponseByJsonPath('$.result.structuredContent.approvalId')[0];

        $I->sendPost('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 9,
            'method' => 'tools/call',
            'params' => [
                'name' => 'approvals.resolve',
                'arguments' => [
                    'approvalId' => (int) $approvalId,
                    'decision' => 'approve',
                    '_idempotencyKey' => 'approval-resolve-1',
                ],
            ],
        ]);
        $I->seeResponseCodeIs(200);
        $I->seeInDatabase('zp_mcp_approvals', [
            'id' => (int) $approvalId,
            'status' => 'approved',
        ]);
        $I->seeInDatabase('zp_timesheets', [
            'ticketId' => $this->ticketId,
            'hours' => 2,
            'kind' => 'GENERAL_BILLABLE',
        ]);
    }

    private function authorize(AcceptanceTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Authorization', 'Bearer '.$this->bearerToken);
    }
}
