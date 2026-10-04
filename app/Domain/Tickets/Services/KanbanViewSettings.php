<?php

declare(strict_types=1);

namespace Leantime\Domain\Tickets\Services;

use Leantime\Domain\Setting\Services\Setting as SettingService;

/**
 * Per-user kanban board view preferences: which optional fields the cards show (#1859).
 *
 * Stored as JSON in zp_settings under `usersettings.{userId}.kanbanView.{projectId}` (the board
 * the user configured) plus `usersettings.{userId}.kanbanView` (their latest choice, used as the
 * starting point on boards they never configured). The defaults reproduce the card exactly as it
 * looked before the setting existed, so users who never touch it see no change.
 */
class KanbanViewSettings
{
    /**
     * Optional card fields and whether each is shown by default. Order = order in the menu.
     * Every field except sprint was always shown before this setting existed; sprint is opt-in.
     *
     * @var array<string, bool>
     */
    public const FIELD_DEFAULTS = [
        'description' => true,
        'dueDate' => true,
        'milestone' => true,
        'effort' => true,
        'priority' => true,
        'assignee' => true,
        'sprint' => false,
        'tags' => true,
        'subtasks' => true,
        'comments' => true,
    ];

    public function __construct(
        private SettingService $settingService,
    ) {}

    /**
     * The built-in preferences used when the user saved nothing.
     *
     * @return array{fields: array<string, bool>}
     */
    public static function defaults(): array
    {
        return ['fields' => self::FIELD_DEFAULTS];
    }

    /**
     * Turns a stored value (JSON string, decoded array, false/null when unset, or garbage) into a
     * complete, valid preference set. Unknown fields are dropped and missing ones fall back to
     * their default, so fields added later appear with their default visibility.
     *
     * @param  mixed  $stored  The raw value read from the settings table.
     * @return array{fields: array<string, bool>}
     */
    public static function resolve(mixed $stored): array
    {
        $preferences = self::decode($stored);

        return [
            'fields' => self::resolveFields($preferences['fields'] ?? null),
        ];
    }

    /**
     * Normalizes a field-visibility map against the known fields.
     *
     * @param  mixed  $storedFields  A map of field => visible (bool-ish); anything else yields the defaults.
     * @return array<string, bool>
     */
    public static function resolveFields(mixed $storedFields): array
    {
        $fields = self::FIELD_DEFAULTS;

        if (! is_array($storedFields)) {
            return $fields;
        }

        foreach ($fields as $fieldName => $defaultVisible) {
            if (! array_key_exists($fieldName, $storedFields)) {
                continue;
            }

            $fields[$fieldName] = filter_var($storedFields[$fieldName], FILTER_VALIDATE_BOOLEAN);
        }

        return $fields;
    }

    /**
     * Builds the field map from a submitted checkbox list: listed known fields are visible,
     * every other known field is hidden.
     *
     * @param  mixed  $visibleFieldNames  The submitted list of checked field names.
     * @return array<string, bool>
     */
    public static function fieldsFromVisibleList(mixed $visibleFieldNames): array
    {
        $visibleFieldNames = is_array($visibleFieldNames) ? array_map('strval', $visibleFieldNames) : [];

        $fields = [];
        foreach (array_keys(self::FIELD_DEFAULTS) as $fieldName) {
            $fields[$fieldName] = in_array($fieldName, $visibleFieldNames, true);
        }

        return $fields;
    }

    /**
     * Reads the current user's kanban preferences for a project.
     *
     * Falls back to the user's latest saved choice, then to the built-in defaults.
     *
     * @param  int  $projectId  The project whose board is being viewed.
     * @return array{fields: array<string, bool>}
     */
    public function getForCurrentUser(int $projectId): array
    {
        $userId = (int) session('userdata.id');
        if ($userId <= 0) {
            return self::defaults();
        }

        $stored = $this->settingService->getSetting($this->projectKey($userId, $projectId));
        if ($stored === false || $stored === null || $stored === '') {
            $stored = $this->settingService->getSetting($this->userKey($userId));
        }

        return self::resolve($stored);
    }

    /**
     * Saves the current user's kanban card fields for a project (and as their default for
     * boards they have not configured yet).
     *
     * @param  int  $projectId  The project whose board was configured.
     * @param  mixed  $visibleFieldNames  The checked field names from the "Card fields" menu.
     * @return bool False when there is no logged-in user to save for.
     */
    public function saveForCurrentUser(int $projectId, mixed $visibleFieldNames): bool
    {
        $userId = (int) session('userdata.id');
        if ($userId <= 0) {
            return false;
        }

        $preferences = [
            'fields' => self::fieldsFromVisibleList($visibleFieldNames),
        ];

        $encodedPreferences = (string) json_encode($preferences);

        // saveSetting() reports false when the stored value did not change (0 affected rows),
        // so its result is not a failure signal here; re-saving the same choice is fine.
        $this->settingService->saveSetting($this->userKey($userId), $encodedPreferences);
        if ($projectId > 0) {
            $this->settingService->saveSetting($this->projectKey($userId, $projectId), $encodedPreferences);
        }

        return true;
    }

    /**
     * Decodes a stored preference value into an array.
     *
     * @param  mixed  $stored  JSON string, array or anything else.
     * @return array<string, mixed>
     */
    private static function decode(mixed $stored): array
    {
        if (is_array($stored)) {
            return $stored;
        }

        if (! is_string($stored) || $stored === '') {
            return [];
        }

        $decoded = json_decode($stored, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Settings key for the user's per-project board preference.
     */
    private function projectKey(int $userId, int $projectId): string
    {
        return 'usersettings.'.$userId.'.kanbanView.'.$projectId;
    }

    /**
     * Settings key for the user's latest (cross-project fallback) board preference.
     */
    private function userKey(int $userId): string
    {
        return 'usersettings.'.$userId.'.kanbanView';
    }
}
