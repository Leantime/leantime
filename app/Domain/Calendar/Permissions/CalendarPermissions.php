<?php

namespace Leantime\Domain\Calendar\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Calendar permission vocabulary.
 *
 * Calendar is a PERSONAL feature: events and external-calendar subscriptions belong to a user
 * (zp_calendar.userId / zp_gcallinks.userId). Two orthogonal axes:
 *  - CAPABILITY (can you use the calendar at all) — the project-scoped standard verbs below,
 *    resolved against the current project's role (mirroring the legacy editor+ authOrRedirect on
 *    the controllers, which used the effective/project role). They auto-grant through the matrix
 *    with NO DefaultRolePermissions edit: view→readonly+, create/edit/delete→editor+.
 *  - OWNERSHIP (whose data) — enforced IN-BODY in the service (row.userId === currentUserId), with
 *    a cross-user override for `manage`.
 *
 * `manage` is GLOBAL-scoped (admin+ only — it auto-grants through scope:any and is deliberately NOT
 * given to managers): it preserves the legacy admin-only cross-user override (patch's
 * userIsAllowedToUpdate used Auth::userIsAtLeast(admin)).
 *
 * Maintainer-approved loosening: VIEW moves from the legacy editor+ page gate to readonly+ — seeing
 * your OWN calendar is benign (ownership still fences whose events you see).
 *
 * The iCal feed methods (getIcalByHash / getIcalByRequestToken) are NOT part of this vocabulary:
 * they are served by the public, hash-authenticated /calendar/ical route with no session, so they
 * are de-@api'd rather than permission-gated.
 */
final class CalendarPermissions implements ProvidesPermissions
{
    /** View your own calendar (events, external subscriptions, feed url). Readonly+. */
    public const VIEW = 'calendar.view';

    /** Add events / connect external calendars to your own calendar. Editor+. */
    public const CREATE = 'calendar.create';

    /** Edit your own events / external calendars. Editor+ (cross-user requires MANAGE). */
    public const EDIT = 'calendar.edit';

    /** Delete your own events / external calendars. Editor+. */
    public const DELETE = 'calendar.delete';

    /** Cross-user calendar override (act on another user's events). Admin+ (global). */
    public const MANAGE = 'calendar.manage';

    public function domain(): string
    {
        return 'calendar';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::VIEW, 'View your calendar', true),
            new Permission(self::CREATE, 'Add calendar events', true),
            new Permission(self::EDIT, 'Edit calendar events', true),
            new Permission(self::DELETE, 'Delete calendar events', true),
            new Permission(self::MANAGE, 'Manage any user\'s calendar', false),
        ];
    }
}
