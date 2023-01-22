<?php


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class LoginCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
    }

    public function loginpageWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/login');

        $I->see('Login');
    }

    public function loginDeniedForWrongCredentials(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/login');
        $I->fillField(['name' => 'username'], 'test@leantime.io');
        $I->fillField(['name' => 'password'], 'WrongPassword');
        $I->click('Login');

        $I->see('Username or password incorrect!');
    }
}
