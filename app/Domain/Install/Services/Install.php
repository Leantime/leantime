<?php

namespace Leantime\Domain\Install\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Domain\Install\Repositories\Install as InstallRepository;
use Leantime\Domain\Setting\Services\Setting as SettingService;

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
     * @param  array  $values  Validated install values (email, firstname, lastname, company).
     * @return bool True on successful setup, false otherwise.
     *
     * @api
     */
    public function runInstall(array $values): bool
    {
        return $this->installRepo->setupDB($values);
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
     * @api
     */
    public function runUpdate(): bool|array
    {
        session()->forget('db-version');

        return $this->installRepo->updateDB();
    }
}
