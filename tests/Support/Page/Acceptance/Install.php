<?php

declare(strict_types=1);

namespace Tests\Support\Page\Acceptance;

use Codeception\Util\Fixtures;
use Tests\Support\AcceptanceTester;

class Install
{
    protected AcceptanceTester $I;

    protected $app;

    public function __construct(AcceptanceTester $I)
    {
        $this->I = $I;
        $this->app = $I->getApplication();
    }

    public function install($email, $password, $firstname, $lastname, $company): void
    {
        if (Fixtures::exists('installed')) {
            $this->suppressModals();

            return;
        }

        $this->I->amOnPage('/install');
        $this->I->fillField(['name' => 'email'], $email);
        $this->I->fillField(['name' => 'firstname'], $firstname);
        $this->I->fillField(['name' => 'lastname'], $lastname);
        $this->I->fillField(['name' => 'company'], $company);
        $this->I->click('Install');

        // A successful install redirects straight into the onboarding wizard
        // (auth/userInvite), starting on the "Setting Account Details" step —
        // there is no longer a standalone success .alert page. Wait for the
        // jobTitle field (the first field this method fills) to confirm the
        // step has rendered before continuing.
        $this->I->waitForElementVisible(['name' => 'jobTitle'], 90);

        $this->I->fillField(['name' => 'jobTitle'], 'CEO');

        $this->I->fillField(['name' => 'password'], $password);

        $this->I->click('Next');
        $this->I->waitForText('Determining A Visual Experience', 90);

        $this->I->click('Next');
        $this->I->waitForText('Creating A Comfortable View', 90);

        $this->I->click('Next');
        $this->I->waitForText('Shaping A Daily Flow', 90);

        $this->I->click('Next');
        $this->I->waitForText('Your Leantime journey is about to begin', 90);

        $this->I->click('Complete Sign up');
        $this->waitForActiveAccount($email);

        Fixtures::add('installed', true);
        $this->suppressModals();
    }

    /**
     * Wait until the last onboarding step has activated the owner account.
     *
     * The account stays pending ('i') until "Complete Sign up" finishes, and pending accounts
     * can't log in or use a token. Without this wait, a test that starts right after the install
     * (e.g. BearerApiCest, which mints a token in the DB) could get 401 while that request was
     * still running.
     */
    private function waitForActiveAccount(string $email): void
    {
        $deadline = time() + 90;

        do {
            $status = (string) $this->I->grabFromDatabase('zp_user', 'status', ['username' => $email]);
            if (strtolower($status) === 'a') {
                return;
            }

            usleep(250_000);
        } while (time() < $deadline);

        throw new \RuntimeException("Install: the owner account {$email} was not activated within 90 seconds (status '{$status}').");
    }

    /**
     * Suppress all helper modals for testing
     */
    private function suppressModals(): void
    {
        $userService = $this->app->make(\Leantime\Domain\Users\Services\Users::class);
        session(['userdata.id' => 1]);

        // Suppress all known modals
        $userService->updateUserSettings('modals', 'projectDashboard', true);
        $userService->updateUserSettings('modals', 'home', true);
        $userService->updateUserSettings('modals', 'kanban', true);
        $userService->updateUserSettings('modals', 'roadmap', true);
        $userService->updateUserSettings('modals', 'goals', true);
    }
}
