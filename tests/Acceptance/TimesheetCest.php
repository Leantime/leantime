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
        $I->fillField('//*[contains(@class, "rowMon")]//input[@class="hourCell"]', 1);
        $I->fillField('//*[contains(@class, "rowTue")]//input[@class="hourCell"]', 2);
        $I->click('//input[@name="saveTimeSheet"][@type="submit"]');
        $I->waitForElement('.growl', 60);
        $I->see('Timesheet successfully updated');

        $I->seeInField('//*[contains(@class, "rowMon")]//input[@class="hourCell"]', '1');
        $I->seeInField('//*[contains(@class, "rowTue")]//input[@class="hourCell"]', '2');
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
        $I->waitForElement('.growl', 60);
        $I->see('Timesheet successfully updated');

        // An page reload will trigger an "resend submission popup".
        $I->amOnPage('/timesheets/showMy');

        $I->waitForElementVisible('//*[contains(@class, "rowMon")]//input[@class="hourCell"]');
        $I->seeInField('//*[contains(@class, "rowMon")]//input[@class="hourCell"]', '1');
        $I->seeInField('//*[contains(@class, "rowTue")]//input[@class="hourCell"]', '2');
        $I->see('3', '#finalSum');
    }

    #[Group('timesheet')]
    #[Depends('createMyTimesheet')]
    public function changeTimezone(AcceptanceTester $I)
    {
        // Change timezone and see the correct timesheet.
        $this->changeUsersTimeZone($I, 'Europe/Copenhagen');

        // Check timesheet
        $I->amOnPage('/timesheets/showMy');
        $I->waitForElementVisible('//*[contains(@class, "rowMon")]//input[@class="hourCell"]');
        $I->wait(30);
        $I->seeInField('//*[contains(@class, "rowMon")]//input[@class="hourCell"]', '1');
        $I->seeInField('//*[contains(@class, "rowTue")]//input[@class="hourCell"]', '2');
        $I->see('3', '#finalSum');

        // Switch back.
        $this->changeUsersTimeZone($I);
    }

    #[Skip]
    #[Group('timesheet')]
    #[Depends('createMyTimesheet')]
    public function editTimesheet(AcceptanceTester $I)
    {
        // Edit timesheet through timesheets/showMyList
    }

    #[Skip]
    #[Group('timesheet')]
    #[Depends('createMyTimesheet')]
    public function showAllTimesheet(AcceptanceTester $I)
    {
        // /timesheets/showAll
    }

    #[Skip]
    #[Group('timesheet')]
    #[Depends('createMyTimesheet')]
    public function showAllEditsTimesheet(AcceptanceTester $I)
    {
        // /timesheets/showAll
        // make paid
        // make Invoiced
        // make MGR Approval
    }


    #[Skip]
    #[Group('timesheet')]
    #[Depends('createMyTimesheet')]
    public function deleteTimesheet(AcceptanceTester $I)
    {
        // Delete timesheet
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
        $I->waitForElement('.growl', 60);
        $I->see('Profile settings saved successfully');
    }
}
