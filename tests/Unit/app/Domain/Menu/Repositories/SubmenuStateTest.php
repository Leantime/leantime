<?php

namespace Unit\app\Domain\Menu\Repositories;

use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use ReflectionClass;
use ReflectionProperty;
use Unit\TestCase;

/**
 * setSubmenuState stores every UI state (accordions, sidebar widths …) in one serialized user
 * setting. It must write on top of what is stored, not on top of this request's session copy:
 * otherwise a state saved by another request since this session was loaded is dropped.
 */
class SubmenuStateTest extends TestCase
{
    public function test_a_state_saved_by_another_request_survives(): void
    {
        session(['userdata.id' => 7, 'usersettings.submenuToggle' => ['accordion_content-a' => 'open']]);

        $saved = null;
        $settings = $this->createMock(SettingRepository::class);
        // Another request saved "b" after this session was loaded.
        $settings->method('getSetting')->willReturn(serialize([
            'accordion_content-a' => 'open',
            'accordion_content-b' => 'closed',
        ]));
        $settings->method('saveSetting')->willReturnCallback(function (string $key, string $value) use (&$saved): bool {
            $saved = [$key, unserialize($value)];

            return true;
        });

        $menu = (new ReflectionClass(Menu::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(Menu::class, 'settingsRepo'))->setValue($menu, $settings);

        $menu->setSubmenuState('content-box_my-calendar_left', '320');

        $this->assertSame('usersetting.7.submenuToggle', $saved[0]);
        $this->assertSame([
            'accordion_content-a' => 'open',
            'accordion_content-b' => 'closed',
            'content-box_my-calendar_left' => '320',
        ], $saved[1]);
        $this->assertSame('320', session('usersettings.submenuToggle.content-box_my-calendar_left'));
        $this->assertSame('closed', session('usersettings.submenuToggle.accordion_content-b'));
    }

    public function test_nothing_stored_yet_starts_from_the_session(): void
    {
        session(['userdata.id' => 7, 'usersettings.submenuToggle' => ['accordion_content-a' => 'open']]);

        $saved = null;
        $settings = $this->createMock(SettingRepository::class);
        $settings->method('getSetting')->willReturn(false);
        $settings->method('saveSetting')->willReturnCallback(function (string $key, string $value) use (&$saved): bool {
            $saved = unserialize($value);

            return true;
        });

        $menu = (new ReflectionClass(Menu::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(Menu::class, 'settingsRepo'))->setValue($menu, $settings);

        $menu->setSubmenuState('accordion_content-c', 'closed');

        $this->assertSame(['accordion_content-a' => 'open', 'accordion_content-c' => 'closed'], $saved);
    }
}
