<?php

namespace Leantime\Domain\Install\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Domain\Install\Repositories\Install as InstallRepository;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Users\Events\UserCreated;

class Install
{
    /**
     * @param  AppSettings  $appSettings  Application settings (version metadata).
     * @param  InstallRepository  $installRepo  Install data-access layer (DB setup/update). The Install
     *                                          domain runs before the full app/DB bootstrap is guaranteed,
     *                                          so the service legitimately wraps the repository directly.
     * @param  SettingService  $settingService  Setting service used to read the stored db-version.
     */
    public function __construct(
        protected AppSettings $appSettings,
        protected InstallRepository $installRepo,
        protected SettingService $settingService
    ) {}

    /**
     * currentVersion - gets the currently installed leantime version
     *
     * @api
     */
    public function currentVersion(): string
    {
        return $this->appSettings->appVersion;
    }

    /**
     * isInstalled - determines whether Leantime has already been installed.
     *
     * @return bool True when the installation has already completed.
     *
     * @api
     */
    public function isInstalled(): bool
    {
        return $this->installRepo->checkIfInstalled();
    }

    /**
     * validateInstallInput - validates the admin/company fields submitted during installation.
     *
     * Throws on the first missing field, preserving the original per-field error order
     * (email, firstname, lastname, company). The exception message carries the language
     * key the controller should surface as a notification.
     *
     * @param  array  $values  Submitted install values (email, firstname, lastname, company).
     *
     * @throws \InvalidArgumentException When a required field is missing.
     *
     * @api
     */
    public function validateInstallInput(array $values): void
    {
        if (empty($values['email'])) {
            throw new \InvalidArgumentException('notification.enter_email');
        }

        if (empty($values['firstname'])) {
            throw new \InvalidArgumentException('notification.enter_firstname');
        }

        if (empty($values['lastname'])) {
            throw new \InvalidArgumentException('notification.enter_lastname');
        }

        if (empty($values['company'])) {
            throw new \InvalidArgumentException('notification.enter_company');
        }
    }

    /**
     * runInstall - executes the database setup for a fresh installation.
     *
     * The installer seeds the first (owner) account with id 1 (see SchemaBuilder), reported as
     * {@see UserCreated} with source 'install' once the setup succeeded. That report is a side
     * effect: a failing listener is logged and does not turn a finished installation into an error.
     *
     * @param  array  $values  Validated install values (email, firstname, lastname, company).
     * @return bool True on successful setup, false otherwise.
     *
     * @internal Not exposed over JSON-RPC: only the Install controller may run the installer.
     */
    public function runInstall(array $values): bool
    {
        $installed = $this->installRepo->setupDB($values);

        if (! $installed) {
            return false;
        }

        try {
            UserCreated::dispatch(userId: 1, role: 50, source: 'install');
        } catch (\Throwable $e) {
            Log::error($e);
        }

        return true;
    }

    /**
     * resumeOwnerSetup - returns the onboarding token of an owner the installer created but nobody
     * ever finished setting up, so /install can send the visitor back into that setup.
     *
     * The installer creates the owner without a password (status 'i') and only the onboarding
     * reached through the redirect after the install activates it. When that redirect never
     * arrives (the install request outlived a proxy timeout, the tab was closed, the redirect
     * pointed at an unreachable URL), the instance is left with an owner that cannot log in or
     * reset a password, while /install refuses to run again. As long as that owner is the only
     * account and has never signed in, the instance is as unclaimed as before the install, so
     * handing out its setup link exposes nothing an uninstalled instance wouldn't.
     *
     * @return string|null The token for /auth/userInvite/{token}, or null when there is no such owner.
     *
     * @internal Not exposed over JSON-RPC: only the Install controller may hand out the owner setup.
     */
    public function resumeOwnerSetup(): ?string
    {
        try {
            $firstUsers = $this->installRepo->getFirstUsers(2);

            if (! self::isOwnerSetupPending($firstUsers)) {
                return null;
            }

            $owner = $firstUsers[0];
            $existingToken = (string) ($owner['pwReset'] ?? '');
            if ($existingToken !== '') {
                return $existingToken;
            }

            $newToken = Str::random(32);
            if (! $this->installRepo->setInviteToken((int) $owner['id'], $newToken)) {
                return null;
            }

            return $newToken;
        } catch (\Throwable $e) {
            // Fail closed: when we can't tell, nobody is sent into the owner's setup.
            Log::error($e);

            return null;
        }
    }

    /**
     * isOwnerSetupPending - whether the user rows show an installer-created owner that was never set up.
     *
     * True only when the owner is the ONLY account, is still pending (status 'i') and has never
     * signed in. Any second account (an SSO/LDAP user, an API key, an invited user) or any sign-in
     * means the instance is in use, and its owner account must not be handed out.
     *
     * @param  array<int, array<string, mixed>>  $firstUsers  The first user rows (at least two requested).
     */
    public static function isOwnerSetupPending(array $firstUsers): bool
    {
        if (count($firstUsers) !== 1) {
            return false;
        }

        $owner = $firstUsers[0];

        if ((int) ($owner['role'] ?? 0) !== 50) {
            return false;
        }

        if (strtolower((string) ($owner['status'] ?? '')) !== 'i') {
            return false;
        }

        return empty($owner['lastlogin']);
    }

    /**
     * needsUpdate - determines whether the stored db-version is behind the application's db-version.
     *
     * @return bool True when a database update is required.
     *
     * @api
     */
    public function needsUpdate(): bool
    {
        $dbVersion = $this->settingService->getSetting('db-version');

        return $this->appSettings->dbVersion != $dbVersion;
    }

    /**
     * runUpdate - executes pending database update scripts.
     *
     * Clears the cached db-version before running so the update starts from a clean state,
     * then delegates to the repository. Returns true on success or an array of error messages
     * on failure (preserving the repository's existing return contract).
     *
     * @return bool|array True on success, or an array of error messages on failure.
     *
     * @throws BindingResolutionException
     *
     * @internal Not exposed over JSON-RPC: only the Install/Update controllers may run migrations.
     */
    public function runUpdate(): bool|array
    {
        session()->forget('db-version');

        return $this->installRepo->updateDB();
    }
}
