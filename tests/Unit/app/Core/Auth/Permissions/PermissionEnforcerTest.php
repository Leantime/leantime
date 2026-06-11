<?php

namespace Tests\Unit\app\Core\Auth\Permissions;

use Leantime\Core\Auth\Permissions\PermissionEnforcer;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Auth\Permissions\RequiresPermission;

/**
 * Verifies the PermissionEnforcer resolves the project scope correctly for each
 * RequiresPermission mode: entityScoped defers (the method self-authorizes its loaded entity),
 * global checks the company-wide role, projectIdParam reads the named request param, and the
 * default falls back to the session project. A method with no attribute is a complete no-op.
 */
class PermissionEnforcerTest extends \Unit\TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Build an enforcer whose engine records every currentUserCan(...) call into $calls and
     * answers $allow, so we can assert exactly what the enforcer asked the engine.
     *
     * @param  array<int, array{key: string, projectId: ?int, forceGlobal: bool}>  $calls
     */
    private function spyEnforcer(array &$calls, bool $allow = true): PermissionEnforcer
    {
        $permissions = $this->make(PermissionService::class, [
            'currentUserCan' => function (string $key, ?int $projectId = null, ?bool $forceGlobal = false) use (&$calls, $allow): bool {
                $calls[] = ['key' => $key, 'projectId' => $projectId, 'forceGlobal' => (bool) $forceGlobal];

                return $allow;
            },
        ]);

        return new PermissionEnforcer($permissions);
    }

    public function test_entity_scoped_defers_and_never_calls_the_engine(): void
    {
        // entityScoped methods authorize their loaded entity's project in their own body, so the
        // enforcer must not run a check here (it can't see the entity, would use the wrong
        // project). Even a denying engine must produce no call and no throw.
        $calls = [];
        $enforcer = $this->spyEnforcer($calls, allow: false);

        $enforcer->enforce(PermissionEnforcerFixture::class, 'entityScopedAction', ['id' => 5]);

        $this->assertSame([], $calls, 'entityScoped should defer to the in-method authorize()');
    }

    public function test_global_checks_company_role_not_a_project(): void
    {
        $calls = [];
        $enforcer = $this->spyEnforcer($calls);

        $enforcer->enforce(PermissionEnforcerFixture::class, 'globalAction', []);

        $this->assertSame([['key' => 'users.create', 'projectId' => null, 'forceGlobal' => true]], $calls);
    }

    public function test_project_id_param_is_read_from_the_named_argument(): void
    {
        $calls = [];
        $enforcer = $this->spyEnforcer($calls);

        $enforcer->enforce(PermissionEnforcerFixture::class, 'paramAction', ['projectId' => 42]);

        $this->assertSame([['key' => 'tickets.view', 'projectId' => 42, 'forceGlobal' => false]], $calls);
    }

    public function test_default_falls_back_to_the_session_project(): void
    {
        session(['currentProject' => 7]);

        $calls = [];
        $enforcer = $this->spyEnforcer($calls);

        $enforcer->enforce(PermissionEnforcerFixture::class, 'sessionAction', []);

        $this->assertSame([['key' => 'tickets.view', 'projectId' => 7, 'forceGlobal' => false]], $calls);
    }

    public function test_unannotated_method_is_a_noop(): void
    {
        $calls = [];
        $enforcer = $this->spyEnforcer($calls, allow: false);

        $enforcer->enforce(PermissionEnforcerFixture::class, 'plainAction', []);

        $this->assertSame([], $calls);
    }

    public function test_mandatory_project_param_absent_fails_closed(): void
    {
        // paramAction declares projectIdParam:'projectId' and types it `int` (no default) — the
        // project is mandatory. With it absent, the enforcer must NOT fall back to the session
        // project (which would authorize the wrong project); it denies without consulting the
        // engine. allow:true proves the denial comes from the unresolved-project path, not a
        // negative engine answer.
        config(['permissions.enforce' => true]);

        $calls = [];
        $enforcer = $this->spyEnforcer($calls, allow: true);

        $threw = false;
        try {
            $enforcer->enforce(PermissionEnforcerFixture::class, 'paramAction', []);
        } catch (\Leantime\Core\Exceptions\AuthorizationException) {
            $threw = true;
        }

        $this->assertTrue($threw, 'an unresolvable mandatory project param must deny');
        $this->assertSame([], $calls, 'the engine must not be consulted when the project is unresolvable');
    }

    public function test_mandatory_project_param_explicit_null_fails_closed(): void
    {
        // isset() was the original bug: it is false for an explicit null, so a null projectId
        // silently fell through to the session project. A mandatory param passed null now denies.
        config(['permissions.enforce' => true]);

        $calls = [];
        $enforcer = $this->spyEnforcer($calls, allow: true);

        $threw = false;
        try {
            $enforcer->enforce(PermissionEnforcerFixture::class, 'paramAction', ['projectId' => null]);
        } catch (\Leantime\Core\Exceptions\AuthorizationException) {
            $threw = true;
        }

        $this->assertTrue($threw, 'an explicit-null mandatory project param must deny');
        $this->assertSame([], $calls);
    }

    public function test_optional_project_param_keeps_the_session_fallback(): void
    {
        // optionalParamAction defaults projectId to null ("current project"), so an absent value
        // is legitimate — the enforcer authorizes against the session project, exactly as the
        // method itself will operate. This is what makes the poll/dashboard endpoints keep working.
        session(['currentProject' => 7]);

        $calls = [];
        $enforcer = $this->spyEnforcer($calls);

        $enforcer->enforce(PermissionEnforcerFixture::class, 'optionalParamAction', []);

        $this->assertSame([['key' => 'tickets.view', 'projectId' => 7, 'forceGlobal' => false]], $calls);
    }
}

/**
 * Fixture exercising each RequiresPermission resolution mode. Bodies are intentionally empty —
 * only the attributes matter to the enforcer.
 */
class PermissionEnforcerFixture
{
    #[RequiresPermission('tickets.edit', entityScoped: true)]
    public function entityScopedAction(int $id): void {}

    #[RequiresPermission('users.create', global: true)]
    public function globalAction(): void {}

    #[RequiresPermission('tickets.view', projectIdParam: 'projectId')]
    public function paramAction(int $projectId): void {}

    // Same attribute, but the project param is OPTIONAL (defaults to null) — "current project"
    // semantics. An absent/null value must keep the session fallback, not deny.
    #[RequiresPermission('tickets.view', projectIdParam: 'projectId')]
    public function optionalParamAction(?int $projectId = null): void {}

    #[RequiresPermission('tickets.view')]
    public function sessionAction(): void {}

    public function plainAction(): void {}
}
