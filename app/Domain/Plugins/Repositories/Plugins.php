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

    /**
     * Updates an installed plugin's row from a freshly read plugin model: metadata and version,
     * and the license when one is given. The enabled flag is left as it is.
     */
    public function updatePlugin(int $id, InstalledPlugin $plugin): bool
    {
        $values = [
            'name' => $plugin->name,
            'description' => $plugin->description,
            'version' => $plugin->version,
            'homepage' => $plugin->homepage,
            'authors' => $plugin->authors,
            'format' => $plugin->format ?? 'folder',
        ];

        if (! empty($plugin->license)) {
            $values['license'] = $plugin->license;
        }

        $this->db->table('zp_plugins')->where('id', $id)->update($values);

        return true;
    }

    /**
     * Rewrites one plugin row ({@see updatePlugin()}) and removes the other rows of the same
     * plugin, in one transaction: either all of it is saved or none of it.
     *
     * @param  array<int>  $duplicateIds  Other rows of the same plugin folder.
     */
    public function replacePluginRows(int $id, InstalledPlugin $plugin, array $duplicateIds): void
    {
        $this->db->transaction(function () use ($id, $plugin, $duplicateIds): void {
            $this->updatePlugin($id, $plugin);

            if ($duplicateIds !== []) {
                $this->db->table('zp_plugins')->whereIn('id', $duplicateIds)->delete();
            }
        });
    }

    /**
     * The ids of every row for a plugin folder, oldest first (updates used to insert a new row
     * each time, so older installs can have several).
     *
     * @return array<int>
     */
    public function getPluginIdsByFolder(string $foldername): array
    {
        return $this->db->table('zp_plugins')
            ->where('foldername', $foldername)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
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
            ->delete() > 0;
    }
}
