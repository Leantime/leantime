<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
use Codeception\Attribute\Depends;
use Tests\Support\Page\Acceptance\Login;

class LoginCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    #[Depends('Tests\Acceptance\InstallCest:createDBSuccessfully')]
    public function loginPageWorks(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/login');
        $I->see('Login');
    }

    #[Depends('Tests\Acceptance\InstallCest:createDBSuccessfully')]
    public function loginDeniedForWrongCredentials(AcceptanceTester $I)
    {
        $I->amOnPage('/auth/login');
        $I->fillField(['name' => 'username'], 'test@leantime.io');
        $I->fillField(['name' => 'password'], 'WrongPassword');
        $I->click('Login');
        $I->waitForElementVisible(".login-alert");

        $I->see('Username or password incorrect!');
    }

    #[Depends('Tests\Acceptance\InstallCest:createDBSuccessfully')]
    public function loginSuccessfully(AcceptanceTester $I, Login $loginPage)
    {
        $loginPage->login('test@leantime.io', 'test');
    }

    #[Depends('Tests\Acceptance\InstallCest::createdDBSuccessfully')]
    public function loginFormIsHidden(AcceptanceTester $I)
    {
        $_ENV['LEAN_DISABLE_LOGIN_FORM'] = true;
        
        $I->amOnPage('/auth/login');
        $I->dontSeeElementInDOM('div#login');
    }
}
