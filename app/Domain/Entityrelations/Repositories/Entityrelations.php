<?php

namespace Leantime\Domain\Entityrelations\Repositories;

use Exception;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Schema;
use Leantime\Core\Db\Db as DbCore;

class Entityrelations
{
    private ConnectionInterface $db;

    public array $applications = [
        'general' => 'General',
    ];

    /**
     * __construct - neu db connection
     */
    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * @return false|mixed
     */
    public function getSetting(string $type): mixed
    {
        if ($this->checkIfInstalled() === false) {
            return false;
        }

        try {
            $result = $this->db->table('zp_settings')
                ->where('key', $type)
                ->limit(1)
                ->first();

            if ($result !== null && isset($result->value)) {
                return $result->value;
            }

            return false;
        } catch (Exception $e) {
            report($e);

            return false;
        }
    }

    public function saveSetting(string $type, string $value): bool
    {
        if ($this->checkIfInstalled() === false) {
            return false;
        }

        return $this->db->table('zp_settings')
            ->updateOrInsert(
                ['key' => $type],
                ['value' => $value]
            );
    }

    public function deleteSetting(string $type): void
    {
        $this->db->table('zp_settings')
            ->where('key', $type)
            ->limit(1)
            ->delete();
    }

    /**
     * checkIfInstalled checks if zp user table exists (and assumes that leantime is installed)
     */
    public function checkIfInstalled(): bool
    {
        if (session()->exists('isInstalled') && session('isInstalled')) {
            return true;
        }

        try {
            if (! Schema::hasTable('zp_user')) {
                session(['isInstalled' => false]);

                return false;
            }

            $this->db->table('zp_user')->count();

            session(['isInstalled' => true]);

            return true;
        } catch (Exception $e) {
            report($e);
            session(['isInstalled' => false]);

            return false;
        }
    }
}
