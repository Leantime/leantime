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
 * Each test is self-contained: the minted token lives in zp_access_tokens and the Db module deletes
 * haveInDatabase() rows after each test, so a method keeps everything it needs alive within itself
 * (a multi-method #[Depends] chain would lose the token between the mint and the assertions). Each
 * authed call asserts 200, JSON, and NOT a -32001 permission denial — that last assertion is the
 * regression gate.
 *
 * Two scenarios:
 *  - bearerAuthHonorsGatedReads — the OWNER (role 50). Owner short-circuits project-role resolution.
 *  - nonManagerBearerHonorsProjectScopedReads — an EDITOR (role 20). Sub-manager roles run a wholly
 *    different authorization path (getProjectRole + isUserAssignedToProject + a resolved projectId),
 *    which owner-only testing never exercises. This is the path that would silently break for real
 *    non-admin mobile users.
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
        ], JSON_THROW_ON_ERROR));
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

        // 6) Session-scoped mobile endpoints: no userId param, the caller is resolved from the
        //    token. These previously 404'd on mobile because the userId-taking originals are
        //    deliberately NOT @api (IDOR guard); the session-scoped siblings are the fix.
        //    getInbox is the inbox list (getAllNotifications stays non-@api — it takes a $userId).
        $inbox = $this->rpc($I, 'leantime.rpc.Notifications.Notifications.getInbox', new \stdClass);
        Assert::assertArrayNotHasKey('error', $inbox, 'getInbox must be exposed + session-scoped: '.json_encode($inbox));
        Assert::assertIsArray($inbox['result'] ?? null, 'getInbox should return an array: '.json_encode($inbox));

        //    getExternalCalendarEvents (subscribed iCal feeds) is already session-scoped; it only
        //    lacked the @api tag. No external calendars on a fresh install => an empty array.
        $extEvents = $this->rpc($I, 'leantime.rpc.Calendar.Calendar.getExternalCalendarEvents', new \stdClass);
        Assert::assertArrayNotHasKey('error', $extEvents, 'getExternalCalendarEvents must be exposed: '.json_encode($extEvents));
        Assert::assertIsArray($extEvents['result'] ?? null, 'getExternalCalendarEvents should return an array: '.json_encode($extEvents));

        //    getICalUrl is now exposed too. It legitimately errors when the user has no iCal secret
        //    configured yet, so assert only that it is FOUND (not -32601 method-not-found).
        $icalUrl = $this->rpc($I, 'leantime.rpc.Calendar.Calendar.getICalUrl', new \stdClass);
        Assert::assertNotSame(-32601, $icalUrl['error']['code'] ?? null, 'getICalUrl must be exposed via @api: '.json_encode($icalUrl));
    }

    #[Group('bearer-api')]
    public function nonManagerBearerHonorsProjectScopedReads(AcceptanceTester $I)
    {
        // A non-manager (editor, role 20) assigned to a project. Unlike the owner, this role does
        // NOT short-circuit effectiveRoleForProject() — it exercises getProjectRole() +
        // isUserAssignedToProject() + a resolved project role over Bearer. That path only works
        // when the session role context is correctly established (the regression), so this is the
        // guard for "works for the owner but -32001s for real non-admin users."
        $ownerProjectId = (int) $I->grabFromDatabase('zp_projects', 'id', ['name' => 'My Project']);
        Assert::assertNotEmpty($ownerProjectId, 'Seed project not found after install');

        $editorId = (int) $I->haveInDatabase('zp_user', [
            'firstname' => 'Ed',
            'lastname' => 'Itor',
            'username' => 'editor@leantime.io',
            'password' => 'x',
            'role' => '20',
            'status' => 'A',
            'createdOn' => date('Y-m-d H:i:s'),
        ]);
        $I->haveInDatabase('zp_relationuserproject', [
            'userId' => $editorId,
            'projectId' => $ownerProjectId,
            'projectRole' => '',
        ]);

        $this->bearerToken = bin2hex(random_bytes(20));
        $I->haveInDatabase('zp_access_tokens', [
            'tokenable_type' => 'Leantime\\Domain\\Auth\\Services\\Auth',
            'tokenable_id' => $editorId,
            'name' => 'bearer-api-cest-editor',
            'token' => hash('sha256', $this->bearerToken),
            'abilities' => json_encode(['*']),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Cross-project "my work" read (no projectId) + a project-scoped read by id, both as the
        // editor over Bearer. Must resolve (200, no -32001).
        $this->assertRpcSucceeds($I, 'leantime.rpc.Tickets.Tickets.getAllOpenUserTickets', new \stdClass);
        $this->assertRpcSucceeds($I, 'leantime.rpc.Projects.Projects.getProject', ['id' => $ownerProjectId]);
        $this->assertRpcSucceeds($I, 'leantime.rpc.Projects.Projects.getProjectProgress', ['projectId' => $ownerProjectId]);

        // Cross-project "my work" with the projectId=0 sentinel mobile sends — must actually
        // SUCCEED, not just avoid -32001 (it previously -32001'd on 0 / -32602 when omitted, for
        // every role incl. owner). Assert no error at all + an array result, so a regression to any
        // error code (not only -32001) fails the test.
        $body = $this->rpc($I, 'leantime.rpc.Tickets.Tickets.getOpenUserTicketsThisWeekAndLater', ['userId' => $editorId, 'projectId' => 0]);
        Assert::assertArrayNotHasKey('error', $body, 'projectId=0 cross-project read must not error: '.json_encode($body));
        Assert::assertIsArray($body['result'] ?? null, 'expected an array result, got: '.json_encode($body));

        // markTicketDone (mobile swipe-complete) must be EXPOSED (was -32601) AND succeed for the
        // assigned editor. Assert no error + result true, so a regression to -32601/-32602/false is
        // caught — assertRpcSucceeds (absence of -32001 only) would not catch those.
        $assignedTicketId = (int) $I->grabFromDatabase('zp_tickets', 'id', ['projectId' => $ownerProjectId]);
        Assert::assertNotEmpty($assignedTicketId, 'Seed ticket not found in project');
        $done = $this->rpc($I, 'leantime.rpc.Tickets.Tickets.markTicketDone', ['id' => $assignedTicketId]);
        Assert::assertArrayNotHasKey('error', $done, 'markTicketDone must be exposed + authorized: '.json_encode($done));
        Assert::assertTrue($done['result'] ?? false, 'markTicketDone should return true: '.json_encode($done));

        // getMyCalendar is the session-scoped calendar feed (getCalendar itself trusts a userId and
        // stays non-@api). Its calendar.view gate is project-scoped, but on an API call there is no
        // session project, so it resolves capability-only against the effective role — which a
        // non-manager editor holds (calendar.view is readonly+). This is the exact path that would
        // -32001 if a project-scoped gate fell closed on a null project, so prove it resolves for a
        // non-manager, not just the owner.
        $cal = $this->rpc($I, 'leantime.rpc.Calendar.Calendar.getMyCalendar', new \stdClass);
        Assert::assertArrayNotHasKey('error', $cal, 'getMyCalendar must resolve for a non-manager editor: '.json_encode($cal));
        Assert::assertIsArray($cal['result'] ?? null, 'getMyCalendar should return an array: '.json_encode($cal));

        // And the inbox list resolves for the editor too (session-scoped, ungated).
        $editorInbox = $this->rpc($I, 'leantime.rpc.Notifications.Notifications.getInbox', new \stdClass);
        Assert::assertArrayNotHasKey('error', $editorInbox, 'getInbox must resolve for a non-manager editor: '.json_encode($editorInbox));
        Assert::assertIsArray($editorInbox['result'] ?? null, 'getInbox should return an array: '.json_encode($editorInbox));

        // IDOR guard: markNotificationUnread is session-scoped (matches on (id, session user)).
        // Seed a notification owned by the OWNER, read=1, then — as the editor — try to flip it
        // unread by its id. With the previous unscoped where('id') update this would succeed; now
        // it must NOT: the result is false and the row stays read.
        $ownerId = (int) $I->grabFromDatabase('zp_user', 'id', ['username' => 'test@leantime.io']);
        $ownerNotifId = (int) $I->haveInDatabase('zp_notifications', [
            'userId' => $ownerId,
            'read' => 1,
            'type' => 'mention',
            'module' => 'ticket',
            'moduleId' => 1,
            'message' => 'owner-only notification',
            'datetime' => date('Y-m-d H:i:s'),
            'url' => '',
            'authorId' => $ownerId,
        ]);
        // result === false proves the IDOR is closed: the session-scoped repo matched (id, editor)
        // => 0 rows => update affected nothing. (A DB read-back is avoided here because `read` is a
        // MySQL reserved word and Codeception's grabFromDatabase doesn't quote the column.)
        $unread = $this->rpc($I, 'leantime.rpc.Notifications.Notifications.markNotificationUnread', ['id' => $ownerNotifId]);
        Assert::assertArrayNotHasKey('error', $unread, 'markNotificationUnread should respond cleanly: '.json_encode($unread));
        Assert::assertFalse($unread['result'] ?? true, 'editor must NOT mark the owner\'s notification unread (IDOR): '.json_encode($unread));
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
        ], JSON_THROW_ON_ERROR));

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
