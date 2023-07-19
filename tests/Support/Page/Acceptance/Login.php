<?php

declare(strict_types=1);

namespace Tests\Support\Page\Acceptance;

use Codeception\Util\Fixtures;

class Login
{
    /**
     * @var \Tests\Support\AcceptanceTester;
     */
    protected $I;

    public function __construct(\Tests\Support\AcceptanceTester $I)
    {
        $this->I = $I;
    }


    public function login($username, $password)
    {
        if ($this->loadSessionShapshot('sid')) {
            return;
        }

        $this->I->amOnPage('/auth/login');
        $this->I->fillField(['name' => 'username'], $username);
        $this->I->fillField(['name' => 'password'], $password);
        $this->I->click('Login');

        $this->I->see('Welcome John');

        $this->saveSessionSnapshot('sid');
    }

    protected function loadSessionShapshot(string $name): bool
    {
        if (! Fixtures::exists($name)) {
            return false;
        }

        $this->I->setCookie($name, Fixtures::get($name));

        return true;
    }

    protected function saveSessionSnapshot(string $name): void
    {
        Fixtures::add($name, $this->I->grabCookie($name));
    }
}
