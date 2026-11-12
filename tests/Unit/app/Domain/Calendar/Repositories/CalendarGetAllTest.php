<?php

namespace Unit\app\Domain\Calendar\Repositories;

use Illuminate\Database\SQLiteConnection;
use Leantime\Domain\Calendar\Repositories\Calendar;
use Unit\TestCase;

/**
 * Calendar::getAll() returns one user's own events.
 *
 * It filtered on `userId >= :userId`, so a user's calendar also held the events of every user with
 * a higher id. That feed backs My Calendar, the dashboard calendar, CalDAV sync and the MCP
 * calendar tools. With no user id it returned every user's events.
 *
 * Runs against a real in-memory SQLite database.
 */
class CalendarGetAllTest extends TestCase
{
    private Calendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = new SQLiteConnection(new \PDO('sqlite::memory:'));
        $connection->statement('CREATE TABLE zp_calendar (
            id INTEGER PRIMARY KEY, userId INTEGER, dateFrom TEXT NULL, dateTo TEXT NULL,
            description TEXT, allDay TEXT
        )');
        $connection->table('zp_calendar')->insert([
            ['id' => 1, 'userId' => 2, 'dateFrom' => '2026-11-10 09:00:00', 'dateTo' => '2026-11-10 10:00:00', 'description' => 'Own event', 'allDay' => 'false'],
            ['id' => 2, 'userId' => 3, 'dateFrom' => '2026-11-10 11:00:00', 'dateTo' => '2026-11-10 12:00:00', 'description' => 'Higher id: doctor appointment', 'allDay' => 'false'],
            ['id' => 3, 'userId' => 1, 'dateFrom' => '2026-11-10 13:00:00', 'dateTo' => '2026-11-10 14:00:00', 'description' => 'Lower id', 'allDay' => 'false'],
            ['id' => 4, 'userId' => 2, 'dateFrom' => null, 'dateTo' => null, 'description' => 'No date', 'allDay' => 'false'],
        ]);

        $this->calendar = (new \ReflectionClass(Calendar::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Calendar::class, 'db'))->setValue($this->calendar, $connection);
    }

    public function test_returns_only_the_users_own_events(): void
    {
        $events = $this->calendar->getAll(2, null, null);

        $this->assertSame([1], array_map('intval', array_column($events, 'id')));
    }

    public function test_never_returns_events_of_users_with_a_higher_id(): void
    {
        $owners = array_unique(array_map('intval', array_column($this->calendar->getAll(1, null, null), 'userId')));

        $this->assertSame([1], array_values($owners));
    }

    public function test_without_a_user_returns_nothing(): void
    {
        $this->assertSame([], $this->calendar->getAll(null, null, null));
        $this->assertSame([], $this->calendar->getAll(0, null, null));
    }
}
