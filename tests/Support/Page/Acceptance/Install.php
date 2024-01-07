<?php

declare(strict_types=1);

namespace Tests\Support\Page\Acceptance;

use Codeception\Util\Fixtures;

class Install
{
    /**
     * @var \Tests\Support\AcceptanceTester;
     */
    protected $I;

    public function __construct(\Tests\Support\AcceptanceTester $I)
    {
        $this->I = $I;
    }

    public function install($email, $password, $firstname, $lastname, $company)
    {
        if (Fixtures::exists('installed')) {
            return;
        }

        $this->I->amOnPage('/install');
        $this->I->fillField(['name' => 'email'], $email);
        $this->I->fillField(['name' => 'password'], $password);
        $this->I->fillField(['name' => 'firstname'], $firstname);
        $this->I->fillField(['name' => 'lastname'], $lastname);
        $this->I->fillField(['name' => 'company'], $company);
        $this->I->click('Install');

        $this->I->waitForElementVisible(".login-alert");

        $this->I->see('The installation was successful');

        Fixtures::add('installed', true);
    }
}
