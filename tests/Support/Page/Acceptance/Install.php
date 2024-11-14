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
            return;
        }

        $this->I->amOnPage('/install');
        $this->I->fillField(['name' => 'email'], $email);
        $this->I->fillField(['name' => 'password'], $password);
        $this->I->fillField(['name' => 'firstname'], $firstname);
        $this->I->fillField(['name' => 'lastname'], $lastname);
        $this->I->fillField(['name' => 'company'], $company);
        $this->I->click('Install');

        $this->I->waitForElementVisible('.login-alert');

        $this->I->see('The installation was successful');

        // Disable all on-boarding modal popups.
        $this->app->make(\Leantime\Domain\Setting\Repositories\Setting::class)->saveSetting('companysettings.completedOnboarding', 0);

        Fixtures::add('installed', true);
    }
}
