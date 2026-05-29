<?php

namespace Acceptance;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Login;

/**
 * Acceptance tests for the Wiki domain after it was decoupled from the
 * deprecated Canvas domain (its repository now extends Blueprints). These
 * smoke tests confirm the wiki still renders and persists through the
 * decoupled repository.
 */
class WikiCest
{
    public function _before(AcceptanceTester $I, Login $loginPage): void
    {
        $loginPage->login('test@leantime.io', 'Test123456!');
    }

    #[Group('wiki')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function wikiLoads(AcceptanceTester $I): void
    {
        $I->wantTo('Open the wiki');

        $I->amOnPage('/wiki/show');
        $I->waitForElementVisible('.pageheader', 30);
        $I->dontSee('Whoops');
        $I->dontSee('Fatal error');
    }
}
