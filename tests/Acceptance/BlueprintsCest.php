<?php

namespace Acceptance;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Login;

/**
 * Acceptance tests for the consolidated Blueprints domain.
 *
 * Exercises the new /blueprints/{slug}/... routing (the dispatch bridge),
 * the YAML-driven board rendering, default-board creation, and the legacy
 * /{slug}canvas/... -> /blueprints/{slug}/... redirects.
 */
class BlueprintsCest
{
    public function _before(AcceptanceTester $I, Login $loginPage): void
    {
        $loginPage->login('test@leantime.io', 'Test123456!');
    }

    #[Group('blueprints')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function swotBoardRendersAndCreatesDefaultBoard(AcceptanceTester $I): void
    {
        $I->wantTo('Open a SWOT blueprint board and have a default board created');

        $I->amOnPage('/blueprints/swot/showCanvas');
        $I->waitForElementVisible('.pageheader', 30);
        $I->dontSee('Whoops');
        $I->dontSee('Fatal error');

        // Visiting the board auto-creates a default board for the current project.
        $I->seeInDatabase('zp_canvas', ['type' => 'swotcanvas']);
    }

    #[Group('blueprints')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function leanBoardRenders(AcceptanceTester $I): void
    {
        $I->wantTo('Open a Lean Canvas blueprint board');

        $I->amOnPage('/blueprints/lean/showCanvas');
        $I->waitForElementVisible('.pageheader', 30);
        $I->dontSee('Whoops');

        $I->seeInDatabase('zp_canvas', ['type' => 'leancanvas']);
    }

    #[Group('blueprints')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function legacyCanvasUrlRedirectsToBlueprints(AcceptanceTester $I): void
    {
        $I->wantTo('Be redirected from a legacy /swotcanvas URL to the blueprints route');

        $I->amOnPage('/swotcanvas/showCanvas');
        $I->waitForElementVisible('.pageheader', 30);
        $I->seeInCurrentUrl('/blueprints/swot/');
    }

    #[Group('blueprints')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function rendersEditableSwotBoxesWiredToBlueprintRoutes(AcceptanceTester $I): void
    {
        $I->wantTo('See the SWOT boxes render with add-item links pointing at the blueprints route');

        $I->amOnPage('/blueprints/swot/showCanvas');
        $I->waitForElementVisible('.pageheader', 30);

        // Each box (e.g. "strengths") renders an add-item affordance wired to the
        // consolidated /blueprints/{slug}/editCanvasItem route, confirming the
        // YAML-driven box rendering produced the right links.
        $I->seeElementInDOM('#swot_strengths');
        $I->seeElementInDOM('a[href*="/blueprints/swot/editCanvasItem?type=swot_strengths"]');
        $I->seeElementInDOM('a[href*="/blueprints/swot/editCanvasItem?type=swot_threats"]');
    }
}
