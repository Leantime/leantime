<?php

namespace Acceptance;

use Codeception\Attribute\Depends;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Login;

class LoginCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function loginPageWorks(AcceptanceTester $I): void
    {
        $I->amOnPage('/auth/login');
        $I->see('Login');
    }

    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function loginDeniedForWrongCredentials(AcceptanceTester $I): void
    {
        $I->amOnPage('/auth/login');
        $I->fillField(['name' => 'username'], 'test@leantime.io');
        $I->fillField(['name' => 'password'], 'WrongPassword');
        $I->click('Login');
        $I->waitForElementVisible(".login-alert");

        $I->see('Username or password incorrect!');
    }

    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function loginSuccessfully(AcceptanceTester $I, Login $loginPage): void
    {
        $loginPage->login('test@leantime.io', 'test');
    }

    #[Depends('Acceptance\InstallCest::createdDBSuccessfully')]
    public function loginFormIsHidden(AcceptanceTester $I): void
    {
        $_ENV['LEAN_DISABLE_LOGIN_FORM'] = true;

        $I->amOnPage('/auth/login');
        $I->dontSeeElementInDOM('div#login');
    }
}
