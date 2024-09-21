<?php

namespace Leantime\Domain\Users\Repositories {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Log\Logger;
    use Illuminate\Support\Facades\Log;
    use LasseRafn\InitialAvatarGenerator\InitialAvatar;
    use LasseRafn\Initials\Initials;
    use Leantime\Core\Configuration\Environment;
    use Leantime\Core\Db\Db as DbCore;
    use Leantime\Domain\Files\Repositories\Files;
    use PDO;
    use SVG\SVG;

    /**
     *
     */
    class Users
    {
        /**
         * @access public
         * @var    string
         */
        public string $user;

        /**
         * @access public
         * @var    string
         */
        public string $lastname;

        /**
         * @access public
         * @var    string
         */
        public string $firstname;

        /**
         * @access public
         * @var    int
         */
        public int $role;

        /**
         * @access public
         * @var    string
         */
        public string $jobTitle;

        /**
         * @access public
         * @var    string
         */
        public string $jobLevel;

        /**
         * @access public
         * @var    string
         */
        public string $department;
        /**
         * @access public
         * @var    int
         */
        public int $id;

        /**
         * @access public
         * @var    array
         */
        public array $adminRoles = array(2, 4);

        /**
         * @access public
         * @var    array
         */
        public array $status = array('active' => 'label.active', 'inactive' => 'label.inactive', 'invited' => 'label.invited');

        /**
         * @access public
         * @var    DbCore|null
         */
        private ?DbCore $db;

        public Environment $config;

        /**
         * __construct - neu db connection
         *
         * @access public
         */
        public function __construct(
            Environment $config,
            DbCore $db
        ) {

            $this->db = $db;
            $this->config = $config;
        }

