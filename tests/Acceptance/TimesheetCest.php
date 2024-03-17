<?php

namespace Acceptance;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use Codeception\Attribute\Skip;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Login;

class TimesheetCest
{
    public function _before(AcceptanceTester $I, Login $loginPage)
    {
        $loginPage->login('test@leantime.io', 'test');
    }

    /**
     * Create timesheet on my page.
     */
    #[Group('timesheet')]
    #[Depends('Acceptance\TicketsCest:createTicket')]
    public function createMyTimesheet(AcceptanceTester $I): void
    {
        $I->wantTo('Add hours to tickets on my timesheet');

        $I->amOnPage('/timesheets/showMy');
        // Select project.
        $I->waitForElementNotVisible(".project-select", 120);
        $I->click('#projectSelect .chosen-single');
        $I->waitForElementVisible('.chosen-drop', 120);
        $I->click('#projectSelect .chosen-results .active-result');

        // Select ticket.
        $I->waitForElementNotVisible(".ticket-select", 120);
        $I->click('#ticketSelect .chosen-single');
        $I->waitForElementVisible('.chosen-drop', 120);
        $I->click('#ticketSelect .chosen-results .active-result');

        // Select type.
        $I->waitForElementVisible(".kind-select", 120);
        $I->selectOption('.kind-select', 'General, billable');

        // Set hours in active
        $I->fillField('//*[contains(@class, "rowday1")]//input[@class="hourCell"]', 1);
        $I->fillField('//*[contains(@class, "rowday2")]//input[@class="hourCell"]', 2);
        $I->click('//input[@name="saveTimeSheet"][@type="submit"]');
        $I->waitForElement('.growl', 60);

        $I->seeInField('//*[contains(@class, "rowday1")]//input[@class="hourCell"]', '1');
        $I->seeInField('//*[contains(@class, "rowday2")]//input[@class="hourCell"]', '2');
        $I->see('3', '#finalSum');
        $I->seeInDatabase('zp_timesheets', [
            'id' => 1,
            'hours' => 1,
            'kind' => 'GENERAL_BILLABLE'
        ]);
        $I->seeInDatabase('zp_timesheets', [
            'id' => 2,
            'hours' => 2,
            'kind' => 'GENERAL_BILLABLE'
        ]);
    }

    /**
     * Save the timesheet once more to ensure number do not change.
     *
     * If the cell IDs are not correct, this will break the registrations.
     */
    #[Group('timesheet')]
    #[Depends('createMyTimesheet')]
    public function saveOnceMoreTimezone(AcceptanceTester $I): void
    {
        $I->wantTo('Save the timesheet once more to ensure number do not change');

        $I->amOnPage('/timesheets/showMy');
        $I->click('//input[@name="saveTimeSheet"][@type="submit"]');
        $I->waitForElement('.growl', 120);
        $I->see('Timesheet saved successfully');

        // An page reload will trigger an "resend submission popup".
        $I->amOnPage('/timesheets/showMy');

        $I->waitForElementVisible('//*[contains(@class, "rowday1")]//input[@class="hourCell"]');
        $I->seeInField('//*[contains(@class, "rowday1")]//input[@class="hourCell"]', '1');
        $I->seeInField('//*[contains(@class, "rowday2")]//input[@class="hourCell"]', '2');
        $I->see('3', '#finalSum');
    }

    #[Group('timesheet')]
    #[Depends('createMyTimesheet')]
    public function changeTimezone(AcceptanceTester $I): void
    {
        $I->wantTo('Change timezone and see the correct timesheet');

        // Change timezone and see the correct timesheet.
        $this->changeUsersTimeZone($I, 'Europe/Copenhagen');

        // Check timesheet
        $I->amOnPage('/timesheets/showMy');
        $I->waitForElementVisible('//*[contains(@class, "rowday1")]//input[@class="hourCell"]');
        $I->seeInField('//*[contains(@class, "rowday1")]//input[@class="hourCell"]', '1');
        $I->seeInField('//*[contains(@class, "rowday2")]//input[@class="hourCell"]', '2');
        $I->see('3', '#finalSum');

        // Switch back.
        $this->changeUsersTimeZone($I);
    }

