<?php

namespace Acceptance;

use Carbon\CarbonImmutable;
use Codeception\Attribute\Depends;
use Codeception\Attribute\Group;
use PHPUnit\Framework\Assert;
use Tests\Support\AcceptanceTester;
use Tests\Support\Page\Acceptance\Login;

/**
 * Dropping or moving a To-Do on a calendar must store the time the user dropped it on.
 *
 * Calendars lay out their grid in the user's timezone (usersettings.timezone), which differs from the
 * browser's timezone whenever the user setting and the machine disagree (the test browser runs in UTC,
 * the test install defaults to America/Los_Angeles). The ticket dates used to be read from the JS Date
 * in the browser's zone, so every drop was shifted by the difference and the event jumped hours away.
 */
class CalendarCest
{
    public function _before(AcceptanceTester $I, Login $loginPage)
    {
        $loginPage->login('test@leantime.io', 'Test123456!');
    }

    #[Group('calendar')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function droppedEventKeepsTheCalendarWallClockTime(AcceptanceTester $I)
    {
        $I->wantTo('Convert a calendar event to ticket dates in the calendar timezone, not the browser timezone');

        $I->amOnPage('/dashboard/home');
        $I->waitForJS('return typeof FullCalendar !== "undefined" && typeof leantime.calendarController !== "undefined";', 60);

        // UTC+14: never the browser's zone, so a conversion that used the browser zone shows a different time.
        $values = $I->executeJS(<<<'JS'
            var el = document.createElement('div');
            document.body.appendChild(el);
            var calendar = new FullCalendar.Calendar(el, { timeZone: 'Pacific/Kiritimati', initialView: 'timeGridDay', initialDate: '2026-10-04' });
            calendar.render();
            var event = calendar.addEvent({ id: 'probe', title: 'probe', start: '2026-10-04T08:00:00' });
            var values = leantime.calendarController.buildTicketDateValues(event, 'yyyy-MM-dd', 'HH:mm');
            calendar.destroy();
            el.remove();
            return values;
        JS);

        // assertEquals: WebDriver doesn't preserve the JS object's key order.
        Assert::assertEquals(
            ['editFrom' => '2026-10-04', 'timeFrom' => '08:00', 'editTo' => '2026-10-04', 'timeTo' => '09:00'],
            $values
        );
    }

    #[Group('calendar')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function droppedEventStaysOnTheCalendar(AcceptanceTester $I)
    {
        $I->wantTo('Keep a dropped to-do on the calendar and remove only the stale copy of the same ticket');

        $I->amOnPage('/dashboard/home');
        $I->waitForJS('return typeof FullCalendar !== "undefined" && typeof leantime.calendarController !== "undefined";', 60);

        $remaining = $I->executeJS(<<<'JS'
            var el = document.createElement('div');
            document.body.appendChild(el);
            var calendar = new FullCalendar.Calendar(el, { initialView: 'timeGridDay', initialDate: '2026-10-04' });
            calendar.render();
            calendar.addEvent({ id: '42', title: 'stale copy', start: '2026-10-04T08:00:00', extendedProps: { enitityType: 'ticket', enitityId: '42' } });
            calendar.addEvent({ id: '7', title: 'other ticket', start: '2026-10-04T09:00:00', extendedProps: { enitityType: 'ticket', enitityId: '7' } });
            var dropped = calendar.addEvent({ id: '42', title: 'dropped', start: '2026-10-04T13:00:00', extendedProps: { enitityType: 'ticket', enitityId: '42' } });
            leantime.calendarController.removeStaleTicketCopies(calendar, dropped);
            var titles = calendar.getEvents().map(function (event) { return event.title; }).sort();
            calendar.destroy();
            el.remove();
            return titles;
        JS);

        Assert::assertSame(['dropped', 'other ticket'], $remaining);
    }

    #[Group('calendar', 'ticket')]
    #[Depends('Acceptance\InstallCest:createDBSuccessfully')]
    public function droppedTicketIsStoredAtTheDroppedTime(AcceptanceTester $I)
    {
        $I->wantTo('Store a ticket dropped on the calendar at the time it was dropped on');

        $projectId = $I->grabFromDatabase('zp_projects', 'id', []);
        $userId = $I->grabFromDatabase('zp_user', 'id', ['username' => 'test@leantime.io']);
        $ticketId = $I->haveInDatabase('zp_tickets', [
            'headline' => 'Calendar drop timezone test',
            'type' => 'task',
            'projectId' => $projectId,
            'userId' => $userId,
            'status' => 3,
            'date' => '2026-10-01 00:00:00',
        ]);

        $I->amOnPage('/dashboard/home');
        $I->waitForJS('return typeof FullCalendar !== "undefined" && typeof leantime.rpc === "function";', 60);

        // Lay the calendar out exactly like the app does (in the user's timezone from the page), drop
        // the ticket at 10:00 and save it through the same RPC call the calendar uses.
        $result = $I->executeAsyncJS(<<<'JS'
            var done = arguments[arguments.length - 1];
            var ticketId = arguments[0];
            var timeZone = leantime.i18n.__('usersettings.timezone');
            var el = document.createElement('div');
            document.body.appendChild(el);
            var calendar = new FullCalendar.Calendar(el, { timeZone: timeZone, initialView: 'timeGridDay', initialDate: '2026-10-05' });
            calendar.render();
            var event = calendar.addEvent({ id: String(ticketId), title: 'drop', start: '2026-10-05T10:00:00', end: '2026-10-05T11:00:00' });
            var values = leantime.calendarController.buildTicketDateValues(
                event,
                leantime.dateHelper.getFormatFromSettings('dateformat', 'luxon'),
                leantime.dateHelper.getFormatFromSettings('timeformat', 'luxon')
            );
            calendar.destroy();
            el.remove();
            leantime.rpc('Tickets.Tickets.patchTicket', { id: String(ticketId), values: values })
                .then(function (saved) { done({ saved: saved, timeZone: timeZone }); })
                .catch(function (error) { done({ saved: false, error: String(error), timeZone: timeZone }); });
        JS, [$ticketId]);

        Assert::assertTrue($result['saved'] === true, 'patchTicket did not save: '.json_encode($result));
        Assert::assertNotSame('local', $result['timeZone'], 'Calendars must use the user timezone, not the browser zone');

        $expectedFromUtc = CarbonImmutable::parse('2026-10-05 10:00:00', $result['timeZone'])->utc()->format('Y-m-d H:i:s');
        $expectedToUtc = CarbonImmutable::parse('2026-10-05 11:00:00', $result['timeZone'])->utc()->format('Y-m-d H:i:s');

        $I->seeInDatabase('zp_tickets', ['id' => $ticketId, 'editFrom' => $expectedFromUtc, 'editTo' => $expectedToUtc]);
    }
}
