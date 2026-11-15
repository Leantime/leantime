<?php

namespace Unit\app\Domain\Calendar\Repositories;

use Illuminate\Database\SQLiteConnection;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Language;
use Leantime\Domain\Calendar\Repositories\Calendar;
use Leantime\Domain\Tickets\Repositories\Tickets;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Unit\TestCase;

/**
 * The iCal feed (#4018) is fetched by calendar apps without a session. Its To-Dos came from the
 * session-scoped Tickets service, so without a session they were scoped to nobody: the feed lost
 * every To-Do, and a user with no personal events got "Calendar could not be retrieved" (404).
 * The feed now scopes the To-Dos to the feed OWNER's project access.
 *
 * Runs against a real in-memory SQLite database.
 */
class CalendarFeedWithoutSessionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const OWNER_ID = 5;

    private const OTHER_USER_ID = 6;

    private Calendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        if (! defined('BASE_URL')) {
            define('BASE_URL', 'http://localhost');
        }

        session()->forget('userdata');

        $connection = new SQLiteConnection(new \PDO('sqlite::memory:'));
        $this->createSchema($connection);
        $this->seedFeedData($connection);

        $language = Mockery::mock(Language::class);
        $language->shouldReceive('__')->andReturnUsing(fn ($key) => $key);

        $tickets = (new \ReflectionClass(Tickets::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Tickets::class, 'connection'))->setValue($tickets, $connection);
        (new \ReflectionProperty(Tickets::class, 'dbHelper'))->setValue($tickets, new DatabaseHelper($connection));
        (new \ReflectionProperty(Tickets::class, 'language'))->setValue($tickets, $language);
        app()->instance(Tickets::class, $tickets);

        $this->calendar = (new \ReflectionClass(Calendar::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Calendar::class, 'db'))->setValue($this->calendar, $connection);
        (new \ReflectionProperty(Calendar::class, 'language'))->setValue($this->calendar, $language);
    }

    protected function tearDown(): void
    {
        app()->forgetInstance(Tickets::class);
        session()->forget('userdata');

        parent::tearDown();
    }

    public function test_without_a_session_the_feed_holds_the_owners_todos(): void
    {
        $events = $this->calendar->getCalendar(self::OWNER_ID, 0);

        $this->assertSame([10], $this->ticketIds($events));
        $this->assertSame([1], $this->personalEventIds($events));
    }

    public function test_owner_with_only_todos_gets_a_non_empty_feed_without_a_session(): void
    {
        $events = $this->calendar->getCalendar(self::OTHER_USER_ID, 0);

        // User 6 has no personal events; before the fix this was [] and the feed 404'd.
        $this->assertSame([12], $this->ticketIds($events));
    }

    public function test_feed_is_scoped_to_the_owner_not_to_a_logged_in_visitor(): void
    {
        // A different user, who can see project 2, opens the owner's feed URL in their browser.
        session(['userdata' => ['id' => self::OTHER_USER_ID, 'clientId' => 0, 'role' => 'editor']]);

        $events = $this->calendar->getCalendar(self::OWNER_ID, 0);

        // Ticket 11 is assigned to the owner but sits in a project the owner can't access.
        $this->assertSame([10], $this->ticketIds($events));
    }

    public function test_client_visible_projects_use_the_owners_client(): void
    {
        $this->assertNotContains(13, $this->ticketIds($this->calendar->getCalendar(self::OWNER_ID, 0)));
        $this->assertContains(13, $this->ticketIds($this->calendar->getCalendar(self::OWNER_ID, 3)));
    }

    public function test_session_scoped_ticket_search_finds_nothing_without_a_session(): void
    {
        // The root cause: the ticket search scopes to the SESSION user, which the feed doesn't have.
        $sessionScoped = app(Tickets::class)->getAllBySearchCriteria(
            ['currentProject' => '', 'users' => (string) self::OWNER_ID, 'status' => 'all', 'sprint' => ''],
            'duedate',
            null,
            false
        );

        $this->assertSame([], $sessionScoped);
    }

    public function test_explicit_scope_user_replaces_the_session_user(): void
    {
        session(['userdata' => ['id' => self::OTHER_USER_ID, 'clientId' => 0, 'role' => 'editor']]);

        $ownerScoped = app(Tickets::class)->getAllBySearchCriteria(
            searchCriteria: ['currentProject' => '', 'status' => 'all', 'sprint' => ''],
            includeCounts: false,
            scopeUserId: self::OWNER_ID,
            scopeClientId: 0,
        );

        $projectIds = array_values(array_unique(array_map('intval', array_column($ownerScoped, 'projectId'))));
        $this->assertSame([1], $projectIds);
    }

    /**
     * @param  array<int, array<string, mixed>>  $events
     * @return int[]
     */
    private function ticketIds(array $events): array
    {
        $ticketEvents = array_filter($events, fn ($event) => $event['eventType'] === 'ticket');
        $ids = array_values(array_unique(array_map('intval', array_column($ticketEvents, 'id'))));
        sort($ids);

        return $ids;
    }

    /**
     * @param  array<int, array<string, mixed>>  $events
     * @return int[]
     */
    private function personalEventIds(array $events): array
    {
        $personalEvents = array_filter($events, fn ($event) => $event['eventType'] === 'calendar');

        return array_values(array_map('intval', array_column($personalEvents, 'id')));
    }

    private function createSchema(SQLiteConnection $connection): void
    {
        $connection->statement('CREATE TABLE zp_tickets (
            id INTEGER PRIMARY KEY, headline TEXT, type TEXT, description TEXT, projectId INTEGER,
            status INTEGER, date TEXT, dateToFinish TEXT, sprint INTEGER, storypoints INTEGER,
            priority TEXT, hourRemaining INTEGER, planHours INTEGER, tags TEXT, editorId TEXT,
            editFrom TEXT, editTo TEXT, dependingTicketId INTEGER, milestoneid INTEGER,
            sortindex INTEGER, modified TEXT, userId INTEGER
        )');
        $connection->statement('CREATE TABLE zp_projects (
            id INTEGER PRIMARY KEY, name TEXT, clientId INTEGER, psettings TEXT, state INTEGER
        )');
        $connection->statement('CREATE TABLE zp_clients (id INTEGER PRIMARY KEY, name TEXT)');
        $connection->statement('CREATE TABLE zp_user (
            id INTEGER PRIMARY KEY, firstname TEXT, lastname TEXT, profileId TEXT, role INTEGER, clientId INTEGER
        )');
        $connection->statement('CREATE TABLE zp_sprints (id INTEGER PRIMARY KEY, name TEXT)');
        $connection->statement('CREATE TABLE zp_timesheets (id INTEGER PRIMARY KEY, ticketId INTEGER, hours REAL)');
        $connection->statement('CREATE TABLE zp_relationuserproject (id INTEGER PRIMARY KEY, userId INTEGER, projectId INTEGER)');
        $connection->statement('CREATE TABLE zp_entity_relationship (
            id INTEGER PRIMARY KEY, entityA INTEGER, entityAType TEXT, entityB INTEGER,
            entityBType TEXT, relationship TEXT
        )');
        $connection->statement('CREATE TABLE zp_settings (key TEXT PRIMARY KEY, value TEXT)');
        $connection->statement('CREATE TABLE zp_calendar (
            id INTEGER PRIMARY KEY, userId INTEGER, dateFrom TEXT NULL, dateTo TEXT NULL,
            description TEXT, allDay TEXT
        )');
    }

    private function seedFeedData(SQLiteConnection $connection): void
    {
        $connection->table('zp_user')->insert([
            ['id' => self::OWNER_ID, 'firstname' => 'Owner', 'lastname' => 'User', 'profileId' => '', 'role' => 20, 'clientId' => 0],
            ['id' => self::OTHER_USER_ID, 'firstname' => 'Other', 'lastname' => 'User', 'profileId' => '', 'role' => 20, 'clientId' => 0],
        ]);

        $connection->table('zp_projects')->insert([
            ['id' => 1, 'name' => 'Owner project', 'clientId' => 0, 'psettings' => 'restricted', 'state' => 0],
            ['id' => 2, 'name' => 'Project the owner cannot access', 'clientId' => 0, 'psettings' => 'restricted', 'state' => 0],
            ['id' => 3, 'name' => 'Client project', 'clientId' => 3, 'psettings' => 'clients', 'state' => 0],
        ]);

        $connection->table('zp_relationuserproject')->insert([
            ['userId' => self::OWNER_ID, 'projectId' => 1],
            ['userId' => self::OTHER_USER_ID, 'projectId' => 1],
            ['userId' => self::OTHER_USER_ID, 'projectId' => 2],
        ]);

        $ticket = [
            'type' => 'task', 'description' => '', 'status' => 3, 'date' => '2026-11-01 00:00:00',
            'sprint' => 0, 'storypoints' => 0, 'priority' => '', 'hourRemaining' => 0, 'planHours' => 0,
            'tags' => '', 'editFrom' => null, 'editTo' => null, 'dependingTicketId' => 0,
            'milestoneid' => 0, 'sortindex' => 0, 'modified' => '2026-11-01 00:00:00', 'userId' => 1,
            'dateToFinish' => '2026-11-20 15:00:00',
        ];

        $connection->table('zp_tickets')->insert([
            ['id' => 10, 'headline' => 'Owner To-Do', 'projectId' => 1, 'editorId' => (string) self::OWNER_ID] + $ticket,
            ['id' => 11, 'headline' => 'Assigned in a foreign project', 'projectId' => 2, 'editorId' => (string) self::OWNER_ID] + $ticket,
            ['id' => 12, 'headline' => 'Someone else\'s To-Do', 'projectId' => 1, 'editorId' => (string) self::OTHER_USER_ID] + $ticket,
            ['id' => 13, 'headline' => 'Client project To-Do', 'projectId' => 3, 'editorId' => (string) self::OWNER_ID] + $ticket,
        ]);

        $connection->table('zp_calendar')->insert([
            ['id' => 1, 'userId' => self::OWNER_ID, 'dateFrom' => '2026-11-18 09:00:00', 'dateTo' => '2026-11-18 10:00:00', 'description' => 'Dentist', 'allDay' => 'false'],
        ]);
    }
}
