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

        // Tags (forms.tags): open the field, type a new tag, Enter adds it.
        $I->waitForElementClickable('.lt-tags .ss-main', 15);
        $I->clickWithRetry('.lt-tags .ss-main', 90);
        $I->waitForElementVisible('.ss-content.ss-open-below .ss-search input, .ss-content.ss-open-above .ss-search input', 15);
        $I->fillField('.ss-content.ss-open-below .ss-search input, .ss-content.ss-open-above .ss-search input', 'test-tag');
        $I->wait(1);
        $I->pressKey('.ss-content.ss-open-below .ss-search input, .ss-content.ss-open-above .ss-search input', \Facebook\WebDriver\WebDriverKeys::ENTER);
        $I->wait(1);
        $I->waitForElementClickable('[data-tiptap-editor] .ProseMirror', 120);
        $I->wait(2);
        $I->clickWithRetry('[data-tiptap-editor] .ProseMirror');
        $I->type('Test Description');
        $I->waitForElementClickable('.saveTicketBtn', 120);
        $I->clickWithRetry('.saveTicketBtn');
        $I->waitForElement('.growl', 120);
        // Match by headline, not a hardcoded auto-increment id: the id depends on how many
        // tickets earlier Cests created, so any change in suite composition (e.g. BearerApiCest
        // creating a ticket) would shift it. Headline keeps this order-independent.
        $I->seeInDatabase('zp_tickets', [
            'headline' => 'Test Ticket',
            'description like' => '%<p>Test Description</p>%',
            'tags' => 'test-tag',
        ]);
    }

    #[Group('ticket')]
    #[Depends('createTicket')]
    public function editTicket(AcceptanceTester $I)
    {
        $I->wantTo('Edit a ticket');

        // Resolve the ticket created by createTicket() by headline rather than a hardcoded id,
        // so this is independent of how many tickets other Cests created.
        $ticketId = $I->grabFromDatabase('zp_tickets', 'id', ['headline' => 'Test Ticket']);

        $I->amOnPage('/tickets/showKanban#/tickets/showTicket/'.$ticketId);
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
            'id' => $ticketId,
            'headline' => 'Test Ticket',
            'description like' => '%Test Description Edited%',
        ]);
    }
}
