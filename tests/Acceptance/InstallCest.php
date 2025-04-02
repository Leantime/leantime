<?php

namespace Acceptance;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Install;

class InstallCest
{
    public function _before(AcceptanceTester $I) {}

    #[Group('install, api')]
    public function installPageWorks(AcceptanceTester $I): void
    {
        $I->amOnPage('/install');
        $I->waitForElementVisible('.registrationForm', 10);

        $I->see('Install');
    }

    #[Group('install, api')]
    #[Depends('installPageWorks')]
    public function createDBSuccessfully(AcceptanceTester $I, Install $installPage): void
    {
        $installPage->install(
            'test@leantime.io',
            'Test123456!',
            'John',
            'Smith',
            'Smith & Co'
        );
    }
}
