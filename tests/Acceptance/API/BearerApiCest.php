<?php

namespace Acceptance\API;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Users\Repositories\Users;
use PHPUnit\Framework\Assert;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Install;

/**
 * Bearer-token JSON-RPC contract suite.
 *
 * Sibling to ApiCest (x-api-key auth). Exists because the JSON-RPC endpoint
 * accepts three auth modes (session cookie, x-api-key, Bearer token), and only
 * the first two had test coverage. The Bearer path is what the mobile app +
 * any AdvancedAuth integrator hits, and a permission-engine deploy in
 * 2026-06 silently broke it for every -32001 gated read without CI noticing.
 *
 * Each primitive asserts the response is 200, JSON, and NOT a `-32001`
 * permission denial. That last assertion is the regression gate.
 */
class BearerApiCest
{
    private string $bearerToken;

    private Install $installPage;

    public function _before(AcceptanceTester $I, Install $installPage)
    {
        $this->installPage = $installPage;

        // Fresh install — same fixture as ApiCest so this Cest can run
        // standalone or alongside it.
        $this->installPage->install(
            'test@leantime.io',
            'Test123456!',
            'John',
            'Smith',
            'Smith & Co'
        );
    }

    /**
     * Mint a Bearer token directly via AccessTokenRepository — no UI flow,
     * no AdvancedAuth plugin needed. This is the entire reason the suite
     * exists: Bearer auth must be testable without depending on a paid
     * plugin's web UI.
     */
    #[Group('bearer-api')]
    public function mintBearerToken(AcceptanceTester $I)
    {
        $usersRepo = app()->make(Users::class);
        $user = $usersRepo->getUserByEmail('test@leantime.io');
        Assert::assertNotEmpty($user['id'] ?? null, 'Test user not found after install');

        $tokenRepo = app()->make(AccessTokenRepository::class);
        $minted = $tokenRepo->createToken((int) $user['id'], 'bearer-api-cest');

        $this->bearerToken = $minted['token'];
        Assert::assertNotEmpty($this->bearerToken, 'Token mint returned empty string');
    }

    #[Group('bearer-api')]
    #[Depends('mintBearerToken')]
    public function usersGetUserResolvesWhoAmI(AcceptanceTester $I)
    {
        $this->assertRpcSucceeds($I, 'leantime.rpc.Users.Users.getUser', new \stdClass);
    }

    #[Group('bearer-api')]
    #[Depends('mintBearerToken')]
    public function projectsListIsReachable(AcceptanceTester $I)
    {
        $this->assertRpcSucceeds($I, 'leantime.rpc.Projects.Projects.getProjectsUserHasAccessTo', new \stdClass);
    }

    #[Group('bearer-api')]
    #[Depends('mintBearerToken')]
    public function ticketsGetAllOpenUserTicketsIsReachable(AcceptanceTester $I)
    {
        $this->assertRpcSucceeds($I, 'leantime.rpc.Tickets.Tickets.getAllOpenUserTickets', new \stdClass);
    }

    #[Group('bearer-api')]
    #[Depends('mintBearerToken')]
    public function ticketsGetTicketHonorsBearerAuth(AcceptanceTester $I)
    {
        // Quick-add a ticket so we have a known id, then fetch it via
        // getTicket. This is the exact pair that broke mobile in 2026-06:
        // -32001 on getTicket against a project the user clearly owns.
        $created = $this->rpc($I, 'leantime.rpc.Tickets.Tickets.quickAddTicket', [
            'params' => [
                'headline' => 'Bearer-auth contract test',
                'projectId' => 1,
            ],
        ]);
        $newId = is_array($created) && isset($created['result']) ? $created['result'] : null;
        Assert::assertIsInt($newId, 'quickAddTicket should return an int id');

        $this->assertRpcSucceeds($I, 'leantime.rpc.Tickets.Tickets.getTicket', ['id' => $newId]);
    }

    #[Group('bearer-api')]
    #[Depends('mintBearerToken')]
    public function commentsGetCommentsHonorsBearerAuth(AcceptanceTester $I)
    {
        // Comments are entity-scoped — they resolve the host entity's
        // project from (module, moduleId). Project 1 has the seeded
        // welcome ticket id 1 in a fresh install.
        $this->assertRpcSucceeds($I, 'leantime.rpc.Comments.Comments.getComments', [
            'module' => 'ticket',
            'moduleId' => 1,
        ]);
    }

    #[Group('bearer-api')]
    #[Depends('mintBearerToken')]
    public function notificationsGetUnreadCountIsReachable(AcceptanceTester $I)
    {
        $this->assertRpcSucceeds($I, 'leantime.rpc.Notifications.Notifications.getUnreadCount', new \stdClass);
    }

    #[Group('bearer-api')]
    #[Depends('mintBearerToken')]
    public function missingBearerReturns401(AcceptanceTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/api/jsonrpc', [
            'jsonrpc' => '2.0',
            'method' => 'leantime.rpc.Tickets.Tickets.getAllOpenUserTickets',
            'params' => new \stdClass,
            'id' => 1,
        ]);

        $I->seeResponseCodeIs(401);
    }

    /**
     * Hit /api/jsonrpc with the test bearer + the given method, return the
     * decoded body. Caller decides what to assert.
     */
    private function rpc(AcceptanceTester $I, string $method, array|\stdClass $params): array
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Authorization', 'Bearer '.$this->bearerToken);

        $I->sendPost('/api/jsonrpc', [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => 1,
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        return json_decode($I->grabResponse(), true) ?? [];
    }

    /**
     * Hit /api/jsonrpc and assert no `-32001` permission-engine denial.
     *
     * Other engine codes (e.g. `-32602` Invalid params, `-32601` Method not
     * found) are out of scope — we're testing the auth → user-context →
     * project-role bootstrap on the Bearer path, not method correctness.
     * Any `-32001` here means the engine refused a request it would have
     * allowed under x-api-key or session auth — which IS the regression.
     */
    private function assertRpcSucceeds(AcceptanceTester $I, string $method, array|\stdClass $params): void
    {
        $body = $this->rpc($I, $method, $params);

        $code = $body['error']['code'] ?? null;
        Assert::assertNotSame(
            -32001,
            $code,
            sprintf(
                'Bearer-auth call to %s was denied by permission engine (-32001). Response: %s',
                $method,
                json_encode($body)
            )
        );
    }
}
