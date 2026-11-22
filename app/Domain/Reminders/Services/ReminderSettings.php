<?php

namespace Leantime\Domain\Reminders\Services;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;

/**
 * A person's reminder settings, stored in zp_settings like their other preferences:
 *
 *   usersettings.{id}.reminders.summary      '1' on (default) / '0' off
 *   usersettings.{id}.reminders.summaryTime  'HH:MM' in their timezone, default 08:00
 *   usersettings.{id}.reminders.taskPreset   default (default) | more | less | off
 *   usersettings.{id}.reminders.task.{ticketId}  the person's choice for one to-do; stored only
 *                                                when they made one ("inherit" deletes it)
 *
 * The workspace switch companysettings.reminders.enabled can only turn reminders off for
 * everyone ('0'). It never turns anything on or makes reminders louder for anyone.
 */
class ReminderSettings
{
    public const PRESET_DEFAULT = 'default';

    public const PRESET_MORE = 'more';

    public const PRESET_LESS = 'less';

    public const PRESET_OFF = 'off';

    public const PRESETS = [self::PRESET_DEFAULT, self::PRESET_MORE, self::PRESET_LESS, self::PRESET_OFF];

    public const DEFAULT_SUMMARY_TIME = '08:00';

    public const WORKSPACE_SWITCH = 'companysettings.reminders.enabled';

    /** When the reminder job last ran (UTC `Y-m-d H:i:s`), for the admin warning. */
    public const HEARTBEAT = 'reminders.lastRun';

    public function __construct(
        private SettingRepository $settings,
        private Environment $config,
    ) {}

    public function workspaceEnabled(): bool
    {
        return (string) $this->settings->getSetting(self::WORKSPACE_SWITCH) !== '0';
    }

    /**
     * @return array{summary: bool, summaryTime: string, taskPreset: string, timezone: string}
     */
    public function forUser(int $userId): array
    {
        return $this->forUsers([$userId])[$userId];
    }

    /**
     * Settings for many people in one read.
     *
     * @param  array<int, int>  $userIds
     * @return array<int, array{summary: bool, summaryTime: string, taskPreset: string, timezone: string}>
     */
    public function forUsers(array $userIds): array
    {
        $keys = [];
        foreach ($userIds as $userId) {
            foreach (['reminders.summary', 'reminders.summaryTime', 'reminders.taskPreset', 'timezone'] as $name) {
                $keys[] = 'usersettings.'.$userId.'.'.$name;
            }
        }
        $stored = $keys === [] ? [] : $this->settings->getSettingsForKeys($keys);

        $result = [];
        foreach ($userIds as $userId) {
            $value = static fn (string $name): string => is_scalar($stored['usersettings.'.$userId.'.'.$name] ?? null) ? (string) $stored['usersettings.'.$userId.'.'.$name] : '';

            $time = $value('reminders.summaryTime');
            $preset = $value('reminders.taskPreset');
            $timezone = $value('timezone');

            $result[$userId] = [
                'summary' => $value('reminders.summary') !== '0',
                'summaryTime' => self::isValidTime($time) ? $time : self::DEFAULT_SUMMARY_TIME,
                'taskPreset' => in_array($preset, self::PRESETS, true) ? $preset : self::PRESET_DEFAULT,
                'timezone' => self::isValidTimezone($timezone) ? $timezone : $this->defaultTimezone(),
            ];
        }

        return $result;
    }

    /**
     * Saves the given values for one person; null leaves a value as it is. Callers validate first.
     */
    public function save(int $userId, ?bool $summary, ?string $summaryTime, ?string $taskPreset): void
    {
        if ($summary !== null) {
            $this->settings->saveSetting('usersettings.'.$userId.'.reminders.summary', $summary ? '1' : '0');
        }
        if ($summaryTime !== null) {
            $this->settings->saveSetting('usersettings.'.$userId.'.reminders.summaryTime', $summaryTime);
        }
        if ($taskPreset !== null) {
            $this->settings->saveSetting('usersettings.'.$userId.'.reminders.taskPreset', $taskPreset);
        }
    }

    /** The person's choice for one to-do, or null when they follow their default. */
    public function taskChoice(int $userId, int $ticketId): ?string
    {
        $stored = $this->settings->getSetting(self::taskKey($userId, $ticketId));

        return is_string($stored) && in_array($stored, self::PRESETS, true) ? $stored : null;
    }

