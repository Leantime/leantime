<?php

namespace Acceptance;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Login;

/**
 * Acceptance tests for the Goals (Goalcanvas) domain after it was decoupled
 * from the deprecated Canvas domain (its repository now extends Blueprints and
 * its controllers no longer extend the Canvas controllers). These smoke tests
 * confirm the goal pages still render end-to-end through the decoupled stack.
 */
class GoalsCest
{
    public function _before(AcceptanceTester $I, Login $loginPage): void
    {
        $loginPage->login('test@leantime.io', 'Test123456!');
    }

    #[Group('goals')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function goalDashboardLoads(AcceptanceTester $I): void
    {
        $I->wantTo('Open the goals dashboard');

        $I->amOnPage('/goalcanvas/dashboard');
        $I->waitForElementVisible('.pageheader', 30);
        $I->dontSee('Whoops');
        $I->dontSee('Fatal error');
    }

    #[Group('goals')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function goalCanvasLoads(AcceptanceTester $I): void
    {
        $I->wantTo('Open the goals canvas board');

        $I->amOnPage('/goalcanvas/showCanvas');
        $I->waitForElementVisible('.pageheader', 30);
        $I->dontSee('Whoops');
        $I->dontSee('Fatal error');
    }
}
