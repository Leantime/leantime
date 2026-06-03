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
            new Permission('sprints.view', 'View', true),
            new Permission('sprints.create', 'Create', true),
            new Permission('sprints.edit', 'Edit', true),
            new Permission('sprints.delete', 'Delete', true),
            new Permission('wiki.view', 'View', true),
            new Permission('wiki.create', 'Create', true),
            new Permission('wiki.edit', 'Edit', true),
            new Permission('wiki.delete', 'Delete', true),
            new Permission('ideas.view', 'View', true),
            new Permission('ideas.create', 'Create', true),
            new Permission('ideas.edit', 'Edit', true),
            new Permission('ideas.delete', 'Delete', true),
            new Permission('blueprints.view', 'View', true),
            new Permission('blueprints.create', 'Create', true),
            new Permission('blueprints.edit', 'Edit', true),
            new Permission('blueprints.delete', 'Delete', true),
            new Permission('comments.view', 'View', true),
            new Permission('comments.create', 'Create', true),
            new Permission('comments.moderate', 'Moderate', true),
            // Company-wide (not project-scoped):
            new Permission('users.view', 'View users', false),
            new Permission('users.create', 'Invite/create users', false),
            new Permission('users.edit', 'Edit users', false),
            new Permission('users.delete', 'Delete users', false),
            new Permission('users.import', 'Import users', false),
            new Permission('clients.view', 'View clients', false),
            new Permission('clients.create', 'Create clients', false),
            new Permission('clients.edit', 'Edit clients', false),
            new Permission('clients.delete', 'Delete clients', false),
            new Permission('company.settings.view', 'View company settings', false),
            new Permission('company.settings.edit', 'Edit company settings', false),
            // Project-scoped (rename a project's ticket/idea state labels — manager+ in project):
            new Permission('projectsettings.labels.manage', 'Rename project labels', true),
        ];
    }

    private function grantsFor(string $role): array
    {
        return DefaultRolePermissions::grantsFor($role, $this->catalog());
    }

    public function test_readonly_can_only_view_project_content(): void
    {
        $this->assertEqualsCanonicalizing(['tickets.view', 'comments.view', 'sprints.view', 'wiki.view', 'ideas.view', 'blueprints.view'], $this->grantsFor('readonly'));
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
        $this->assertNotContains('sprints.create', $grants); // commenter views but cannot create
        $this->assertContains('sprints.view', $grants);       // inherited from readonly
        $this->assertNotContains('wiki.create', $grants);     // commenter views but cannot create
        $this->assertContains('wiki.view', $grants);          // inherited from readonly
        $this->assertNotContains('ideas.create', $grants);    // commenter views but cannot create
        $this->assertContains('ideas.view', $grants);         // inherited from readonly
        $this->assertNotContains('blueprints.create', $grants); // commenter views but cannot create
        $this->assertContains('blueprints.view', $grants);      // inherited from readonly
        $this->assertNotContains('comments.moderate', $grants);
    }

    public function test_editor_gets_content_crud_but_not_moderation_or_company(): void
    {
        $grants = $this->grantsFor('editor');

        $this->assertContains('tickets.create', $grants);
        $this->assertContains('tickets.edit', $grants);
        $this->assertContains('tickets.delete', $grants);
        // Sprints uses the same standard project verbs, so editor auto-gets create/edit/delete.
        $this->assertContains('sprints.create', $grants);
        $this->assertContains('sprints.edit', $grants);
        $this->assertContains('sprints.delete', $grants);
        // Wiki uses the same standard project verbs, so editor auto-gets create/edit/delete.
        $this->assertContains('wiki.create', $grants);
        $this->assertContains('wiki.edit', $grants);
        $this->assertContains('wiki.delete', $grants);
        // Ideas uses the same standard project verbs, so editor auto-gets create/edit/delete.
        $this->assertContains('ideas.create', $grants);
        $this->assertContains('ideas.edit', $grants);
        $this->assertContains('ideas.delete', $grants);
        // Blueprints (canvas) uses the same standard project verbs, so editor auto-gets create/edit/delete.
        $this->assertContains('blueprints.create', $grants);
        $this->assertContains('blueprints.edit', $grants);
        $this->assertContains('blueprints.delete', $grants);
        $this->assertContains('comments.create', $grants);    // inherited
        $this->assertNotContains('comments.moderate', $grants); // manager+ only
        $this->assertNotContains('users.view', $grants);        // company-wide, admin+
        $this->assertNotContains('users.create', $grants);      // company-wide, manager+
        $this->assertNotContains('clients.view', $grants);      // company-wide, admin+
        $this->assertNotContains('company.settings.view', $grants);
        // Label renaming uses the 'manage' verb (not 'edit'), so it stays manager+ and does NOT
        // leak to editor via the project create/edit/delete grant.
        $this->assertNotContains('projectsettings.labels.manage', $grants);
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
        // Client management stays admin+ (managers have no real client access today — ShowAll
        // redirects them and ShowClient 403s them), so a manager gets NO clients.* —
        // grant-equivalent with the current behavior, not the aspirational target matrix.
        $this->assertNotContains('clients.view', $grants);
        $this->assertNotContains('clients.create', $grants);
        $this->assertNotContains('clients.edit', $grants);
        $this->assertNotContains('clients.delete', $grants);
        // Renaming a project's labels is a manager-in-project capability (project '*' grant).
        $this->assertContains('projectsettings.labels.manage', $grants);
        $this->assertNotContains('company.settings.view', $grants);
        $this->assertNotContains('company.settings.edit', $grants);
    }

    public function test_admin_gets_company_wide_including_company_settings(): void
    {
        $grants = $this->grantsFor('admin');

        $this->assertContains('users.view', $grants);
        $this->assertContains('users.create', $grants);
        $this->assertContains('users.edit', $grants);
        $this->assertContains('users.delete', $grants);
        $this->assertContains('users.import', $grants);   // full user management
        $this->assertContains('clients.view', $grants);
        $this->assertContains('clients.create', $grants);
        $this->assertContains('clients.edit', $grants);
        $this->assertContains('clients.delete', $grants);   // full client management
        $this->assertContains('projectsettings.labels.manage', $grants);
        $this->assertContains('comments.moderate', $grants);
        $this->assertContains('tickets.delete', $grants);
        // Per policy (admin views + edits company settings), admins hold both company.settings
        // keys via an explicit grant alongside the wildcard-with-exclude rule.
        $this->assertContains('company.settings.view', $grants);
        $this->assertContains('company.settings.edit', $grants);
    }

    public function test_owner_gets_everything_including_company_settings(): void
    {
        $grants = $this->grantsFor('owner');

        $this->assertContains('company.settings.view', $grants);
        $this->assertContains('company.settings.edit', $grants);
        $this->assertContains('projectsettings.labels.manage', $grants);
        $this->assertContains('clients.delete', $grants);
        $this->assertContains('users.view', $grants);
        $this->assertContains('comments.moderate', $grants);
        $this->assertContains('tickets.delete', $grants);
    }

    /**
     * Regression: a rule that combines an explicit `keys` allow-list with an `exclude` list must
     * still honor the exclude. matches() previously returned early for `keys` rules and bypassed
     * the exclude entirely, which could over-grant an excluded permission.
     */
    public function test_keys_rule_still_honors_exclude(): void
    {
        $matches = new \ReflectionMethod(DefaultRolePermissions::class, 'matches');
        $matches->setAccessible(true);

        $rule = [
            'scope' => 'global',
            'keys' => ['company.settings.view', 'company.settings.edit'],
            'exclude' => ['company.settings.edit'],
        ];

        $included = new Permission('company.settings.view', 'View', false);
        $excluded = new Permission('company.settings.edit', 'Edit', false);

        $this->assertTrue($matches->invoke(null, $included, $rule), 'A keys-listed, non-excluded permission still matches');
        $this->assertFalse($matches->invoke(null, $excluded, $rule), 'A keys-listed permission that is also excluded must NOT match');
    }
}