        /**
         * getUser - get on user from db
         *
         * @access public
         * @param  $id
         * @return array|bool
         */
        public function getUser($id): array|bool
        {

            $sql = "SELECT * FROM `zp_user` WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * getUser - get on user from db
         *
         * @access public
         * @param $hash
         * @return mixed
         */
        public function getUserBySha($hash): array|false
        {


            $sql = "SELECT * FROM `zp_user` WHERE SHA1(CONCAT(id,:sessionSecret)) = :hash";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':hash', $hash, PDO::PARAM_STR);
            $stmn->bindValue(':sessionSecret', $this->config->sessionPassword, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * getLastLogin - get the date of the last login of any user
         *
         * @access public
         * @return string|null returns datetime string with last login or null if nothing could be found
         */
        public function getLastLogin(): string|null
        {

            $sql = "SELECT  lastlogin FROM `zp_user` Order by lastlogin DESC LIMIT 1";

            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if (isset($values['lastlogin'])) {
                return $values['lastlogin'];
            }
            return null;
        }

        /**
         * getUserByEmail - get on user from db
         *
         * @param string $email
         * @param string $status
         *
         * @return array|false
         */
        public function getUserByEmail(string $email, string $status = "a"): array | false
        {
            $sql = "SELECT * FROM `zp_user` WHERE username = :email ";

            if ($status == "a") {
                $sql .= " and status = 'a'";
            }

            if ($status == "i") {
                $sql .= " and status = 'i'";
            }

            $sql .=  " LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':email', $email, PDO::PARAM_STR);
            $stmn->bindValue(':email', $email, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @return int
         */
        public function getNumberOfUsers($activeOnly = false, $includeApi = true): int
        {
            $sql = "SELECT COUNT(id) AS userCount FROM `zp_user`";
            $conditions = [];

            if ($activeOnly) {
                $conditions[] = "status = 'a'";
            }

            if ($includeApi) {
                $conditions[] = "(source != 'api' OR source IS NULL)";
            }

            foreach ($conditions as $condition) {
                $sql .= str_contains($sql, 'WHERE') ? ' AND' : ' WHERE';
                $sql .= " $condition";
            }

            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values['userCount'] ?? 0;
        }

        /**
         * getEmployees - get all employees
         *
         * @access public
         * @return array
         */
        public function getEmployees(): array
        {

            $sql = "SELECT
            zp_user.id,
            IF(zp_user.firstname IS NOT NULL, zp_user.firstname, zp_user.username) AS firstname,
            zp_user.lastname,
            zp_user.jobTitle,
            zp_user.jobLevel,
            zp_user.department,
            zp_user.modified
         FROM zp_user
            ORDER BY lastname";

            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * getAll - get all user
         *
         * @access public
         * @param bool $activeOnly
         * @return array
         */
        public function getAll($activeOnly = false): array
        {

            $query = "SELECT
                      zp_user.id,
                      lastname,
                      IF(firstname <> '', firstname, username) AS firstname,
                      role,
                      profileId,
                      status,
                      username,
                      twoFAEnabled,
                      clientId,
                      zp_clients.name AS clientName,
                      jobTitle,
                      jobLevel,
                      department,
                      zp_user.modified
                    FROM `zp_user`
                    LEFT JOIN zp_clients ON zp_clients.id = zp_user.clientId
                    WHERE !(source <=> 'api')";

            if ($activeOnly) {
                $query .= " AND status LIKE 'a' ";
            }

            $query .= " ORDER BY lastname";

            $stmn = $this->db->database->prepare($query);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $source
         * @return array|false
         */
        public function getAllBySource($source): false|array
        {

            $query = "SELECT
                      zp_user.id,
                      lastname,
                      firstname,
                      role,
                      profileId,
                      status,
                      username,
                      lastlogin,
                      createdOn,
                      jobTitle,
                      jobLevel,
                      department,
                      modified
                    FROM `zp_user`
                    WHERE source <=> :source
                    ORDER BY lastname";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':source', $source, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * getAll - get all user
         *
         * @access public
         * @param $clientId
         * @return array
         */
        public function getAllClientUsers($clientId): array
        {

            $query = "SELECT
                        zp_user.id,
                        lastname,
                        firstname,
                        role,
                        profileId,
                        status,
                        username,
                        twoFAEnabled,
                        zp_clients.name AS clientName,
                        jobTitle,
                        jobLevel,
                        department,
                        modified
                    FROM `zp_user`
                    LEFT JOIN zp_clients ON zp_clients.id = zp_user.clientId
                    WHERE clientId = :clientId
                    ORDER BY lastname";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':clientId', $clientId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $userId
         * @return bool
         */
        /**
         * @param $userId
         * @return bool
         */
        public function isAdmin($userId): bool
        {

            $sql = "SELECT role FROM zp_user WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $userId, PDO::PARAM_STR);

            $stmn->execute();
            $user = $stmn->fetch();
            $stmn->closeCursor();

            $flag = false;
            if (in_array($user['role'], $this->adminRoles)) {
                $flag = true;
            }

            return $flag;
        }

        /**
         * editUSer - edit user
         *
         * @access public
         * @param array $values
         * @param  $id
         * @return bool
         */
        public function editUser(array $values, $id): bool
        {
            if (isset($values['password']) && $values['password'] != '') {
                $chgPW = " password = :password, ";
            } else {
                $chgPW = "";
            }

            $query = "UPDATE `zp_user` SET
                firstname = :firstname,
                lastname = :lastname,
                username = :username,
                phone = :phone,
                status = :status,
                role = :role,
                hours = :hours,
                wage = :wage,
                jobTitle = :jobTitle,
                jobLevel = :jobLevel,
                department = :department,
                " . $chgPW . "
                clientId = :clientId,
                modified = :modified
             WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':firstname', $values['firstname'], PDO::PARAM_STR);
            $stmn->bindValue(':lastname', $values['lastname'], PDO::PARAM_STR);
            $stmn->bindValue(':username', $values['user'], PDO::PARAM_STR);
            $stmn->bindValue(':phone', $values['phone'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':status', $values['status'], PDO::PARAM_STR);
            $stmn->bindValue(':role', $values['role'], PDO::PARAM_STR);
            $stmn->bindValue(':hours', $values['hours'] ?? 0, PDO::PARAM_STR);
            $stmn->bindValue(':wage', $values['wage'] ?? 0, PDO::PARAM_STR);
            $stmn->bindValue(':clientId', $values['clientId'], PDO::PARAM_STR);
            $stmn->bindValue(':jobTitle', $values['jobTitle'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':jobLevel', $values['jobLevel'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':department', $values['department'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':modified', date("Y-m-d H:i:s"), PDO::PARAM_STR);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            if (isset($values['password']) && $values['password'] != '') {
                $stmn->bindValue(':password', password_hash($values['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);
            }

            $result = $stmn->execute();
            $stmn->closeCursor();

            return $result;
        }

        /**
         * usernameExist - Check if a username is already in db
         *
         * @access public
         * @param  $username
         * @param string   $userId
         * @return bool
         */
        public function usernameExist($username, string $userId = ''): bool
        {

            if ($userId != '') {
                $queryOwn = " AND id != :id ";
            } else {
                $queryOwn = "";
            }

            $query = "SELECT COUNT(username) AS numUser FROM `zp_user` WHERE username = :username " . $queryOwn . " LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':username', $username, PDO::PARAM_STR);

            if ($userId != '') {
                $stmn->bindValue(':id', $userId, PDO::PARAM_STR);
            }

            $stmn->execute();

            $result = $stmn->fetch();
            $stmn->closeCursor();

            if ($result['numUser'] == 1) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * editOwn - Edit own Userdates
         *
         * @access public
         * @param  $values
         * @param  $id
         */
        public function editOwn($values, $id): void
        {

            if (isset($values['password']) && $values['password'] != '') {
                $chgPW = " password = :password, ";
            } else {
                $chgPW = "";
            }

            $query = "UPDATE `zp_user` SET
                lastname = :lastname,
                firstname = :firstname,
                username = :username,
                " . $chgPW . "
                phone = :phone,
                notifications = :notifications,
                modified = :modified
                WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':firstname', $values['firstname'], PDO::PARAM_STR);
            $stmn->bindValue(':lastname', $values['lastname'], PDO::PARAM_STR);
            $stmn->bindValue(':username', $values['user'], PDO::PARAM_STR);
            $stmn->bindValue(':phone', $values['phone'], PDO::PARAM_STR);
            $stmn->bindValue(':notifications', $values['notifications'], PDO::PARAM_STR);
            $stmn->bindValue(':modified', date("Y-m-d H:i:s"), PDO::PARAM_STR);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            if (isset($values['password']) && $values['password'] != '') {
                $stmn->bindValue(':password', password_hash($values['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);
            }

            $stmn->execute();
            $stmn->closeCursor();
        }

        /**
         * addUser - add User to db
         *
         * @access public
         * @param array $values
         * @return false|string
         */
        public function addUser(array $values): false|string
        {

            $query = "INSERT INTO `zp_user` (
                            firstname,
                            lastname,
                            phone,
                            username,
                            role,
                            notifications,
                            clientId,
                            password,
                            source,
                            pwReset,
                            status,
                            createdOn,
                            jobTitle,
                            jobLevel,
                            department,
                            modified
                        ) VALUES (
                            :firstname,
                            :lastname,
                            :phone,
                            :user,
                            :role,
                            1,
                            :clientId,
                            :password,
                            :source,
                            :pwReset,
                            :status,
                            :createdOn,
                            :jobTitle,
                            :jobLevel,
                            :department,
                            :modified
                        )";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':firstname', $values['firstname'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':lastname', $values['lastname'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':phone', $values['phone'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':user', $values['user'], PDO::PARAM_STR);
            $stmn->bindValue(':role', $values['role'], PDO::PARAM_STR);

            $stmn->bindValue(':password', password_hash($values['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);
            $stmn->bindValue(':clientId', $values['clientId'] ?? '', PDO::PARAM_INT);

            $stmn->bindValue(':jobTitle', $values['jobTitle'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':jobLevel', $values['jobLevel'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':department', $values['department'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':createdOn', date("Y-m-d H:i:s"), PDO::PARAM_STR);
            $stmn->bindValue(':modified', date("Y-m-d H:i:s"), PDO::PARAM_STR);

            if (isset($values['source'])) {
                $stmn->bindValue(':source', $values['source'], PDO::PARAM_STR);
            } else {
                $stmn->bindValue(':source', '', PDO::PARAM_STR);
            }

            $stmn->bindValue(':pwReset', $values['pwReset'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':status', $values['status'] ?? '', PDO::PARAM_STR);

            $stmn->execute();
            $userId = $this->db->database->lastInsertId();

            $stmn->closeCursor();

            return $userId;
        }

        /**
         * deleteUser - delete user from db
         *
         * @access public
         * @param  $id
         */
        public function deleteUser($id): void
        {

            $query = "DELETE FROM `zp_user` WHERE zp_user.id = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();
        }

        /**
         * setPicture - set the profile picture for an individual
         *
         * @access public
         * @param array $_FILE
         * @param $id
         * @throws BindingResolutionException
         */
        public function setPicture(array $_FILE, $id): void
        {

            $sql = "SELECT * FROM `zp_user` WHERE id=:id";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            $files = app()->make(files::class);

            if (isset($values['profileId']) && $values['profileId'] > 0) {
                $file = $files->getFile($values['profileId']);
                $img = 'userdata/' . $file['encName'] . $file['extension'];

                $files->deleteFile($values['profileId']);
            }


            $lastId = $files->upload($_FILE, 'user', $id, true, 200, 200);

            if (isset($lastId['fileId'])) {
                $sql = 'UPDATE
                            `zp_user`
                        SET
                            profileId = :fileId,
                            modified = :modified
                        WHERE id = :userId';

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindValue(':fileId', $lastId['fileId'], PDO::PARAM_INT);
                $stmn->bindValue(':userId', $id, PDO::PARAM_INT);
                $stmn->bindValue(':modified', date("Y-m-d H:i:s"), PDO::PARAM_STR);

                $stmn->execute();
                $stmn->closeCursor();
            }
        }

        /**
         * @param $id
         * @return string[]|SVG
         * @throws BindingResolutionException
         */
        /**
         * @param $id
         * @return string[]|SVG
         * @throws BindingResolutionException
         */
        public function getProfilePicture($id): array|SVG
        {
            $value = false;
            if ($id !== false) {
                $sql = "SELECT profileId, firstname, lastname FROM `zp_user` WHERE id = :id LIMIT 1";

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindValue(':id', $id, PDO::PARAM_INT);

                $stmn->execute();
                $value = $stmn->fetch();
                $stmn->closeCursor();
            }

            try {

                $avatar = (new InitialAvatar())
                    ->fontName("Verdana")
                    ->background('#00a887')
                    ->color("#fff");

                if (empty($value)) {
                    return $avatar->name("👻")->generateSvg();
                }

            } catch (\Exception $e) {
                Log::error("Could not generate user avatar.");
                Log::error($e);
                return array("filename" => "not_found", "type" => "uploaded");
            }

            $name = $value['firstname'] . " " . $value['lastname'];

            if (empty($value['profileId'])) {

                /** @var Initials $initialsClass */
                $initialsClass = app()->make(Initials::class);
                $initialsClass->name($name);
                $imagename = $initialsClass->getInitials();

                if (!file_exists($filename = APP_ROOT . "/cache/avatars/user-" . $imagename . ".svg")) {
                    $image = $avatar->name($name)->generateSvg();

                    if (!is_writable(APP_ROOT . "/cache/avatars/")) {
                        return $image;
                    }

                    file_put_contents($filename, $image);
                }

                return array("filename" => $filename, "type" => "generated");
            }

            $files = app()->make(Files::class);
            $file = $files->getFile($value['profileId']);

            if ($file) {
                $filePath = $file['encName'] . "." . $file['extension'];
                $type = $file['extension'];

                return array("filename" => $filePath, "type" => "uploaded");
            }

            return $avatar->name("👻")->generateSvg();
        }

        /**
         * @param $id
         * @param $params
         * @return bool
         */
        public function patchUser($id, $params): bool
        {

            $sql = "UPDATE zp_user SET ";

            foreach ($params as $key => $value) {
                $sql .= DbCore::sanitizeToColumnString($key) . "=:" . DbCore::sanitizeToColumnString($key) . ", ";
            }

            $sql .= " modified =:modified ";

            $sql .= " WHERE id=:id LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->bindValue(':modified', date("Y-m-d H:i:s"), PDO::PARAM_STR);

            foreach ($params as $key => $value) {
                $cleanKey = DbCore::sanitizeToColumnString($key);
                $val = $value;
                if ($cleanKey == 'password') {
                    $val = password_hash($value, PASSWORD_DEFAULT);
                }
                $stmn->bindValue(':' . $cleanKey, $val, PDO::PARAM_STR);
            }

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;
        }

        /**
         * getUserIdByName - Get Author/User Id by first- and lastname
         *
         * @access public
         * @param string $firstname
         * @param string $lastname  Lastname
         * @return int|bool Identifier of user or false, if not found
         */
        public function getUserIdByName(string $firstname, string $lastname): int|bool
        {
            $query = "SELECT profileId FROM `zp_user` WHERE `firstname` = :firstname AND `lastname` = :lastname";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':firstname', $firstname, PDO::PARAM_STR);
            $stmn->bindValue(':lastname', $lastname, PDO::PARAM_STR);

            $stmn->execute();
            $result = $stmn->fetch();
            $stmn->closeCursor();

            return $result['profileId'] ?? false;
        }
    }

}
