<?php

namespace Unit\app\Domain\Notifications\Services;

use Leantime\Domain\Notifications\Services\PushPreferences;
use PHPUnit\Framework\TestCase;

/**
 * The per-user push switch: on unless stored as '0', read from a preloaded settings map.
 */
class PushPreferencesTest extends TestCase
{
    public function test_setting_key_is_scoped_to_the_user(): void
    {
        $this->assertSame('usersettings.7.pushNotifications', PushPreferences::settingKey(7));
    }

    /**
     * @dataProvider storedValuesProvider
     */
    public function test_is_enabled_reads_on_unless_stored_off(mixed $stored, bool $expected): void
    {
        $settings = $stored === null ? [] : [PushPreferences::settingKey(7) => $stored];

        $this->assertSame($expected, PushPreferences::isEnabled(7, $settings));
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function storedValuesProvider(): array
    {
        return [
            'never set' => [null, true],
            'empty (settings repo default)' => ['', true],
            'false (settings repo default)' => [false, true],
            'on' => ['1', true],
            'on as int' => [1, true],
            'off' => ['0', false],
            'off as int' => [0, false],
            'junk is off' => ['yes', false],
        ];
    }

    public function test_filter_keeps_users_with_push_on_and_normalizes_ids(): void
    {
        $settings = [
            PushPreferences::settingKey(2) => '0',
            PushPreferences::settingKey(3) => '1',
        ];

        $this->assertSame([3, 4], PushPreferences::filter(['2', 3, '4'], $settings));
        $this->assertSame([], PushPreferences::filter([], $settings));
    }
}
