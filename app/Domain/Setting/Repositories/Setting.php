<?php

namespace Leantime\Domain\Setting\Repositories;

use Exception;
use Leantime\Infrastructure\Database\Db as DbCore;
use Leantime\Domain\Setting\Services\SettingCache;
use PDO;

class Setting
{
    private DbCore $db;

    private SettingCache $cache;

    public array $applications = [
        'general' => 'General',
    ];

    /**
     * __construct - neu db connection
     */
    public function __construct(DbCore $db, SettingCache $cache)
    {
        $this->db = $db;
        $this->cache = $cache;
    }

    /**
     * @return false|mixed
     */
    public function getSetting($type, $default = false): mixed
    {
        if ($this->checkIfInstalled() === false) {
            return false;
        }

        // Check cache first
        $cachedValue = $this->cache->get($type);
        if ($cachedValue !== null) {
            return $cachedValue;
        }

        $sql = 'SELECT
                        value
                FROM zp_settings WHERE `key` = :key
                LIMIT 1';

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindvalue(':key', $type, PDO::PARAM_STR);

        try {
            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();
        } catch (Exception $e) {
            report($e);

            return false;
        }

        if ($values !== false && isset($values['value'])) {
            // Store in cache for future requests
            $this->cache->set($type, $values['value']);

            return $values['value'];
        }

        // value is not in the db, which is fine. Let's cache that too
        $this->cache->set($type, false);

        // TODO: This needs to return null or throw an exception if the setting doesn't exist.
        return $default;
    }

    public function saveSetting($type, $value): bool
    {

        if ($this->checkIfInstalled() === false) {
            return false;
        }

        $sql = 'INSERT INTO zp_settings (`key`, `value`)
                VALUES (:key, :value) ON DUPLICATE KEY UPDATE
                  `value` = :valueUpdate';

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindvalue(':key', $type, PDO::PARAM_STR);
        $stmn->bindvalue(':value', $value, PDO::PARAM_STR);
        $stmn->bindvalue(':valueUpdate', $value, PDO::PARAM_STR);

        $return = $stmn->execute();
        $stmn->closeCursor();

        // Update cache
        $this->cache->set($type, $value);

        return $return;
    }

    public function deleteSetting($type): void
    {

        $sql = 'DELETE FROM zp_settings WHERE `key` = :key LIMIT 1';

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindvalue(':key', $type, PDO::PARAM_STR);

        $stmn->execute();
        $stmn->closeCursor();

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

            $stmn = $this->db->database->prepare('SELECT COUNT(*) FROM zp_user');

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            $this->cache->set('isInstalled', true);

            return true;

        } catch (Exception $e) {
            report($e);

            return false;
        }
    }
}
