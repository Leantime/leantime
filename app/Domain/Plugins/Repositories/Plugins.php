<?php

namespace Leantime\Domain\Plugins\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Plugins\Models\InstalledPlugin;

class Plugins
{
    private ConnectionInterface $db;

    /**
     * __construct - get database connection
     */
    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * @return array<InstalledPlugin>|false
     */
    public function getAllPlugins(bool $enabledOnly = true): false|array
    {
        $query = $this->db->table('zp_plugins')
            ->select(
                'id',
                'name',
                'enabled',
                'description',
                'version',
                'installdate',
                'foldername',
                'homepage',
                'authors',
                'format',
                'license'
            );

        if ($enabledOnly) {
            $query->where('enabled', true);
        }

        $results = $query->groupBy(
            'id',
            'name',
            'enabled',
            'description',
            'version',
            'installdate',
            'foldername',
            'homepage',
            'authors',
            'format',
            'license'
        )->get();

        $allPlugins = [];
        foreach ($results as $row) {
            $plugin = new InstalledPlugin;
            $plugin->id = $row->id;
            $plugin->name = $row->name;
            $plugin->enabled = $row->enabled;
            $plugin->description = $row->description;
            $plugin->version = $row->version;
            $plugin->installdate = $row->installdate;
            $plugin->foldername = $row->foldername;
            $plugin->homepage = $row->homepage;
            $plugin->authors = json_decode($row->authors);
            $plugin->format = $row->format;
            $plugin->license = $row->license;
            $allPlugins[] = $plugin;
        }

        return $allPlugins;
    }

    public function getPlugin(int $id): InstalledPlugin|false
    {
        $result = $this->db->table('zp_plugins')
            ->select(
                'id',
                'name',
                'enabled',
                'description',
                'version',
                'installdate',
                'foldername',
                'homepage',
                'authors',
                'license',
                'format'
            )
            ->where('id', $id)
            ->first();

        if ($result === null) {
            return false;
        }

        $plugin = new InstalledPlugin;
        $plugin->id = $result->id;
        $plugin->name = $result->name;
        $plugin->enabled = $result->enabled;
        $plugin->description = $result->description;
        $plugin->version = $result->version;
        $plugin->installdate = $result->installdate;
        $plugin->foldername = $result->foldername;
        $plugin->homepage = $result->homepage;
        $plugin->authors = $result->authors;
        $plugin->license = $result->license;
        $plugin->format = $result->format;

        return $plugin;
    }

    public function addPlugin(InstalledPlugin $plugin): false|string
    {
        $id = $this->db->table('zp_plugins')->insertGetId([
            'name' => $plugin->name,
            'enabled' => $plugin->enabled,
            'description' => $plugin->description,
            'version' => $plugin->version,
            'installdate' => $plugin->installdate,
            'foldername' => $plugin->foldername,
            'homepage' => $plugin->homepage,
            'authors' => $plugin->authors,
            'license' => $plugin->license ?? '',
            'format' => $plugin->format ?? 'folder',
        ]);

        return (string) $id;
    }

    public function enablePlugin(int $id): bool
    {
        return $this->db->table('zp_plugins')
            ->where('id', $id)
            ->update(['enabled' => 1]) > 0;
    }

    public function disablePlugin(int $id): bool
    {
        return $this->db->table('zp_plugins')
            ->where('id', $id)
            ->update(['enabled' => 0]) > 0;
    }

    public function removePlugin(int $id): bool
    {
        return $this->db->table('zp_plugins')
            ->where('id', $id)
            ->limit(1)
            ->delete() > 0;
    }
}
