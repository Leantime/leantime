<?php

namespace Leantime\Domain\Setting\Services;

use Illuminate\Support\Facades\Cache;

/**
 * SettingCache - Provides a two-tier cache for settings.
 *
 * Tier 1: In-memory array (eliminates redundant I/O within a single request)
 * Tier 2: Laravel cache store (file/Redis, persists across requests with 1hr TTL)
 *
 * The same setting key is often read 5-10 times within a single request
 * (Theme, Localization, Menu all reading overlapping keys). The in-memory
 * tier eliminates all redundant file/Redis reads after the first access.
 */
class SettingCache
{
    private const CACHE_KEY_PREFIX = 'setting:';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * In-memory request-level cache.
     * Stores values for the duration of the PHP request to avoid
     * redundant file/Redis cache reads for the same key.
     *
     * Uses a sentinel to distinguish "key cached as null" from "key not cached".
     *
     * @var array<string, mixed>
     */
    private array $inMemory = [];

    private const NOT_FOUND = '__SETTING_CACHE_NOT_FOUND__';

    /**
     * Get setting from cache.
     * Checks in-memory first, then falls through to Laravel cache.
     */
    public function get(string $key): mixed
    {
        // Tier 1: In-memory (zero I/O)
        if (array_key_exists($key, $this->inMemory)) {
            $value = $this->inMemory[$key];

            return $value === self::NOT_FOUND ? null : $value;
        }

        // Tier 2: Laravel cache (file/Redis)
        $value = Cache::get(self::CACHE_KEY_PREFIX.$key);

        // Store in memory for subsequent reads within this request
        $this->inMemory[$key] = $value ?? self::NOT_FOUND;

        return $value;
    }

    /**
     * Store setting in both in-memory and Laravel cache.
     */
    public function set(string $key, mixed $value): void
    {
        $this->inMemory[$key] = $value;
        Cache::put(self::CACHE_KEY_PREFIX.$key, $value, self::CACHE_TTL);
    }

    /**
     * Remove setting from both in-memory and Laravel cache.
     */
    public function forget(string $key): void
    {
        unset($this->inMemory[$key]);
        Cache::forget(self::CACHE_KEY_PREFIX.$key);
    }

    /**
     * Clear all settings from cache.
     */
    public function flush(): void
    {
        $this->inMemory = [];
        Cache::tags(['settings'])->flush();
    }
}
