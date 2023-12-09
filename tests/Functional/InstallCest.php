<?php

namespace Functional;

use Codeception\Attribute\Depends;
use Tests\Support\FunctionalTester;
use Tests\Support\Page\Functional\Install;

class InstallCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function installPageWorks(FunctionalTester $I)
    {
        $I->amOnPage('/install');
        echo $I->grabPageSource();
        $I->waitForElementVisible('.registrationForm', 120);

        $I->see('Install');
    }

    #[Depends('installPageWorks')]
    public function createDBSuccessfully(FunctionalTester $I, Install $installPage)
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
