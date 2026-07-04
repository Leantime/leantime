<?php

namespace Leantime\Domain\Api\Permissions;

use Leantime\Core\Auth\Permissions\Permission;
use Leantime\Core\Auth\Permissions\ProvidesPermissions;

/**
 * The API (key management) permission vocabulary — the verbs only.
 *
 * Leantime API keys act as service accounts (a key IS a user row with a role), so creating,
 * listing, and editing them is an installation-wide administrative capability — the management
 * UI (ApiKey / NewApiKey / DelAPIKey controllers) is already `authOrRedirect([owner, admin])`.
 * The single verb below is therefore COMPANY-WIDE (`projectScoped = false`); call sites gate with
 * `#[RequiresPermission(ApiPermissions::MANAGE, global: true)]`, which by the default role map
 * lands on admin/owner only.
 *
 * Note: authenticating WITH an existing key (getAPIKeyUser) is not gated by this — that is the
 * auth primitive itself, invoked by the AuthCheck middleware, not a management action.
 */
final class ApiPermissions implements ProvidesPermissions
{
    /** Create, list, edit, or remove API keys / service-account credentials (company-wide). */
    public const MANAGE = 'api.manage';

    public function domain(): string
    {
        return 'api';
    }

    public function permissions(): array
    {
        return [
            new Permission(self::MANAGE, 'Manage API keys', false),
        ];
    }
}
