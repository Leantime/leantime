<?php

namespace Leantime\Domain\Comments\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The Comments permission vocabulary — verbs only.
 *
 * Comments are project-scoped (a comment lives on a project-owned entity). Note the
 * distinct `moderate` verb: editing/deleting *someone else's* comment is a manager+
 * capability (the matrix grants `moderate` only via `manager: project *`), while the
 * author always edits/deletes their own via the ownership check in the service. Keeping a
 * dedicated `moderate` verb avoids over-granting (a `comments.edit` would seed at editor+).
 */
final class CommentsPermissions implements ProvidesPermissions
{
    public const VIEW = 'comments.view';

    public const CREATE = 'comments.create';

    /** Edit/delete ANY comment (not your own) — manager+. Authors bypass via ownership. */
    public const MODERATE = 'comments.moderate';

    public function domain(): string
    {
        return 'comments';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::VIEW, 'View comments'),
            new Permission(self::CREATE, 'Add comments'),
            new Permission(self::MODERATE, 'Moderate (edit/delete) any comment'),
        ];
    }
}
