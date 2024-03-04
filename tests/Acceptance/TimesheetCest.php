<?php

namespace Acceptance;

use Codeception\Attribute\Depends;
use Codeception\Attribute\Skip;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Login;

class TimesheetCest
{
    public function _before(AcceptanceTester $I, Login $loginPage)
    {
        $loginPage->login('test@leantime.io', 'test');
    }

    #[Depends('Acceptance\TicketsCest:createTicket')]
    public function createMyTimesheets(AcceptanceTester $I)
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

    #[Skip]
    #[Depends('createMyTimesheets')]
    public function changeTimezone(AcceptanceTester $I)
    {
        // Change timezome and see correct timesheets.

        // Switch back.
    }

    #[Skip]
    #[Depends('createMyTimesheets')]
    public function editTimesheet(AcceptanceTester $I)
    {
        // Edit timesheet through timesheets/showMyList
    }

    #[Skip]
    #[Depends('createMyTimesheets')]
    public function showAllTimesheet(AcceptanceTester $I)
    {
        // /timesheets/showAll
    }

    #[Skip]
    #[Depends('createMyTimesheets')]
    public function showAllEditsTimesheet(AcceptanceTester $I)
    {
        // /timesheets/showAll
        // make paid
        // make Invoiced
        // make MGR Approval
    }


    #[Skip]
    #[Depends('createMyTimesheets')]
    public function deleteTimesheet(AcceptanceTester $I)
    {
        // Delete timesheet
    }
}
