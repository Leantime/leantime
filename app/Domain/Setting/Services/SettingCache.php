<?php

namespace Leantime\Domain\Setting\Services;

use Illuminate\Support\Facades\Cache;

class SettingCache
{
    private const CACHE_KEY_PREFIX = 'setting:';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get setting from cache
     */
    public function get(string $key): mixed
    {
        return Cache::get(self::CACHE_KEY_PREFIX.$key);
    }

    /**
     * Store setting in cache
     */
    public function set(string $key, mixed $value): void
    {
        Cache::put(self::CACHE_KEY_PREFIX.$key, $value, self::CACHE_TTL);
    }

    /**
     * Remove setting from cache
     */
    public function forget(string $key): void
    {
        Cache::forget(self::CACHE_KEY_PREFIX.$key);
    }

    /**
     * Clear all settings from cache
     */
    public function flush(): void
    {
        Cache::tags(['settings'])->flush();
    }
}
