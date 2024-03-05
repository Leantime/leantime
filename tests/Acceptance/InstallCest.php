<?php

namespace Acceptance;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Install;

class InstallCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    #[Group('install')]
    public function installPageWorks(AcceptanceTester $I): void
    {
        $I->amOnPage('/install');
        $I->waitForElementVisible('.registrationForm', 120);

        $I->see('Install');
    }

    #[Group('install')]
    #[Depends('installPageWorks')]
    public function createDBSuccessfully(AcceptanceTester $I, Install $installPage): void
    {
        $installPage->install(
            'test@leantime.io',
            'test',
            'John',
            'Smith',
            'Smith & Co'
        );
    }
}
