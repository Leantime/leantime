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

        $this->I->waitForElementVisible('.alert');

        $this->I->waitForText('The installation was successful', 90);

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

        Fixtures::add('installed', true);
        $this->suppressModals();
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
