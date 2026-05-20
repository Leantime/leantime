<?php

namespace Leantime\Core\Mcp\Auth;

class McpAbilityCatalog
{
    public const PRESETS = [
        'read-only' => [
            'mcp:connect',
            'mcp:read',
            'projects:read',
            'tickets:read',
            'comments:read',
            'users:read',
        ],
        'ticket-writer' => [
            'mcp:connect',
            'mcp:read',
            'mcp:write',
            'projects:read',
            'projects:write',
            'tickets:read',
            'tickets:write',
            'comments:read',
            'comments:write',
            'users:read',
        ],
        'time-writer' => [
            'mcp:connect',
            'mcp:read',
            'mcp:write',
            'projects:read',
            'tickets:read',
            'timesheets:write',
            'comments:read',
            'users:read',
        ],
        'full-agent' => [
            'mcp:connect',
            'mcp:read',
            'mcp:write',
            'projects:read',
            'projects:write',
            'tickets:read',
            'tickets:write',
            'comments:read',
            'comments:write',
            'users:read',
            'timesheets:write',
        ],
        'admin' => ['*'],
    ];

    public static function presets(): array
    {
        return self::PRESETS;
    }

    public static function abilitiesForPreset(string $preset): array
    {
        return self::PRESETS[$preset] ?? [];
    }

    public static function allAbilities(): array
    {
        $abilities = [];
        foreach (self::PRESETS as $presetAbilities) {
            $abilities = array_merge($abilities, $presetAbilities);
        }

        return array_values(array_unique($abilities));
    }

    public static function normalize(array $abilities): array
    {
        $abilities = array_values(array_filter(array_map('trim', $abilities), static fn ($value) => $value !== ''));

        if (in_array('*', $abilities, true)) {
            return ['*'];
        }

        return array_values(array_unique($abilities));
    }
}
