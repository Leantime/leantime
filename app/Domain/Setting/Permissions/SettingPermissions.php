<?php

namespace Leantime\Domain\Setting\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Setting permission vocabulary — spans two scopes.
 *
 *  - COMPANY settings (`company.settings.*`) — company-wide (projectScoped = false), resolved
 *    against the GLOBAL role. Covers the editCompanySettings screen + the company logo.
 *  - PROJECT labels (`projectsettings.labels.manage`) — project-scoped (projectScoped = true),
 *    resolved against the user's role IN the project. Renaming a project's ticket/idea state
 *    labels (the EditBoxLabel dialog). The verb is `manage` (not `edit`) on purpose: it keeps
 *    the capability at manager+ and stops it leaking to editor via the project
 *    create/edit/delete grant.
 *
 * The generic getSetting/saveSetting/deleteSetting key/value primitives are deliberately NOT in
 * this vocabulary — they read/write ANY setting and are internal infrastructure, excluded from
 * the JSON-RPC surface entirely (see the Setting service).
 */
final class SettingPermissions implements ProvidesPermissions
{
    /** View company-wide settings (branding, language, notification defaults). */
    public const COMPANY_VIEW = 'company.settings.view';

    /** Edit company-wide settings (incl. the company logo). */
    public const COMPANY_EDIT = 'company.settings.edit';

    /** Rename a project's ticket/idea state labels — manager+ in the project. */
    public const PROJECT_LABELS = 'projectsettings.labels.manage';

    public function domain(): string
    {
        return 'setting';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::COMPANY_VIEW, 'View company settings', false),
            new Permission(self::COMPANY_EDIT, 'Edit company settings', false),
            new Permission(self::PROJECT_LABELS, 'Rename project labels', true),
        ];
    }
}
