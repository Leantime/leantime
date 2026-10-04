<?php

namespace Acceptance;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use PHPUnit\Framework\Assert;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Login;

/**
 * Closing a modal used to reload the whole page (#1809, #2969). A modal that changed nothing now
 * closes without any refresh, and one that changed data refreshes the page content in place.
 * A JS marker set on the page survives both, which it would not across a full page load.
 */
class ModalSoftReloadCest
{
    private int $ticketId = 0;

    public function _before(AcceptanceTester $I, Login $loginPage)
    {
        $loginPage->login('test@leantime.io', 'Test123456!');

        $projectId = (int) $I->grabFromDatabase('zp_projects', 'id', []);
        $userId = (int) $I->grabFromDatabase('zp_user', 'id', ['username' => 'test@leantime.io']);
        $this->ticketId = (int) $I->haveInDatabase('zp_tickets', [
            'headline' => 'Soft reload test',
            'type' => 'task',
            'projectId' => $projectId,
            'userId' => $userId,
            'status' => 3,
            'date' => '2026-10-01 00:00:00',
        ]);

        $I->amOnPage('/projects/changeCurrentProject/'.$projectId);
        $I->amOnPage('/tickets/showKanban');
        $I->waitForElement('#ticket_'.$this->ticketId, 60);
        $I->executeJS('window.__softReloadMarker = "kept";');
    }

    #[Group('ticket')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function closingAnUnchangedModalDoesNotReload(AcceptanceTester $I)
    {
        $I->wantTo('Close a To-Do modal without changes and stay on the same page instance');

        $this->openTicketModal($I);
        $I->executeJS('jQuery.nmTop().close();');
        $I->waitForElementNotVisible('.nyroModalCont', 30);
        $I->wait(2);

        Assert::assertSame('kept', $I->executeJS('return window.__softReloadMarker || "reloaded";'));
        $I->seeElement('#ticket_'.$this->ticketId);
    }

    #[Group('ticket')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function savingInAModalRefreshesTheBoardInPlace(AcceptanceTester $I)
    {
        $I->wantTo('Save a To-Do in its modal and see the board updated without a full page reload');

        $this->openTicketModal($I);
        $I->waitForElementVisible('.nyroModalCont .main-title-input', 60);
        $I->fillField('.nyroModalCont .main-title-input', 'Soft reload test renamed');
        $I->clickWithRetry('.nyroModalCont .saveTicketBtn');
        $I->waitForElement('.growl', 60);
        $I->executeJS('jQuery.nmTop() && jQuery.nmTop().close();');
        $I->waitForElementNotVisible('.nyroModalCont', 30);

        $I->waitForText('Soft reload test renamed', 30, '#ticket_'.$this->ticketId);
        Assert::assertSame('kept', $I->executeJS('return window.__softReloadMarker || "reloaded";'));
        // The board is interactive again after the in-place refresh.
        Assert::assertGreaterThan(0, (int) $I->executeJS('return document.querySelectorAll(".ui-sortable").length;'));
    }

    private function openTicketModal(AcceptanceTester $I): void
    {
        $I->executeJS('location.hash = "#/tickets/showTicket/'.$this->ticketId.'";');
        $I->waitForElementVisible('.nyroModalCont', 60);
        $I->wait(1);
    }
}
