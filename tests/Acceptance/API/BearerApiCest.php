<?php

namespace Acceptance\API;

use Codeception\Attribute\Group;
use PHPUnit\Framework\Assert;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Install;

/**
 * Bearer-token JSON-RPC contract test.
 *
 * Sibling to ApiCest (x-api-key auth). Exists because the JSON-RPC endpoint accepts three auth
 * modes (session cookie, x-api-key, Bearer token) and only the first two had coverage. The Bearer
 * path is what the mobile app + any AdvancedAuth integrator hits, and a permission-engine deploy in
 * 2026-06 silently broke it (every gated read 401'd) without CI noticing.
 *
 * It is ONE test method on purpose: the minted token lives in zp_access_tokens, and the Db module
 * deletes haveInDatabase() rows after each test — so a multi-method #[Depends] chain would lose the
 * token between the mint and the assertions. Keeping everything in one test keeps the token alive
 * for every call. Each authed call asserts 200, JSON, and NOT a -32001 permission denial — that
 * last assertion is the regression gate.
 */
class BearerApiCest
{
    private string $bearerToken;

    public function _before(AcceptanceTester $I, Install $installPage)
    {
        // Fresh install — same fixture as ApiCest so this Cest can run standalone or alongside it.
        $installPage->install('test@leantime.io', 'Test123456!', 'John', 'Smith', 'Smith & Co');
    }

    #[Group('bearer-api')]
    public function bearerAuthHonorsGatedReads(AcceptanceTester $I)
    {
        // 1) No bearer → 401. Done first, before any Authorization header is set (Codeception
        //    headers are sticky across requests within a test).
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPost('/api/jsonrpc', json_encode([
            'jsonrpc' => '2.0',
            'method' => 'leantime.rpc.Tickets.Tickets.getAllOpenUserTickets',
            'params' => new \stdClass,
            'id' => 1,
        ]));
        $I->seeResponseCodeIs(401);

        // 2) Mint a Bearer token directly in the DB — no UI flow, no AdvancedAuth plugin. A token
        //    is just a random string whose sha256 is stored in zp_access_tokens, exactly as
        //    AccessTokenRepository::createToken persists it. (Done via the Db module because the
        //    Laravel container is not reliably bootstrapped against the test DB in the acceptance
        //    process, so app()->getUserByEmail() resolves the wrong connection.)
        $userId = $I->grabFromDatabase('zp_user', 'id', ['username' => 'test@leantime.io']);
        Assert::assertNotEmpty($userId, 'Test user not found after install');

        $this->bearerToken = bin2hex(random_bytes(20)); // 40-char opaque token
        $I->haveInDatabase('zp_access_tokens', [
            'tokenable_type' => 'Leantime\\Domain\\Auth\\Services\\Auth',
            'tokenable_id' => (int) $userId,
            'name' => 'bearer-api-cest',
            'token' => hash('sha256', $this->bearerToken),
            'abilities' => json_encode(['*']),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // 3) Gated reads over Bearer must all resolve (200, no -32001).
        $this->assertRpcSucceeds($I, 'leantime.rpc.Users.Users.getUser', new \stdClass);
        $this->assertRpcSucceeds($I, 'leantime.rpc.Projects.Projects.getProjectsUserHasAccessTo', new \stdClass);
        $this->assertRpcSucceeds($I, 'leantime.rpc.Tickets.Tickets.getAllOpenUserTickets', new \stdClass);
        $this->assertRpcSucceeds($I, 'leantime.rpc.Notifications.Notifications.getUnreadCount', new \stdClass);

        // 4) The exact pair that broke mobile: create a ticket, then fetch it by id.
        // quickAddTicket($params) takes a single array argument literally named "params".
        $created = $this->rpc($I, 'leantime.rpc.Tickets.Tickets.quickAddTicket', [
            'params' => ['headline' => 'Bearer-auth contract test', 'projectId' => 1],
        ]);
        $newId = $created['result'] ?? null;
        Assert::assertIsInt($newId, 'quickAddTicket should return an int id, got: '.json_encode($created));
        $this->assertRpcSucceeds($I, 'leantime.rpc.Tickets.Tickets.getTicket', ['id' => $newId]);

        // 5) Entity-scoped comments resolve the project from (module, moduleId).
        $this->assertRpcSucceeds($I, 'leantime.rpc.Comments.Comments.getComments', ['module' => 'ticket', 'moduleId' => 1]);
    }

    /** POST /api/jsonrpc with the test bearer + method, return the decoded body. */
    private function rpc(AcceptanceTester $I, string $method, array|\stdClass $params): array
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Authorization', 'Bearer '.$this->bearerToken);

        // JSON string body (not a PHP array): an array body with a stdClass param makes
        // Codeception's REST module form-encode it, so it never arrives as JSON-RPC.
        $I->sendPost('/api/jsonrpc', json_encode([
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => 1,
        ]));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        return json_decode($I->grabResponse(), true) ?? [];
    }

    /**
     * Assert a Bearer call is NOT denied by the permission engine (-32001). Other engine codes
     * (e.g. -32602 invalid params) are out of scope — this gates auth → user-context → project-role
     * on the Bearer path, not method correctness. A -32001 here is the regression.
     */
    private function assertRpcSucceeds(AcceptanceTester $I, string $method, array|\stdClass $params): void
    {
        $body = $this->rpc($I, $method, $params);

        Assert::assertNotSame(
            -32001,
            $body['error']['code'] ?? null,
            sprintf('Bearer-auth call to %s was denied by the permission engine (-32001). Response: %s', $method, json_encode($body))
        );
    }
}
