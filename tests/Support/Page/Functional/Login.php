<?php

declare(strict_types=1);

namespace Tests\Support\Page\Functional;

use Tests\Support\Page\Functional\Install;
use Codeception\Util\Fixtures;

class Login
{
    /**
     * @var \Tests\Support\FunctionalTester;
     */
    protected $I;

    public function __construct(\Tests\Support\FunctionalTester $I, Install $installPage)
    {
        $this->I = $I;
        $this->installPage = $installPage;
    }


    public function login($username, $password)
    {
        if ($this->loadSessionShapshot('sid')) {
            return;
        }

        if (! Fixtures::exists('installed')) {
            $this->installPage->install(
                'test@leantime.io',
                'test',
                'John',
                'Smith',
                'Smith & Co'
            );
        }

        $this->I->amOnPage('/auth/login');
        $this->I->fillField(['name' => 'username'], $username);
        $this->I->fillField(['name' => 'password'], $password);
        $this->I->click('Login');
        $this->I->waitForElementVisible('.articleHeadline', 30);
        echo $this->I->grabPageSource();
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
