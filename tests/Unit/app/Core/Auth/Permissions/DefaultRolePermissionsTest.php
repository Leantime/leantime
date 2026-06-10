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
            new Permission('goals.view', 'View', true),
            new Permission('goals.create', 'Create', true),
            new Permission('goals.edit', 'Edit', true),
            new Permission('goals.delete', 'Delete', true),
            new Permission('files.view', 'View', true),
            new Permission('files.upload', 'Upload', true),
            new Permission('files.delete', 'Delete', true),
            new Permission('reports.view', 'View', true),
            // Calendar: project-scoped capability verbs (view→readonly+, create/edit/delete→editor+)
            // + a GLOBAL manage verb (admin+ cross-user override; managers do NOT get it).
            new Permission('calendar.view', 'View', true),
            new Permission('calendar.create', 'Create', true),
            new Permission('calendar.edit', 'Edit', true),
            new Permission('calendar.delete', 'Delete', true),
            new Permission('calendar.manage', 'Manage any calendar', false),
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
            // Timesheets are company-wide (global): editor gets own-time view/create/edit/delete,
            // manager+ gets manage (cross-user invoicing/reports).
            new Permission('timesheets.view', 'View timesheets', false),
            new Permission('timesheets.create', 'Log time', false),
            new Permission('timesheets.edit', 'Edit timesheets', false),
            new Permission('timesheets.delete', 'Delete timesheets', false),
            new Permission('timesheets.manage', 'Manage timesheets', false),
            // Project-scoped (rename a project's ticket/idea state labels — manager+ in project):
            new Permission('projectsettings.labels.manage', 'Rename project labels', true),
            // Projects: view is project-scoped (readonly+ data read); create/edit/delete are GLOBAL
            // company actions (manager+; editors do NOT get them since global perms aren't matched
            // by the editor project-verb rule — same shape as the timesheets globals).
            new Permission('projects.view', 'View a project', true),
            new Permission('projects.create', 'Create projects', false),
            new Permission('projects.edit', 'Edit projects', false),
            new Permission('projects.delete', 'Delete projects', false),
        ];
    }

    private function grantsFor(string $role): array
    {
        return DefaultRolePermissions::grantsFor($role, $this->catalog());
    }

    public function test_readonly_can_only_view_project_content(): void
    {
        $this->assertEqualsCanonicalizing(['tickets.view', 'comments.view', 'sprints.view', 'wiki.view', 'ideas.view', 'blueprints.view', 'goals.view', 'files.view', 'reports.view', 'calendar.view', 'projects.view'], $this->grantsFor('readonly'));
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
        $this->assertNotContains('goals.create', $grants);      // commenter views but cannot create
        $this->assertContains('goals.view', $grants);           // inherited from readonly
        // Files: a commenter inherits view and gains the standard upload verb (attachments), but
        // cannot delete (editor+).
        $this->assertContains('files.view', $grants);           // inherited from readonly
        $this->assertContains('files.upload', $grants);         // commenter upload verb
        $this->assertNotContains('files.delete', $grants);      // editor+
        // Reports: view-only feature, inherited from readonly (maintainer-approved loosening of
        // the legacy editor+ page gate — it only aggregates readonly-visible data).
        $this->assertContains('reports.view', $grants);
        // Timesheets are editor+ (global); a commenter logs no time.
        $this->assertNotContains('timesheets.view', $grants);
        $this->assertNotContains('timesheets.create', $grants);
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
        $this->assertContains('goals.create', $grants);
        $this->assertContains('goals.edit', $grants);
        $this->assertContains('goals.delete', $grants);
        // Files uses standard project verbs, so editor auto-gets upload + delete (and view).
        $this->assertContains('files.view', $grants);
        $this->assertContains('files.upload', $grants);
        $this->assertContains('files.delete', $grants);
        // Timesheets are GLOBAL-scoped, so the project verb rule does NOT match them — editor gets
        // its own-time keys explicitly (view/create/edit/delete) but NOT the manager-only `manage`.
        $this->assertContains('timesheets.view', $grants);
        $this->assertContains('timesheets.create', $grants);
        $this->assertContains('timesheets.edit', $grants);
        $this->assertContains('timesheets.delete', $grants);
        $this->assertNotContains('timesheets.manage', $grants);
        // Calendar uses standard PROJECT verbs, so editor auto-gets view/create/edit/delete; the
        // GLOBAL manage verb (cross-user override) stays admin+.
        $this->assertContains('calendar.view', $grants);
        $this->assertContains('calendar.create', $grants);
        $this->assertContains('calendar.edit', $grants);
        $this->assertContains('calendar.delete', $grants);
        $this->assertNotContains('calendar.manage', $grants);
        // Projects: editor can VIEW projects (inherited from readonly) but project create/edit/delete
        // are GLOBAL company actions reserved for manager+ (editors do NOT manage projects).
        $this->assertContains('projects.view', $grants);
        $this->assertNotContains('projects.create', $grants);
        $this->assertNotContains('projects.edit', $grants);
        $this->assertNotContains('projects.delete', $grants);
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
        // Timesheets: manager gets the company-wide manage verb AND inherits editor's own-time keys.
        $this->assertContains('timesheets.manage', $grants);
        $this->assertContains('timesheets.view', $grants);
        $this->assertContains('timesheets.edit', $grants);
        // Calendar: manager holds all four project capability verbs (project '*' rule) but NOT the
        // cross-user override — calendar.manage is GLOBAL-scoped and admin-only (legacy override was
        // Auth::userIsAtLeast(admin)).
        $this->assertContains('calendar.view', $grants);
        $this->assertContains('calendar.create', $grants);
        $this->assertContains('calendar.edit', $grants);
        $this->assertContains('calendar.delete', $grants);
        $this->assertNotContains('calendar.manage', $grants);
        // Projects: manager gets the GLOBAL project-management keys (the matrix edit) + inherits view.
        $this->assertContains('projects.view', $grants);
        $this->assertContains('projects.create', $grants);
        $this->assertContains('projects.edit', $grants);
        $this->assertContains('projects.delete', $grants);
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
        $this->assertContains('calendar.manage', $grants);  // cross-user calendar override (admin+)
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
