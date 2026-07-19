<?php

declare(strict_types=1);

namespace Unit\app\Domain\Users;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\DefaultRolePermissions;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Domain\Users\Controllers\EditUser;
use Leantime\Domain\Users\Permissions\UsersPermissions;
use Leantime\Domain\Users\Repositories\Users as UsersRepository;
use Leantime\Domain\Users\Services\Users as UsersService;
use ReflectionMethod;
use Unit\TestCase;

/**
 * Regression guard for the write-path authorization on user records.
 *
 * A third-party review (2026-07-18) flagged: the Blade capacity section
 * gates on `Roles::admin`, but the controller `buildValuesFromPost`
 * reads `weekly_hours` + `employment_type` straight from `$_POST`. If
 * `EditUser::post()` did not independently require admin at the server
 * boundary, a non-admin could set these fields by crafting a POST.
 *
 * The server gate exists — every write surface carries
 * `#[RequiresPermission(UsersPermissions::EDIT, global: true)]`, and
 * `users.edit` is granted only to admin+ by `DefaultRolePermissions`.
 * PermissionEnforcer throws `AuthorizationException` before the method
 * body runs (Frontcontroller for legacy convention routes,
 * CheckPermissions middleware for Laravel routes, Jsonrpc for the
 * RPC surface).
 *
 * This test guards against silent removal of that attribute (any of
 * the three surfaces) or a future default-permission grant that would
 * hand `users.edit` to a lower role. Both would silently reopen the
 * bypass the reviewer flagged.
 */
class EditUserAuthorizationTest extends TestCase
{
    // ─── Attribute presence on every write surface ────────────────────

    public function test_controller_post_requires_users_edit_permission_globally(): void
    {
        // Legacy convention route: /users/editUser/{id}. If someone
        // strips this attribute, PermissionEnforcer stops enforcing
        // and any authenticated user can POST — the bypass scenario.
        $this->assertRequiresPermission(
            EditUser::class,
            'post',
            UsersPermissions::EDIT,
        );
    }

    public function test_controller_get_requires_users_edit_permission_globally(): void
    {
        // GET is gated too — otherwise a non-admin could view the
        // admin edit form (info leak) even without being able to POST.
        $this->assertRequiresPermission(
            EditUser::class,
            'get',
            UsersPermissions::EDIT,
        );
    }

    public function test_service_editUser_requires_users_edit_permission_globally(): void
    {
        // Service-layer surface — any caller (JSON-RPC, plugins,
        // service-to-service) also passes through PermissionEnforcer
        // because the attribute is on the method, not the controller.
        $this->assertRequiresPermission(
            UsersService::class,
            'editUser',
            UsersPermissions::EDIT,
        );
    }

    public function test_service_updateUser_requires_users_edit_permission_globally(): void
    {
        // updateUser is the JSON-RPC entry point — wraps editUser +
        // project reconciliation. Its attribute is what secures the
        // RPC path (RPC bypasses the controller gate, per the
        // RequiresPermission docblock).
        $this->assertRequiresPermission(
            UsersService::class,
            'updateUser',
            UsersPermissions::EDIT,
        );
    }

    // ─── Default-grant hierarchy — who has users.edit ─────────────────

    public function test_users_edit_is_granted_to_admin_and_owner_only(): void
    {
        // The other half of the bypass guarantee: the attribute above
        // is only meaningful if `users.edit` isn't handed out to a
        // lower role by default. Owner + admin get it; manager gets
        // only users.create; editor/commenter/readonly get no users.*.
        $catalog = [new Permission(UsersPermissions::EDIT, 'Edit users', false)];

        $this->assertContains(
            UsersPermissions::EDIT,
            DefaultRolePermissions::grantsFor('admin', $catalog),
            'admin must retain users.edit — the primary gate'
        );
        $this->assertContains(
            UsersPermissions::EDIT,
            DefaultRolePermissions::grantsFor('owner', $catalog),
            'owner must retain users.edit — inherits admin grants'
        );

        // Everything below admin must NOT have it. If a future default
        // hands users.edit to manager or below, this test fails and
        // the reviewer's bypass concern re-materialises silently.
        foreach (['manager', 'editor', 'commenter', 'readonly'] as $role) {
            $this->assertNotContains(
                UsersPermissions::EDIT,
                DefaultRolePermissions::grantsFor($role, $catalog),
                sprintf('%s must NOT have users.edit by default', $role)
            );
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function assertRequiresPermission(string $class, string $method, string $permission): void
    {
        $reflection = new ReflectionMethod($class, $method);
        $attributes = $reflection->getAttributes(RequiresPermission::class);

        $this->assertCount(
            1,
            $attributes,
            sprintf('%s::%s must declare exactly one #[RequiresPermission] attribute', $class, $method)
        );

        $attr = $attributes[0]->newInstance();
        $this->assertSame(
            $permission,
            $attr->permission,
            sprintf('%s::%s must require %s', $class, $method, $permission)
        );
        $this->assertTrue(
            $attr->global,
            sprintf('%s::%s must be global-scoped (users.* are company-wide, not project-scoped)', $class, $method)
        );
    }
}
