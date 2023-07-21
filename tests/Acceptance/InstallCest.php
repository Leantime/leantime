<?php


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
use Codeception\Attribute\Depends;
use Tests\Support\Page\Acceptance\Install;

class InstallCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    public function installPageWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/install');
        $I->see('Install');
    }

    #[Depends('installPageWorks')]
    public function createDBSuccessfully(AcceptanceTester $I, Install $installPage)
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
