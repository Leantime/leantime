<?php


namespace Acceptance;

use leantime\core\config;
use leantime\domain\repositories\install;
use Tests\Support\AcceptanceTester;

class InstallTest
{
    private string $randpomDBName;
    public function _before(AcceptanceTester $I)
    {

        $install = new install();
        $this->randpomDBName = 'leantime_' . rand(100000, 999999);
        $install->createDatabase($this->randpomDBName);

        $config = \leantime\core\environment::getInstance();
        $config->dbDatabase =  $this->randpomDBName;

    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
    }

    public function installPageWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/install');

        $I->see('Install');
    }

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
