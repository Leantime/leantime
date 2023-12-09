<?php

namespace Functional;

use Codeception\Attribute\Depends;
use Tests\Support\FunctionalTester;
use Tests\Support\Page\Functional\Login;

class LoginCest
{
    public function _before(FunctionalTester $I)
    {
    }

    #[Depends('Tests\Functional\InstallCest:createDBSuccessfully')]
    public function loginPageWorks(FunctionalTester $I)
    {
        $I->amOnPage('/auth/login');
        $I->see('Login');
    }

    #[Depends('Tests\Functional\InstallCest:createDBSuccessfully')]
    public function loginDeniedForWrongCredentials(FunctionalTester $I)
    {
        $I->amOnPage('/auth/login');
        $I->fillField(['name' => 'username'], 'test@leantime.io');
        $I->fillField(['name' => 'password'], 'WrongPassword');
        $I->click('Login');
        $I->waitForElementVisible(".login-alert");

        $I->see('Username or password incorrect!');
    }

    #[Depends('Tests\Functional\InstallCest:createDBSuccessfully')]
    public function loginSuccessfully(FunctionalTester $I, Login $loginPage)
    {
        $loginPage->login('test@leantime.io', 'test');
    }

    #[Depends('Tests\Functional\InstallCest::createdDBSuccessfully')]
    public function loginFormIsHidden(FunctionalTester $I)
    {
        $_ENV['LEAN_DISABLE_LOGIN_FORM'] = true;

        $I->amOnPage('/auth/login');
        $I->dontSeeElementInDOM('div#login');
    }
}