    /** Saves the person's choice for one to-do; null deletes it so their default applies again. */
    public function saveTaskChoice(int $userId, int $ticketId, ?string $preset): void
    {
        if ($preset === null) {
            $this->settings->deleteSetting(self::taskKey($userId, $ticketId));

            return;
        }

        $this->settings->saveSetting(self::taskKey($userId, $ticketId), $preset);
    }

    /**
     * What a to-do follows when the person made no choice for it: off when it isn't theirs
     * (reminders on someone else's to-do are opt-in) or when their default is off, "less" for
     * to-dos that repeat every weekday or more often, otherwise their default.
     */
    public static function inheritedPreset(string $defaultPreset, bool $frequentRoutine, bool $isYours = true): string
    {
        return match (true) {
            ! $isYours, $defaultPreset === self::PRESET_OFF => self::PRESET_OFF,
            $frequentRoutine => self::PRESET_LESS,
            default => $defaultPreset,
        };
    }

    /**
     * The preset that governs a to-do's reminders: the person's choice for it, else what it
     * inherits. The one resolver the summary and the API both use.
     */
    public static function effectivePreset(?string $chosen, string $defaultPreset, bool $frequentRoutine, bool $isYours = true): string
    {
        return $chosen !== null && in_array($chosen, self::PRESETS, true)
            ? $chosen
            : self::inheritedPreset($defaultPreset, $frequentRoutine, $isYours);
    }

    /** True when the reminder job ran within the last $maxAgeMinutes. */
    public function schedulerRanSince(\DateTimeInterface $nowUtc, int $maxAgeMinutes = 60): bool
    {
        $lastRun = $this->settings->getSetting(self::HEARTBEAT);
        if (! is_string($lastRun) || $lastRun === '') {
            return false;
        }

        $ranAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $lastRun, new \DateTimeZone('UTC'));
        if ($ranAt === false) {
            return false;
        }

        return $nowUtc->getTimestamp() - $ranAt->getTimestamp() <= $maxAgeMinutes * 60;
    }

    /** The workspace switch: on clears it; off stores '0'. */
    public function saveWorkspaceEnabled(bool $enabled): void
    {
        if ($enabled) {
            $this->settings->deleteSetting(self::WORKSPACE_SWITCH);

            return;
        }

        $this->settings->saveSetting(self::WORKSPACE_SWITCH, '0');
    }

    public static function taskKey(int $userId, int $ticketId): string
    {
        return 'usersettings.'.$userId.'.reminders.task.'.$ticketId;
    }

    public function recordHeartbeat(string $nowUtc): void
    {
        $this->settings->saveSetting(self::HEARTBEAT, $nowUtc);
    }

    /**
     * The person's own time format (Settings → Time format, stored as a PHP date format), else the
     * language's default. Read from storage, so it works without a session (the scheduler).
     */
    public function timeFormat(int $userId): string
    {
        $stored = $this->settings->getSetting('usersettings.'.$userId.'.time_format');
        if (is_string($stored) && trim($stored) !== '') {
            return html_entity_decode($stored, ENT_QUOTES);
        }

        $default = (string) app()->make(LanguageCore::class)->__('language.timeformat');

        return $default !== '' && $default !== 'language.timeformat' ? $default : 'H:i';
    }

    /** A stored 'HH:MM' time in a PHP time format: '14:00' → '02:00 PM' with 'h:i A'. */
    public static function formatTime(string $time, string $format): string
    {
        $parsed = \DateTimeImmutable::createFromFormat('!H:i', $time);

        return $parsed === false ? $time : $parsed->format($format);
    }

    /** 'HH:MM', 00:00 to 23:59. */
    public static function isValidTime(string $time): bool
    {
        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) === 1;
    }

    public static function isValidTimezone(string $timezone): bool
    {
        return $timezone !== '' && in_array($timezone, \DateTimeZone::listIdentifiers(), true);
    }

    private function defaultTimezone(): string
    {
        $timezone = (string) ($this->config->defaultTimezone ?? '');

        return self::isValidTimezone($timezone) ? $timezone : 'UTC';
    }
}
