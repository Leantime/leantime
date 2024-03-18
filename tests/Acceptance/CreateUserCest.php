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
        $loginPage->login('test@leantime.io', 'test');
    }

    #[Group('user')]
    #[Depends('Acceptance\LoginCest:loginSuccessfully')]
    public function createAUser(AcceptanceTester $I): void
    {
        $I->wantTo('Create a user');
        $I->amOnPage('/users/showAll');
        $I->click('Add User');
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
        $I->click('Invite User');
        $I->waitForElement('.growl', 120);

        $I->seeInDatabase('zp_user', [
            'username' => 'john@doe.com'
        ]);
    }

    #[Group('user')]
    #[Depends('Acceptance\LoginCest:loginSuccessfully')]
    public function editAUser(AcceptanceTester $I): void
    {
        $I->wantTo('Edit a user');
        $I->amOnPage('/users/editUser/1/');
        $I->see('Edit User');
        $I->fillField(['name' => 'jobTitle'], 'Testing');
        $I->click('Save');
        $I->waitForElement('.growl', 120);
        $I->see('User edited successfully');
    }
}
