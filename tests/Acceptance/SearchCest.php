<?php

namespace Acceptance;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Login;

/**
 * Global search: the header bar's quick results and the full results page.
 *
 * Relies on the onboarding data the install creates for the first user ("My Project" with
 * its "Getting Started" to-dos), so nothing has to be seeded here.
 */
class SearchCest
{
    public function _before(AcceptanceTester $I, Login $loginPage): void
    {
        $loginPage->login('test@leantime.io', 'Test123456!');
    }

    #[Group('search')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function quickSearchShowsGroupedHits(AcceptanceTester $I): void
    {
        $I->wantTo('Type in the header search and see grouped quick results');

        $I->amOnPage('/dashboard/home');
        $I->waitForElementVisible('#globalSearchInput', 30);

        $I->fillField('#globalSearchInput', 'Getting Started');
        $I->waitForElementVisible('#globalSearchResults .searchResult', 15);

        $I->see('Getting Started', '#globalSearchResults');
        $I->seeElement('#globalSearchResults .globalSearch__footer');
    }

    #[Group('search')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function resultsPageLoadsEveryPanel(AcceptanceTester $I): void
    {
        $I->wantTo('Open the full results page and see per-type panels load');

        $I->amOnPage('/search/show?q=Getting+Started');
        $I->waitForElementVisible('.searchPage__panels', 30);
        $I->waitForElementVisible('#searchPanel-tickets', 30);
        $I->waitForElementVisible('.searchPage__panel .searchPage__list .searchResult', 30);

        $I->see('Getting Started', '.searchPage__panels');
        $I->dontSee('Whoops');
        $I->dontSee('Fatal error');
    }

    #[Group('search')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function resultsPageHonoursTypeFilter(AcceptanceTester $I): void
    {
        $I->wantTo('Restrict the results page to projects only');

        $I->amOnPage('/search/show?q=My+Project&types[]=projects');
        $I->waitForElementVisible('#searchPanel-projects', 30);
        $I->dontSeeElement('#searchPanel-tickets');

        $I->waitForElementVisible('.searchPage__panel .searchResult', 30);
        $I->see('My Project', '.searchPage__panels');
    }
}
