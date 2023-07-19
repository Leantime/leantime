<?php


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
use Codeception\Attribute\Depends;

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
    public function createDBSuccessfully(AcceptanceTester $I)
    {
        $I->amOnPage('/install');

        $I->fillField(['name' => 'email'], 'test@leantime.io');
        $I->fillField(['name' => 'password'], 'test');
        $I->fillField(['name' => 'firstname'], 'John');
        $I->fillField(['name' => 'lastname'], 'Smith');
        $I->fillField(['name' => 'company'], 'Smith & Co');
        $I->click('Install');

        $I->see('The installation was successful');
    }
}
