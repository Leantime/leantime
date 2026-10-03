<?php

namespace Unit\app\Core\Auth;

use Leantime\Core\Auth\RoleCeiling;

/**
 * A user may only hand out roles at or below their own global role.
 */
class RoleCeilingTest extends \Unit\TestCase
{
    public function test_manager_cannot_assign_admin_or_owner(): void
    {
        session(['userdata' => ['id' => 4, 'role' => 'manager']]);
        $ceiling = new RoleCeiling;

        $this->assertTrue($ceiling->canAssign(20));
        $this->assertTrue($ceiling->canAssign('30'));
        $this->assertTrue($ceiling->canAssign('manager'));
        $this->assertFalse($ceiling->canAssign(40));
        $this->assertFalse($ceiling->canAssign('50'));
        $this->assertFalse($ceiling->canAssign('owner'));
        $this->assertSame([5, 10, 20, 30], array_keys($ceiling->assignableRoles()));
        $this->assertTrue($ceiling->callerIsBelow('admin'));
    }

    public function test_admin_cannot_assign_owner_but_owner_can(): void
    {
        session(['userdata' => ['id' => 4, 'role' => 'admin']]);
        $this->assertTrue((new RoleCeiling)->canAssign(40));
        $this->assertFalse((new RoleCeiling)->canAssign(50));
        $this->assertFalse((new RoleCeiling)->callerIsBelow('admin'));

        session(['userdata' => ['id' => 4, 'role' => 'owner']]);
        $this->assertTrue((new RoleCeiling)->canAssign(50));
    }

    public function test_unknown_roles_and_unresolvable_callers_fail_closed(): void
    {
        session(['userdata' => ['id' => 4, 'role' => 'owner']]);
        $this->assertFalse((new RoleCeiling)->canAssign(99));
        $this->assertFalse((new RoleCeiling)->canAssign('superuser'));
        $this->assertTrue((new RoleCeiling)->canAssign(''), 'no role is always assignable');

        // Authenticated, but the role cannot be resolved.
        session(['userdata' => ['id' => 4]]);
        $this->assertFalse((new RoleCeiling)->canAssign(5));
        $this->assertTrue((new RoleCeiling)->callerIsBelow('admin'));
    }

    public function test_no_ceiling_without_an_authenticated_caller(): void
    {
        // CLI / system jobs / invite onboarding: nobody to apply a ceiling to.
        session()->forget('userdata');

        $this->assertTrue((new RoleCeiling)->canAssign(50));
        $this->assertFalse((new RoleCeiling)->callerIsBelow('admin'));
    }
}
