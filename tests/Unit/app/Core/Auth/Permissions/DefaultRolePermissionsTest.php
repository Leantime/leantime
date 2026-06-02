<?php

namespace Tests\Unit\app\Core\Auth\Permissions;

use Leantime\Core\Auth\Permissions\DefaultRolePermissions;
use Leantime\Core\Auth\Permissions\Permission;

/**
 * Locks the built-in role -> permission matrix against a representative catalog so a change
 * to DefaultRolePermissions that would over- or under-grant a role fails loudly. This is the
 * grant-equivalence guard for the pilot: it proves the seeded grants match the documented
 * role capabilities (readonly view-only; commenter comment/upload; editor content CRUD;
 * manager moderation + all project perms; admin everything-but-company-settings; owner all).
 */
class DefaultRolePermissionsTest extends \Unit\TestCase
{
    /** @return array<int, Permission> */
    private function catalog(): array
    {
        return [
            new Permission('tickets.view', 'View', true),
            new Permission('tickets.comment', 'Comment', true),
            new Permission('tickets.upload', 'Upload', true),
            new Permission('tickets.create', 'Create', true),
            new Permission('tickets.edit', 'Edit', true),
            new Permission('tickets.delete', 'Delete', true),
            new Permission('comments.view', 'View', true),
            new Permission('comments.create', 'Create', true),
            new Permission('comments.moderate', 'Moderate', true),
            // Company-wide (not project-scoped):
            new Permission('users.view', 'View users', false),
            new Permission('users.create', 'Invite/create users', false),
            new Permission('users.edit', 'Edit users', false),
            new Permission('users.delete', 'Delete users', false),
            new Permission('users.import', 'Import users', false),
            new Permission('company.settings.edit', 'Edit company settings', false),
        ];
    }

    private function grantsFor(string $role): array
    {
        return DefaultRolePermissions::grantsFor($role, $this->catalog());
    }

    public function test_readonly_can_only_view_project_content(): void
    {
        $this->assertEqualsCanonicalizing(['tickets.view', 'comments.view'], $this->grantsFor('readonly'));
    }

    public function test_commenter_adds_comment_upload_and_can_create_comments(): void
    {
        $grants = $this->grantsFor('commenter');

        $this->assertContains('tickets.view', $grants);     // inherited
        $this->assertContains('tickets.comment', $grants);
        $this->assertContains('tickets.upload', $grants);
        $this->assertContains('comments.create', $grants);  // explicit commenter grant
        $this->assertNotContains('tickets.create', $grants);
        $this->assertNotContains('tickets.delete', $grants);
        $this->assertNotContains('comments.moderate', $grants);
    }

    public function test_editor_gets_content_crud_but_not_moderation_or_company(): void
    {
        $grants = $this->grantsFor('editor');

        $this->assertContains('tickets.create', $grants);
        $this->assertContains('tickets.edit', $grants);
        $this->assertContains('tickets.delete', $grants);
        $this->assertContains('comments.create', $grants);    // inherited
        $this->assertNotContains('comments.moderate', $grants); // manager+ only
        $this->assertNotContains('users.view', $grants);        // company-wide, admin+
        $this->assertNotContains('users.create', $grants);      // company-wide, manager+
        $this->assertNotContains('company.settings.edit', $grants);
    }

    public function test_manager_moderates_and_holds_all_project_perms_but_no_company(): void
    {
        $grants = $this->grantsFor('manager');

        $this->assertContains('comments.moderate', $grants);
        $this->assertContains('tickets.delete', $grants);
        // Managers may INVITE users (within their own client — scoped in the controller), but
        // cannot view the roster, edit, delete, or import accounts (those stay admin+).
        $this->assertContains('users.create', $grants);
        $this->assertNotContains('users.view', $grants);
        $this->assertNotContains('users.edit', $grants);
        $this->assertNotContains('users.delete', $grants);
        $this->assertNotContains('users.import', $grants);
        $this->assertNotContains('company.settings.edit', $grants);
    }

    public function test_admin_gets_company_wide_except_company_settings(): void
    {
        $grants = $this->grantsFor('admin');

        $this->assertContains('users.view', $grants);
        $this->assertContains('users.create', $grants);
        $this->assertContains('users.edit', $grants);
        $this->assertContains('users.delete', $grants);
        $this->assertContains('users.import', $grants);   // full user management
        $this->assertContains('comments.moderate', $grants);
        $this->assertContains('tickets.delete', $grants);
        $this->assertNotContains('company.settings.edit', $grants); // owner-only
    }

    public function test_owner_gets_everything_including_company_settings(): void
    {
        $grants = $this->grantsFor('owner');

        $this->assertContains('company.settings.edit', $grants);
        $this->assertContains('users.view', $grants);
        $this->assertContains('comments.moderate', $grants);
        $this->assertContains('tickets.delete', $grants);
    }
}
