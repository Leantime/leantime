<?php

namespace Leantime\Domain\Files\Repositories {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Db\Db as DbCore;
    use Leantime\Core\Fileupload;
    use Leantime\Domain\Users\Repositories\Users as UserRepo;
    use PDO;

    /**
     *
     */
    class Files
    {
        private array $adminModules = array('project' => 'Projects','ticket' => 'Tickets','client' => 'Clients','lead' => 'Lead','private' => 'General'); // 'user'=>'Users',

        private array $userModules = array('project' => 'Projects','ticket' => 'Tickets','private' => 'General');

        private DbCore $db;

        /**
         * @param DbCore $db
         */
        public function __construct(DbCore $db)
        {
            $this->db = $db;
        }

        /**
         * @param $id
         * @return string[]
         * @throws BindingResolutionException
         */
        public function getModules($id): array
        {
            $users = app()->make(UserRepo::class);

            $modules = $this->userModules;
            if ($users->isAdmin($id)) {
                $modules = $this->adminModules;
            }

            return $modules;
        }

        /**
         * @param $values
         * @param $module
         * @return false|string
         */
        public function addFile($values, $module): false|string
        {


            $sql = "INSERT INTO zp_file (
					encName, realName, extension, module, moduleId, userId, date
				) VALUES (
					:encName, :realName, :extension, :module, :moduleId, :userId, NOW()
				)";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':encName', $values['encName'], PDO::PARAM_STR);
            $stmn->bindValue(':realName', $values['realName'], PDO::PARAM_STR);
            $stmn->bindValue(':extension', $values['extension'], PDO::PARAM_STR);
            $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            $stmn->bindValue(':moduleId', $values['moduleId'], PDO::PARAM_INT);
            $stmn->bindValue(':userId', $values['userId'], PDO::PARAM_INT);

            $stmn->execute();
            $stmn->closeCursor();

            return $this->db->database->lastInsertId();
        }

        /**
         * @param $id
         * @return mixed
         */
        public function getFile($id): mixed
        {

            $sql = "SELECT
					file.id, file.extension, file.realName, file.encName, file.date, file.module, file.moduleId,
					user.firstname, user.lastname
				FROM zp_file as file
				INNER JOIN zp_user as user ON file.userId = user.id
				WHERE file.id=:id";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param int $userId
         * @return array|false
         */
        public function getFiles(int $userId = 0): false|array
        {

            $sql = "SELECT
					file.id, file.moduleId, file.extension, file.realName, file.encName, file.date, file.module,
					user.firstname, user.lastname
				FROM zp_file as file
				INNER JOIN zp_user as user ON file.userId = user.id ";

            if ($userId && $userId > 0) {
                $sql .= " WHERE file.userId = " . $userId;
            }

            $sql .= " ORDER BY file.module, file.moduleId";

            $stmn = $this->db->database->prepare($sql);
            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $module
         * @return array
         */
        public function getFolders($module): array
        {

            $folders = array();
            $files = $this->getFiles(session("userdata.id"));

            $sql = match ($module) {
                'ticket' => "SELECT headline as title, id FROM zp_tickets WHERE id=:moduleId LIMIT 1",
                'client' => "SELECT name as title, id FROM zp_clients WHERE id=:moduleId LIMIT 1",
                'project' => "SELECT name as title, id FROM zp_projects WHERE id=:moduleId LIMIT 1",
                'lead' => "SELECT name as title, id FROM zp_lead WHERE id=:moduleId LIMIT 1",
                default => "SELECT headline as title, id FROM zp_tickets WHERE id=:moduleId LIMIT 1",
            };

            $stmn = $this->db->database->prepare($sql);

            $ids = array();
            foreach ($files as $file) {
                $stmn->bindValue(':moduleId', $file['moduleId'], PDO::PARAM_STR);
                $stmn->execute();
                if (!isset($ids[$file['moduleId']])) {
                    $folders[] = $stmn->fetch();
                    $ids[$file['moduleId']] = true;
                }
            }

            $stmn->closeCursor();

            return $folders;
        }

        /**
         * @param string   $module
         * @param null     $moduleId
         * @param int|null $userId
         * @return array|false
         */
        public function getFilesByModule(string $module = '', $moduleId = null, ?int $userId = 0): false|array
        {

            $sql = "SELECT
					file.id,
					file.extension,
					file.realName,
					file.encName,
					file.date,
					DATE_FORMAT(file.date,  '%Y,%m,%e') AS timelineDate,
					file.module,
					file.moduleId,
					user.firstname,
					user.lastname,
					user.id AS userId
				FROM zp_file as file

				INNER JOIN zp_user as user ON file.userId = user.id ";

            if ($module != '') {
                $sql .= " WHERE file.module=:module ";
            } else {
                $sql .= " WHERE file.module <> '' ";
            }

            if ($moduleId != null) {
                $sql .= " AND moduleId=:moduleId";
            }

            if ($userId && $userId > 0) {
                $sql .= " AND userId= :userId";
            }

            $stmn = $this->db->database->prepare($sql);
            if ($module != '') {
                $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            }

            if ($moduleId != null) {
                $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_INT);
            }

            if ($userId && $userId > 0) {
                $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $id
         * @return bool
         */
        public function deleteFile($id): bool
        {

            $sql = "SELECT encName, extension FROM zp_file WHERE id=:id";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if (isset($values['encName']) && isset($values['extension'])) {
                $file = ROOT . '/../userfiles/' . $values['encName'] . '.' . $values['extension'];
                if (file_exists($file)) {
                    unlink($file);
                }
            }

            $sql = "DELETE FROM zp_file WHERE id=:id";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            return $stmn->execute();
            $stmn->closeCursor();
        }

        /**
         * @param $file
         * @param $module
         * @param $moduleId
         * @return array|false
         * @throws BindingResolutionException
         */
        public function upload($file, $module, $moduleId): false|string|array
        {

            //Clean module mess
            if ($module == "projects") {
                $module = "project";
            }
            if ($module == "tickets") {
                $module = "ticket";
            }

            $upload = app()->make(Fileupload::class);

            $path = $file['file']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            $upload->initFile($file['file']);

            $return = false;

            if ($upload->error == '') {
                //Just something unique to avoid collision in s3 (each customer has their own folder)
                $newname = md5(session("userdata.id") . time());

                $upload->renameFile($newname);

                if ($upload->upload() === true) {
                    $values = array(
                        'encName'     => $newname,
                        'realName'     => str_replace('.' . $ext, '', $file['file']['name']),
                        'extension' => $ext,
                        'moduleId'     => $moduleId,
                        'userId'     => session("userdata.id"),
                        'module'    => $module,
                        'fileId' => '',
                    );

                    $fileAddResults = $this->addFile($values, $module);

                    if ($fileAddResults) {
                        $values['fileId'] = $fileAddResults;
                        $return = $values;
                    } else {
                        $return = false;
                    }
                } else {
                    //report($upload->error);
                    return $upload->error;
                }
            }

            return $return;
        }

        /**
         * @param $name
         * @param $url
         * @param $module
         * @param $moduleId
         * @return void
         */
        public function uploadCloud($name, $url, $module, $moduleId): void
        {

            //Add cloud stuff ehre.
        }
    }

}
