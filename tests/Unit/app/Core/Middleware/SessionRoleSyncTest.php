<?php

namespace Unit\app\Core\Middleware;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Auth\Contracts\ChecksProjectAccess;
use Leantime\Core\Auth\PasswordFingerprint;
use Leantime\Core\Auth\Permissions\PermissionRepository;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Auth\RoleResolver;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Middleware\AuthenticateSession;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * #4090: a role an admin changes takes effect on the user's next request, not after their next
 * login. Covers the global role (user management) and the role inside a project.
 */
class SessionRoleSyncTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const PASSWORD_HASH = '$2y$hash';

    private const PROJECT_ID = 3;

    /** The user's project role as stored in zp_relationuserproject ('' = inherit). */
    private string $storedProjectRole = '';

    private int $projectRoleLookups = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.stores.installation' => ['driver' => 'array'],
            'cache.stores.instance' => ['driver' => 'array'],
            'cache.default' => 'instance',
        ]);
        Cache::forgetDriver(['installation', 'instance']);

        session()->flush();
        $this->storedProjectRole = '';
        $this->projectRoleLookups = 0;

        app()->instance(AuthService::class, $this->make(AuthService::class, [
            'logout' => fn () => $this->fail('a role change must not log the user out'),
        ]));

        app()->instance(ChecksProjectAccess::class, $this->makeEmpty(ChecksProjectAccess::class, [
            'isUserAssignedToProject' => true,
            'getProjectRole' => function (): string {
                $this->projectRoleLookups++;

                return $this->storedProjectRole;
            },
        ]));
    }

    /** A logged-in session as login + project switch leave it behind. */
    private function loggedInSession(string $role, string $projectRole = ''): void
    {
        session([
            'userdata' => [
                'id' => 7,
                'role' => $role,
                'projectRole' => $projectRole,
                'clientId' => 1,
                'pwfp' => PasswordFingerprint::of(self::PASSWORD_HASH),
            ],
            'currentProject' => self::PROJECT_ID,
        ]);
    }

    /** Run AuthenticateSession for the next request, with the given zp_user.role in the database. */
    private function nextRequest(int $roleKeyInDatabase, int $clientIdInDatabase = 1): void
    {
        $userRepo = $this->make(UserRepository::class, [
            'getUser' => fn ($id) => [
                'id' => (int) $id,
                'password' => self::PASSWORD_HASH,
                'status' => 'a',
                'role' => $roleKeyInDatabase,
                'clientId' => $clientIdInDatabase,
            ],
        ]);

        $request = IncomingRequest::create('/tickets/showKanban');
        $request->setLaravelSession(app('session')->driver());

        $reachedApp = false;
        (new AuthenticateSession($userRepo))->handle($request, function () use (&$reachedApp) {
            $reachedApp = true;

            return new Response('ok');
        });

        $this->assertTrue($reachedApp, 'a role change must not end the session');
    }

    /** The permission engine as a fresh request builds it. Only editors and up may create to-dos. */
    private function permissions(): PermissionService
    {
        $repo = $this->make(PermissionRepository::class, [
            'getRolePermissionMap' => fn (): array => [
                'readonly' => [],
                'commenter' => [],
                'editor' => ['tickets.create'],
                'manager' => ['tickets.create'],
            ],
            'getAllPermissions' => fn (): array => [
                ['permissionKey' => 'tickets.create', 'isProjectScoped' => 1],
            ],
        ]);

        $projectAccess = app(ChecksProjectAccess::class);

        return new PermissionService($repo, new RoleResolver($projectAccess), $projectAccess, $this->allModulesOn());
    }

    public function test_downgraded_role_loses_the_permission_on_the_next_request(): void
    {
        $this->loggedInSession('editor');
        $this->assertTrue($this->permissions()->currentUserCan('tickets.create'));

        // Admin sets the user to Read Only (role key 5) in user management.
        $this->nextRequest(roleKeyInDatabase: 5);

        $this->assertSame('readonly', session('userdata.role'));
        $this->assertFalse($this->permissions()->currentUserCan('tickets.create'), 'session-project check');
        $this->assertFalse($this->permissions()->currentUserCan('tickets.create', self::PROJECT_ID), 'explicit-project check');
    }

    public function test_downgraded_manager_loses_the_permission_on_the_next_request(): void
    {
        $this->loggedInSession('manager');
        $this->assertTrue($this->permissions()->currentUserCan('tickets.create'));

        $this->nextRequest(roleKeyInDatabase: 5);

        $this->assertSame('readonly', session('userdata.role'));
        $this->assertFalse($this->permissions()->currentUserCan('tickets.create'));
    }

    public function test_upgraded_role_gains_the_permission_on_the_next_request(): void
    {
        $this->loggedInSession('readonly');
        $this->assertFalse($this->permissions()->currentUserCan('tickets.create'));

        $this->nextRequest(roleKeyInDatabase: 20);

        $this->assertSame('editor', session('userdata.role'));
        $this->assertTrue($this->permissions()->currentUserCan('tickets.create'));
    }

    public function test_downgraded_project_role_loses_the_permission_on_the_next_request(): void
    {
        $this->storedProjectRole = '20';
        $this->loggedInSession('editor', projectRole: 'editor');
        $this->assertTrue($this->permissions()->currentUserCan('tickets.create'));

        // A project admin sets the user to Read Only in this project only.
        $this->storedProjectRole = '5';
        $this->nextRequest(roleKeyInDatabase: 20);

        $this->assertSame('editor', session('userdata.role'), 'the global role is unchanged');
        $this->assertSame('readonly', session('userdata.projectRole'));
        $this->assertFalse($this->permissions()->currentUserCan('tickets.create'));
    }

    public function test_legacy_project_role_name_is_enforced(): void
    {
        // Older membership rows store the role name instead of its key.
        $this->storedProjectRole = 'readonly';
        $this->loggedInSession('editor', projectRole: 'editor');

        $this->nextRequest(roleKeyInDatabase: 20);

        $this->assertSame('readonly', session('userdata.projectRole'));
        $this->assertFalse($this->permissions()->currentUserCan('tickets.create'));
    }

    public function test_project_role_set_back_to_inherit_follows_the_global_role(): void
    {
        $this->storedProjectRole = '5';
        $this->loggedInSession('editor', projectRole: 'readonly');
        $this->assertFalse($this->permissions()->currentUserCan('tickets.create'));

        $this->storedProjectRole = '';
        $this->nextRequest(roleKeyInDatabase: 20);

        $this->assertSame('', session('userdata.projectRole'));
        $this->assertTrue($this->permissions()->currentUserCan('tickets.create'));
    }

    public function test_unchanged_roles_leave_the_session_alone(): void
    {
        $this->storedProjectRole = '5';
        $this->loggedInSession('editor', projectRole: 'readonly');
        $userdataBefore = session('userdata');

        $this->nextRequest(roleKeyInDatabase: 20);

        $this->assertSame($userdataBefore, session('userdata'));
    }

    public function test_managers_need_no_project_role_lookup(): void
    {
        $this->loggedInSession('manager');

        $this->nextRequest(roleKeyInDatabase: 30);

        $this->assertSame(0, $this->projectRoleLookups, 'project roles never apply to managers and above');
    }

    public function test_client_change_reaches_the_session(): void
    {
        $this->loggedInSession('editor');

        $this->nextRequest(roleKeyInDatabase: 20, clientIdInDatabase: 4);

        $this->assertSame(4, session('userdata.clientId'));
    }
}
