<?php

namespace Leantime\Domain\Plugins\Repositories {

    use Leantime\Core\Db\Db as DbCore;
    use Leantime\Domain\Plugins\Models\InstalledPlugin;
    use PDO;

    /**
     *
     */
    class Plugins
    {
        private DbCore $db;

        /**
         * __construct - get database connection
         *
         * @access public
         */
        public function __construct(DbCore $db)
        {
            $this->db = $db;
        }

        /**
         * @param $enabledOnly
         * @return array|false
         */
        public function getAllPlugins(bool $enabledOnly = true): false|array
        {
            $query = "SELECT
                id,
                name,
                enabled,
                description,
                version,
                installdate,
                foldername,
                homepage,
                authors,
                format,
                license
            FROM zp_plugins";

            if ($enabledOnly) {
                $query .= " WHERE enabled = true";
            }

            $query .= " GROUP BY name ";

            $stmn = $this->db->database->prepare($query);

            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, InstalledPlugin::class);
            $allPlugins = $stmn->fetchAll();

            foreach ($allPlugins as &$row) { // Use reference so we can modify in place
                $row->authors = json_decode($row->authors);
            }

            return $allPlugins;
        }

        /**
         * @param int $id
         * @return \Leantime\Domain\Plugins\Models\InstalledPlugin|false
         */
        public function getPlugin(int $id): InstalledPlugin|false
        {

            $query = "SELECT
                    id,
                  name,
                  enabled,
                  description,
                  version,
                  installdate,
                  foldername,
                  homepage,
                  authors,
                  license,
                  format

                FROM zp_plugins WHERE id = :id";


            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, InstalledPlugin::class);
            $plugin = $stmn->fetch();

            return $plugin;
        }

        /**
         * @param \Leantime\Domain\Plugins\Models\InstalledPlugin $plugin
         * @return false|string
         */
        public function addPlugin(\Leantime\Domain\Plugins\Models\InstalledPlugin $plugin): false|string
        {

            $sql = "INSERT INTO zp_plugins (
                name,
                enabled,
                description,
                version,
                installdate,
                foldername,
                homepage,
                authors,
                license,
                format
            ) VALUES (
                :name,
                :enabled,
                :description,
                :version,
                :installdate,
                :foldername,
                :homepage,
                :authors,
                :license,
                :format
            )";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':name', $plugin->name, PDO::PARAM_STR);
            $stmn->bindValue(':enabled', $plugin->enabled, PDO::PARAM_STR);
            $stmn->bindValue(':description', $plugin->description, PDO::PARAM_STR);
            $stmn->bindValue(':version', $plugin->version, PDO::PARAM_STR);
            $stmn->bindValue(':installdate', $plugin->installdate, PDO::PARAM_STR);
            $stmn->bindValue(':foldername', $plugin->foldername, PDO::PARAM_STR);
            $stmn->bindValue(':homepage', $plugin->homepage, PDO::PARAM_STR);
            $stmn->bindValue(':authors', $plugin->authors, PDO::PARAM_STR);
            $stmn->bindValue(':license', $plugin->license ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':format', $plugin->format ?? 'folder', PDO::PARAM_STR);

            $stmn->execute();

            $id = $this->db->database->lastInsertId();
            $stmn->closeCursor();

            return $id;
        }

        /**
         * @param int $id
         * @return bool
         */
        public function enablePlugin(int $id): bool
        {

            $sql = "UPDATE zp_plugins
                   SET enabled = 1
                WHERE id = :id
            ";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $result = $stmn->execute();

            $stmn->closeCursor();

            return $result;
        }

        /**
         * @param int $id
         * @return bool
         */
        /**
         * @param int $id
         * @return bool
         */
        public function disablePlugin(int $id): bool
        {

            $sql = "UPDATE zp_plugins
                   SET enabled = 0
                WHERE id = :id
            ";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $result = $stmn->execute();

            $stmn->closeCursor();

            return $result;
        }

        /**
         * @param int $id
         * @return bool
         */
        /**
         * @param int $id
         * @return bool
         */
        public function removePlugin(int $id): bool
        {

            $sql = "DELETE FROM zp_plugins
                WHERE id = :id LIMIT 1
            ";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $result = $stmn->execute();

            $stmn->closeCursor();

            return $result;
        }
    }

}
