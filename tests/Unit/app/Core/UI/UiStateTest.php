<?php

namespace Unit\app\Core\UI;

use Illuminate\Database\Connection;
use Illuminate\Database\SQLiteConnection;
use Leantime\Core\UI\UiState;
use PDO;
use Unit\TestCase;

/**
 * UiState keeps each user's UI states (accordions, sidebar widths, calendar views) as one
 * zp_settings row per state, outside the session. These pin the properties that motivated it:
 * a save is visible to the next request, saves of different states from requests that were in
 * flight at the same time all survive, and states saved before (the serialized submenuToggle
 * blob) still apply.
 */
class UiStateTest extends TestCase
{
    private Connection $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new SQLiteConnection(new PDO('sqlite::memory:'));
        $this->db->getSchemaBuilder()->create('zp_settings', function ($table) {
            $table->string('key', 175)->primary();
            $table->text('value')->nullable();
        });

        session(['userdata.id' => 7]);
    }

    /** A new instance = a new request (each loads the stored states once). */
    private function request(): UiState
    {
        return new UiState($this->db);
    }

    public function test_a_saved_state_shows_on_the_next_request(): void
    {
        $this->request()->set('accordion_content-tickets-dates', 'closed');

        $this->assertSame('closed', $this->request()->get('accordion_content-tickets-dates'));
        $this->assertSame('closed', $this->db->table('zp_settings')->where('key', 'usersetting.7.ui.accordion_content-tickets-dates')->value('value'));
    }

    public function test_saves_from_requests_in_flight_together_all_survive(): void
    {
        // Both requests load the (empty) states before either saves.
        $first = $this->request();
        $second = $this->request();
        $first->all();
        $second->all();

        $first->set('accordion_content-a', 'closed');
        $second->set('content-box_my-calendar_left', '320');

        $next = $this->request();
        $this->assertSame('closed', $next->get('accordion_content-a'));
        $this->assertSame('320', $next->get('content-box_my-calendar_left'));
    }

    public function test_saving_the_same_state_again_updates_it(): void
    {
        $this->request()->set('myCalendarView', 'timeGridWeek');
        $this->request()->set('myCalendarView', 'dayGridMonth');

        $this->assertSame('dayGridMonth', $this->request()->get('myCalendarView'));
        $this->assertSame(1, $this->db->table('zp_settings')->where('key', 'like', 'usersetting.7.ui.%')->count());
    }

    public function test_states_saved_before_still_apply_and_newer_rows_win(): void
    {
        $this->db->table('zp_settings')->insert([
            'key' => 'usersetting.7.submenuToggle',
            'value' => serialize(['accordion_content-a' => 'closed', 'myCalendarView' => 'timeGridDay']),
        ]);
        $this->request()->set('myCalendarView', 'dayGridMonth');

        $state = $this->request();
        $this->assertSame('closed', $state->get('accordion_content-a'));
        $this->assertSame('dayGridMonth', $state->get('myCalendarView'));
    }

    public function test_names_that_differ_only_by_case_stay_separate(): void
    {
        // zp_settings.key is case-insensitive on MySQL (utf8mb4_unicode_ci); SQLite compares
        // binary, so check the keys only use characters every collation compares exactly.
        $this->request()->set('accordion_content-Foo', 'closed');
        $this->request()->set('accordion_content-foo', 'open');

        $state = $this->request();
        $this->assertSame('closed', $state->get('accordion_content-Foo'));
        $this->assertSame('open', $state->get('accordion_content-foo'));

        $keys = $this->db->table('zp_settings')->pluck('key')->all();
        $this->assertCount(2, $keys);
        foreach ($keys as $key) {
            $this->assertMatchesRegularExpression('/^[a-z0-9_.~-]+$/', $key);
        }
    }

    public function test_a_dotted_name_saved_by_the_old_store_still_applies(): void
    {
        // The old store wrote through a dot-notation session path: "section.part" was nested.
        $this->db->table('zp_settings')->insert([
            'key' => 'usersetting.7.submenuToggle',
            'value' => serialize(['section' => ['part' => 'closed'], 'mainMenu' => 'open']),
        ]);

        $state = $this->request();
        $this->assertSame('closed', $state->get('section.part'));
        $this->assertSame('open', $state->get('mainMenu'));
    }

    public function test_states_are_per_user(): void
    {
        $this->request()->set('accordion_content-a', 'closed');

        session(['userdata.id' => 8]);
        $this->assertNull($this->request()->get('accordion_content-a'));
        $this->assertSame('open', $this->request()->get('accordion_content-a', 'open'));
    }

    public function test_without_a_user_nothing_is_read_or_saved(): void
    {
        session()->forget('userdata');

        $this->request()->set('accordion_content-a', 'closed');

        $this->assertSame('open', $this->request()->get('accordion_content-a', 'open'));
        $this->assertSame(0, $this->db->table('zp_settings')->count());
    }

    public function test_a_long_name_is_stored_under_a_short_stable_key(): void
    {
        $name = 'accordion_content-'.str_repeat('x', 300);

        $this->request()->set($name, 'closed');

        $this->assertSame('closed', $this->request()->get($name));
        $key = $this->db->table('zp_settings')->where('key', 'like', 'usersetting.7.ui.%')->value('key');
        $this->assertLessThanOrEqual(175, strlen($key));
    }

    public function test_a_store_without_a_database_returns_defaults(): void
    {
        // Unit tests and installs have no usable connection: reads fall back, saves do nothing.
        $state = new UiState(null);

        $this->assertSame('open', $state->get('accordion_content-a', 'open'));
    }
}
