<?php

namespace Acceptance;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Login;

class CreateUserCest
{
    public function _before(AcceptanceTester $I, Login $loginPage): void
    {
        $loginPage->login('test@leantime.io', 'Test123456!');
    }

    #[Group('user')]
    #[Depends('Acceptance\LoginCest:loginSuccessfully')]
    public function createAUser(AcceptanceTester $I): void
    {
        $I->wantTo('Create a user');
        $I->amOnPage('/users/showAll');
        $I->clickWithRetry('.userEditModal');
        $I->waitForElement('#firstname', 120);
        $I->fillField('#firstname', 'John');
        $I->fillField('#lastname', 'Doe');
        $I->selectOption('#role', 'Read Only');
        $I->selectOption('#client', 'Not assigned to a client');
        $I->fillField('#user', 'john@doe.com');
        $I->fillField('#phone', '1234567890');
        $I->fillField('#jobTitle', 'Testing');
        $I->fillField('#jobLevel', 'Testing');
        $I->fillField('#department', 'Testing');
        $I->clickWithRetry('#save');
        $I->waitForElement('.growl', 120);

        $I->seeInDatabase('zp_user', [
            'username' => 'john@doe.com',
        ]);
    }

    #[Group('user')]
    #[Depends('Acceptance\LoginCest:loginSuccessfully')]
    public function editAUser(AcceptanceTester $I): void
    {
        $I->wantTo('Edit a user');

        // Set CSRF token before making the request
        $I->setCSRFToken();
        $I->amOnPage('/users/editUser/1/');
        $I->waitForElement('.pagetitle', 120);
        $I->see('Edit User');
        $I->fillField(['name' => 'jobTitle'], 'Testing');
        $I->clickWithRetry('#save');
        $I->waitForElement('.growl', 120);
        $I->seeInSource('User edited successfully');
    }
}