    #[Group('timesheet')]
    #[Depends('createMyTimesheet')]
    public function editTimesheet(AcceptanceTester $I): void
    {
        $I->wantTo('Edit timesheet');

        $I->amOnPage('/timesheets/showMyList');
        $I->waitForElementVisible('#allTimesheetsTable');
        $I->see('#1 - Edit');

        $I->click('#1 - Edit');
        $I->waitForElementVisible('#hours');
        $I->fillField('#hours', 2);
        $I->click('.stdformbutton .button');
        $I->waitForElement('.growl', 120);

        $I->seeInDatabase('zp_timesheets', [
            'id' => '1',
            'hours' => 2,
        ]);


        // Close modal.
        $I->waitForElementVisible('.nyroModalClose');
        $I->click('.nyroModalClose');

        // Check that data have been updated.
        $I->wait(5);
        $I->waitForElementVisible('#allTimesheetsTable');
        $I->see('2', '//*//tr[@class="odd"]//td', '2');
        $I->see('2', '//*//tr[@class="odd"]//td', '-2');
    }

    #[Group('timesheet')]
    #[Depends('createMyTimesheet', 'editTimesheet')]
    public function logTimeOnTicketTimesheet(AcceptanceTester $I): void
    {
        $I->wantTo('Open ticket and add time');

        $I->amOnPage('/#/tickets/showTicket/10');
        $I->waitForElementVisible('#ui-id-8');
        $I->click('#ui-id-8');
        $I->waitForElementVisible('#hours');
        $I->fillField('#hours', 4);

        $I->click('.formModal .button');
        $I->wait(1);

        // Go and see if the total is correct.
        $I->amOnPage('/timesheets/showMy');
        $I->waitForElementVisible('#finalSum');
        $I->see('8', '#finalSum');
    }

    #[Group('timesheet')]
    #[Depends('createMyTimesheet', 'editTimesheet', 'logTimeOnTicketTimesheet')]
    public function showAllTimesheet(AcceptanceTester $I): void
    {
        $I->wantTo('Show all timesheet list');
        $I->amOnPage('/timesheets/showAll');

        $I->waitForElementVisible('#allTimesheetsTable');
        $I->see('2', '//*//tr[@class="odd"]//td', '2');
        $I->see('2', '//*//tr[@class="odd"]//td', '-2');
    }

    #[Group('timesheet')]
    #[Depends('createMyTimesheet')]
    public function showAllEditsTimesheet(AcceptanceTester $I): void
    {
        $I->wantTo('Show all timesheet list');
        $I->amOnPage('/timesheets/showAll');
        $I->waitForElementVisible('#allTimesheetsTable');

        // Maker paid
        $I->checkOption('//*//input[@id="checkAllPaid"]');
        $I->click('#allTimesheetsTable_wrapper .button');
        $I->waitForElementVisible('#allTimesheetsTable_wrapper');
        $I->cantSeeElement('//*//input[@class="paid"]');

        // Make Invoiced
        $I->checkOption('//*/input[@id="checkAllEmpl"]');
        $I->click('#allTimesheetsTable_wrapper .button');
        $I->waitForElementVisible('#allTimesheetsTable_wrapper');
        $I->cantSeeElement('//*//input[@class="invoicedEmpl"]');

        // Make MGR Approval
        $I->checkOption('//*//input[@id="checkAllComp"]');
        $I->click('#allTimesheetsTable_wrapper .button');
        $I->waitForElementVisible('#allTimesheetsTable_wrapper');
        $I->cantSeeElement('//*//input[@class="invoicedComp"]');
    }

    #[Group('timesheet')]
    #[Depends('createMyTimesheet')]
    public function deleteTimesheet(AcceptanceTester $I): void
    {
        $I->wantTo('Delete timesheet');

        $I->amOnPage('/timesheets/showMyList');
        $I->waitForElementVisible('#allTimesheetsTable');
        $I->see('#1 - Edit');

        $I->click('#1 - Edit');
        $I->waitForElementVisible('.delete');
        $I->click('.stdformbutton .delete');

        $I->wait(1);
        $I->see('Should the timesheet really be deleted?');

        $I->click('.nyroModalLink .button');
        $I->waitForElement('.growl', 60);
        $I->wait(1);
        $I->see('Time entry deleted successfully');

        $I->waitForElementVisible('#allTimesheetsTable');
        $I->cantSee('#1 - Edit');
    }

    /**
     * Change the timezone for the logged-in user.
     *
     * @param AcceptanceTester $I The AcceptanceTester object representing the test runner.
     * @param string           $timezone The timezone to be set. Defaults to 'America/Los_Angeles'.
     *
     * @return void
     */
    private function changeUsersTimeZone(AcceptanceTester $I, string $timezone = 'America/Los_Angeles'): void
    {
        $I->amOnPage('/users/editOwn#settings');
        $I->waitForElementVisible('#timezone');
        $I->selectOption('#timezone', $timezone);
        $I->click('#saveSettings');
        $I->waitForElement('.growl', 120);

        $I->seeInDatabase('zp_settings', [
            'key' => 'usersettings.1.timezone',
            'value' => $timezone,
        ]);

    }
}
