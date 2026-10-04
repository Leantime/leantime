<?php

namespace Unit\app\Domain\Tickets\Services;

use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Tickets\Services\KanbanViewSettings;
use Unit\TestCase;

/**
 * Preference resolution for the kanban board view menu: card fields (#1859) and sort (#1536).
 */
class KanbanViewSettingsTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata' => ['id' => 7, 'role' => 'editor', 'name' => 'Editor']]);
    }

    public function test_nothing_stored_resolves_to_todays_card(): void
    {
        foreach ([false, null, '', 'not json', '"a string"', 42] as $storedValue) {
            $preferences = KanbanViewSettings::resolve($storedValue);

            $this->assertSame(KanbanViewSettings::FIELD_DEFAULTS, $preferences['fields']);
        }

        // Everything that was on the card before the setting existed stays visible; sprint is opt-in.
        $defaults = KanbanViewSettings::defaults()['fields'];
        $this->assertFalse($defaults['sprint']);
        unset($defaults['sprint']);
        $this->assertNotContains(false, $defaults);
    }

    public function test_stored_fields_override_defaults_and_unknown_fields_are_dropped(): void
    {
        $stored = json_encode(['fields' => [
            'milestone' => false,
            'sprint' => true,
            'priority' => '0',
            'bogus' => true,
        ]]);

        $fields = KanbanViewSettings::resolve($stored)['fields'];

        $this->assertFalse($fields['milestone']);
        $this->assertTrue($fields['sprint']);
        $this->assertFalse($fields['priority']);
        $this->assertTrue($fields['effort'], 'Fields missing from the stored value keep their default');
        $this->assertArrayNotHasKey('bogus', $fields);
        $this->assertSame(array_keys(KanbanViewSettings::FIELD_DEFAULTS), array_keys($fields));
    }

    public function test_checked_list_becomes_full_field_map(): void
    {
        $fields = KanbanViewSettings::fieldsFromVisibleList(['priority', 'sprint', 'nope']);

        $this->assertTrue($fields['priority']);
        $this->assertTrue($fields['sprint']);
        $this->assertFalse($fields['milestone']);
        $this->assertArrayNotHasKey('nope', $fields);

        $this->assertNotContains(true, KanbanViewSettings::fieldsFromVisibleList(null), 'Nothing checked hides every optional field');
    }

    public function test_sort_defaults_to_manual_and_rejects_unknown_options(): void
    {
        $this->assertSame('manual', KanbanViewSettings::resolve(false)['sort']);
        $this->assertSame('priority', KanbanViewSettings::resolve(json_encode(['sort' => 'priority']))['sort']);
        $this->assertSame('manual', KanbanViewSettings::resolve(json_encode(['sort' => 'zp_tickets.id; DROP']))['sort']);
        $this->assertSame('manual', KanbanViewSettings::normalizeSort(['priority']));
        $this->assertSame('manual', KanbanViewSettings::normalizeSort('kanbansort'), 'repository keys are not sort options');
    }

    public function test_sort_options_map_to_repository_sort_keys(): void
    {
        $this->assertSame('kanbansort', KanbanViewSettings::repositorySortKey('manual'));
        $this->assertSame('priority', KanbanViewSettings::repositorySortKey('priority'));
        $this->assertSame('duedate', KanbanViewSettings::repositorySortKey('dueDate'));
        $this->assertSame('date', KanbanViewSettings::repositorySortKey('created'));
        $this->assertSame('effort', KanbanViewSettings::repositorySortKey('effort'));
        $this->assertSame('title', KanbanViewSettings::repositorySortKey('title'));
        $this->assertSame('kanbansort', KanbanViewSettings::repositorySortKey('bogus'), 'unknown options keep the manual order');
        $this->assertSame('kanbansort', KanbanViewSettings::repositorySortKey(null));
    }

    public function test_only_manual_sort_lets_dragging_rewrite_the_order(): void
    {
        $this->assertTrue(KanbanViewSettings::isManualSort('manual'));
        $this->assertTrue(KanbanViewSettings::isManualSort('bogus'));
        $this->assertFalse(KanbanViewSettings::isManualSort('priority'));
        $this->assertFalse(KanbanViewSettings::isManualSort('title'));
    }

    public function test_project_preference_wins_then_user_fallback_then_defaults(): void
    {
        $projectValue = json_encode(['fields' => ['milestone' => false]]);
        $userValue = json_encode(['fields' => ['tags' => false]]);

        $service = $this->serviceWithSettings([
            'usersettings.7.kanbanView.3' => $projectValue,
            'usersettings.7.kanbanView' => $userValue,
        ]);

        $this->assertFalse($service->getForCurrentUser(3)['fields']['milestone']);
        $this->assertTrue($service->getForCurrentUser(3)['fields']['tags']);

        // A board the user never configured starts from their latest choice.
        $this->assertFalse($service->getForCurrentUser(99)['fields']['tags']);

        $this->assertSame(KanbanViewSettings::defaults(), $this->serviceWithSettings([])->getForCurrentUser(3));
    }

    public function test_save_writes_project_and_user_keys(): void
    {
        $saved = [];
        $settingService = $this->make(SettingService::class, [
            'saveSetting' => function ($key, $value) use (&$saved) {
                $saved[$key] = $value;

                return false; // unchanged value: repository reports 0 affected rows
            },
        ]);

        $result = (new KanbanViewSettings($settingService))->saveForCurrentUser(3, ['priority'], 'dueDate');

        $this->assertTrue($result);
        $this->assertSame('dueDate', KanbanViewSettings::resolve($saved['usersettings.7.kanbanView'])['sort']);
        $this->assertSame(['usersettings.7.kanbanView', 'usersettings.7.kanbanView.3'], array_keys($saved));
        $this->assertTrue(KanbanViewSettings::resolve($saved['usersettings.7.kanbanView.3'])['fields']['priority']);
        $this->assertFalse(KanbanViewSettings::resolve($saved['usersettings.7.kanbanView.3'])['fields']['milestone']);
    }

    public function test_guests_get_defaults_and_cannot_save(): void
    {
        session()->forget('userdata');

        $service = $this->serviceWithSettings(['usersettings.0.kanbanView' => json_encode(['fields' => ['milestone' => false]])]);

        $this->assertSame(KanbanViewSettings::defaults(), $service->getForCurrentUser(3));
        $this->assertFalse($service->saveForCurrentUser(3, []));
    }

    /**
     * @param  array<string, string>  $settings  Stored settings by key.
     */
    private function serviceWithSettings(array $settings): KanbanViewSettings
    {
        $settingService = $this->make(SettingService::class, [
            'getSetting' => fn ($key, $default = false) => $settings[$key] ?? $default,
            'saveSetting' => true,
        ]);

        return new KanbanViewSettings($settingService);
    }
}
