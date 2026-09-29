<?php

namespace Leantime\Domain\Setting\Repositories;

use Exception;
use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Setting\Services\SettingCache;

class Setting
{
    private ConnectionInterface $db;

    private SettingCache $cache;

    public array $applications = [
        'general' => 'General',
    ];

    /**
     * __construct - neu db connection
     */
    public function __construct(DbCore $db, SettingCache $cache)
    {
        $this->db = $db->getConnection();
        $this->cache = $cache;
    }

    /**
     * @return false|mixed
     */
    public function getSetting(string $type, mixed $default = false): mixed
    {
        if ($this->checkIfInstalled() === false) {
            return false;
        }

        // Check cache first
        $cachedValue = $this->cache->get($type);
        if ($cachedValue !== null) {
            return $cachedValue;
        }

        try {
            $result = $this->db->table('zp_settings')
                ->where('key', $type)
                ->limit(1)
                ->first();

            if ($result !== null && isset($result->value)) {
                // Store in cache for future requests
                $this->cache->set($type, $result->value);

                return $result->value;
            }

            // value is not in the db, which is fine. Let's cache that too
            $this->cache->set($type, false);

            return $default;
        } catch (Exception $e) {
            report($e);

            return false;
        }
    }

    /**
     * Writes a setting and keeps the cache in step with what the database confirmed.
     *
     * The new value is cached only when the write reports success. On a false result the
     * cached copy is dropped instead, so the next getSetting() reads the stored row:
     * updateOrInsert() also returns false when an existing row matched but nothing changed
     * (drivers that count changed rather than matched rows), and only the database can tell
     * that benign no-op apart from a lost write.
     *
     * @param  string  $type  The setting key.
     * @param  mixed  $value  The value to store.
     * @return bool True when the write reported success; false when Leantime is not installed or the write reported nothing written.
     */
    public function saveSetting(string $type, mixed $value): bool
    {
        if ($this->checkIfInstalled() === false) {
            return false;
        }

        $saved = $this->db->table('zp_settings')
            ->updateOrInsert(
                ['key' => $type],
                ['value' => $value]
            );

        if (! $saved) {
            $this->cache->forget($type);

            return false;
        }

        $this->cache->set($type, $value);

        return true;
    }

    /**
     * Retrieves multiple settings in a single query.
     *
     * @param  array<string>  $keys  The setting keys to fetch.
     * @return array<string, mixed> Map of key => value for found settings.
     */
    public function getSettingsForKeys(array $keys): array
    {
        if (empty($keys) || $this->checkIfInstalled() === false) {
            return [];
        }

        $results = [];

        // Check cache first for all keys
        $uncachedKeys = [];
        foreach ($keys as $key) {
            $cachedValue = $this->cache->get($key);
            if ($cachedValue !== null) {
                $results[$key] = $cachedValue;
            } else {
                $uncachedKeys[] = $key;
            }
        }

        if (empty($uncachedKeys)) {
            return $results;
        }

        try {
            $rows = $this->db->table('zp_settings')
                ->whereIn('key', $uncachedKeys)
                ->get(['key', 'value']);

            $foundKeys = [];
            foreach ($rows as $row) {
                $results[$row->key] = $row->value;
                $this->cache->set($row->key, $row->value);
                $foundKeys[] = $row->key;
            }

            // Cache misses as false
            foreach (array_diff($uncachedKeys, $foundKeys) as $missingKey) {
                $this->cache->set($missingKey, false);
            }
        } catch (Exception $e) {
            report($e);
        }

        return $results;
    }

    public function deleteSetting(string $type): void
    {
        $this->db->table('zp_settings')
            ->where('key', $type)
            ->delete();

        // Remove from cache
        $this->cache->forget($type);
    }

    /**
     * checkIfInstalled checks if zp user table exists (and assumes that leantime is installed)
     */
    public function checkIfInstalled(): bool
    {
        $cachedValue = $this->cache->get('isInstalled');
        if ($cachedValue !== null) {
            return true;
        }

        try {
            $this->db->table('zp_user')->count();

            $this->cache->set('isInstalled', true);

            return true;
        } catch (Exception $e) {
            report($e);

            return false;
        }
    }
}
