<?php

namespace Leantime\Domain\Notifications\Services;

/**
 * The per-user "Receive push notifications" switch.
 *
 * Push is its own channel with its own on/off, separate from "Receive email
 * notifications" (zp_user.notifications). The switch lives in zp_settings as
 * usersettings.{userId}.pushNotifications, '1' or '0'; a missing setting means
 * on, so nobody loses push by never having seen the setting.
 *
 * The switch is final: it is applied after relevance, category, mention and
 * collaborator bypasses, so a user who switched push off never gets one.
 */
class PushPreferences
{
    public const SETTING_SUFFIX = 'pushNotifications';

    public static function settingKey(int $userId): string
    {
        return 'usersettings.'.$userId.'.'.self::SETTING_SUFFIX;
    }

    /**
     * Whether the user has push switched on, from a preloaded settings map.
     *
     * @param  array<string, mixed>  $preloadedSettings  Setting key => stored value.
     */
    public static function isEnabled(int $userId, array $preloadedSettings): bool
    {
        $value = $preloadedSettings[self::settingKey($userId)] ?? null;

        return $value === null || $value === false || $value === '' || (string) $value === '1';
    }

    /**
     * Narrows a recipient list to the users who have push switched on.
     *
     * @param  array<int, mixed>  $userIds
     * @param  array<string, mixed>  $preloadedSettings  Setting key => stored value.
     * @return array<int, int>
     */
    public static function filter(array $userIds, array $preloadedSettings): array
    {
        return array_values(array_filter(
            array_map('intval', $userIds),
            fn (int $userId) => self::isEnabled($userId, $preloadedSettings)
        ));
    }
}
