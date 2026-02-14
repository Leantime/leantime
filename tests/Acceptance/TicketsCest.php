<?php

namespace Acceptance;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Login;

class TicketsCest
{
    public function _before(AcceptanceTester $I, Login $loginPage)
    {
        $loginPage->login('test@leantime.io', 'Test123456!');
    }

    #[Group('timesheet', 'ticket')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function createTicket(AcceptanceTester $I)
    {
        $I->wantTo('Create a ticket');

        $I->amOnPage('/tickets/showKanban#/tickets/newTicket');
        $I->waitForElementVisible('.main-title-input', 120);
        $I->fillField(['class' => 'main-title-input'], 'Test Ticket');

        $I->waitForElementClickable('.tagsinput', 15);
        $I->clickWithRetry('.tagsinput', 90);
        $I->wait(2);
        $I->type('test-tag,');
        $I->waitForElementClickable('[data-tiptap-editor] .ProseMirror', 120);
        $I->wait(2);
        $I->clickWithRetry('[data-tiptap-editor] .ProseMirror');
        $I->type('Test Description');
        $I->waitForElementClickable('.saveTicketBtn', 120);
        $I->clickWithRetry('.saveTicketBtn');
        $I->waitForElement('.growl', 120);
        $I->seeInDatabase('zp_tickets', [
            'id' => 10,
            'headline' => 'Test Ticket',
            'description like' => '%<p>Test Description</p>%',
        ]);
    }

    #[Group('ticket')]
    #[Depends('createTicket')]
    public function editTicket(AcceptanceTester $I)
    {
        $I->wantTo('Edit a ticket');

        $I->amOnPage('/tickets/showKanban#/tickets/showTicket/10');
        // Currently (and only in tests) the editor is not loaded when clicked on less the page is reloaded first.
        $I->reloadPage();
        $I->waitForElementVisible('.main-title-input', 120);
        $I->waitForElementClickable('[data-tiptap-editor] .ProseMirror', 120);
        $I->wait(2);
        $I->clickWithRetry('[data-tiptap-editor] .ProseMirror');
        $I->type('Test Description Edited');
        $I->waitForElementClickable('.saveTicketBtn', 120);
        $I->clickWithRetry('.saveTicketBtn');
        $I->waitForElement('.growl', 120);
        $I->wait(2);
        $I->seeInDatabase('zp_tickets', [
            'id' => 10,
            'headline' => 'Test Ticket',
            'description like' => '%Test Description Edited%',
        ]);
    }
}
