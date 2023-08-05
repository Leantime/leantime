<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class users
    {
        /**
         * @access public
         * @var    string
         */
        public $user;

        /**
         * @access public
         * @var    string
         */
        public $lastname;

        /**
         * @access public
         * @var    string
         */
        public $firstname;

        /**
         * @access public
         * @var    integer
         */
        public $role;

       /**
         * @access public
         * @var    string
         */
        public $jobTitle;

        /**
         * @access public
         * @var    string
         */
        public $jobLevel;

        /**
         * @access public
         * @var    string
         */
        public $department;
        /**
         * @access public
         * @var    integer
         */
        public $id;

        /**
         * @access public
         * @var    array
         */
        public $adminRoles = array(2, 4);

        /**
         * @access public
         * @var    array
         */
        public $status = array('active' => 'label.active', 'inactive' => 'label.inactive', 'invited' => 'label.invited');

        /**
         * @access public
         * @var    object
         */
        private $db;

        public \leantime\core\environment $config;

        /**
         * __construct - neu db connection
         *
         * @access public
         */
        public function __construct(
            \leantime\core\environment $config,
            \leantime\core\db $db
        ) {

            $this->db = $db;
            $this->config = $config;
        }

        /**
         * getUser - get on user from db
         *
         * @access public
         * @param  $id
         * @return array|boolean
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
         * @param  $id
         * @return array
         */
        public function getUserBySha($hash)
        {


            $sql = "SELECT * FROM `zp_user` WHERE SHA1(CONCAT(id,:sessionSecret)) = :hash";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':hash', $hash, PDO::PARAM_STR);
            $stmn->bindValue(':sessionSecret', $this->config->sessionpassword, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * getLastLogin - get the date of the last login of any user
         *
         * @access public
         * @param  $id
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
         * @access public
         * @param  $id
         * @return array
         */
        public function getUserByEmail($email): array | false
        {

            $sql = "SELECT * FROM `zp_user` WHERE username = :email AND status LIKE 'a' LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':email', $email, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;
        }

        public function getNumberOfUsers(): int
        {

            $sql = "SELECT COUNT(id) AS userCount FROM `zp_user`";

            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if (isset($values['userCount']) === true) {
                return $values['userCount'];
            } else {
                return 0;
            }
        }

        /**
         * getEmployees - get all employees
         *
         * @access public
         * @return array
         */
        public function getEmployees()
        {

            $sql = "SELECT
			zp_user.id,
			IF(zp_user.firstname IS NOT NULL, zp_user.firstname, zp_user.username) AS firstname,
			zp_user.lastname,
			zp_user.jobTitle,
			zp_user.jobLevel,
			zp_user.department
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
         * @return array
         */
        public function getAll($activeOnly = false)
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
                      department
					FROM `zp_user`
					LEFT JOIN zp_clients ON zp_clients.id = zp_user.clientId
                    WHERE !(source <=> 'api')";

                if($activeOnly == true) {
                        $query .= " AND status LIKE 'a' ";
                    }

            $query .=" ORDER BY lastname";

            $stmn = $this->db->database->prepare($query);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function getAllBySource($source)
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
                      department
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
         * @return array
         */
        public function getAllClientUsers($clientId)
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
                        department
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
         * @param  array $values
         * @param  $id
         */
        public function editUser(array $values, $id)
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
				clientId = :clientId
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
         * @param  $userId
         * @return boolean
         */
        public function usernameExist($username, $userId = ''): bool
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
        public function editOwn($values, $id)
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
				notifications = :notifications
				WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':firstname', $values['firstname'], PDO::PARAM_STR);
            $stmn->bindValue(':lastname', $values['lastname'], PDO::PARAM_STR);
            $stmn->bindValue(':username', $values['user'], PDO::PARAM_STR);
            $stmn->bindValue(':phone', $values['phone'], PDO::PARAM_STR);
            $stmn->bindValue(':notifications', $values['notifications'], PDO::PARAM_STR);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            if ($values['password'] != '') {
                $stmn->bindValue(':password', password_hash($values['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);
            }

            $stmn->execute();
            $stmn->closeCursor();
        }

        /**
         * addUser - add User to db
         *
         * @access public
         * @param  array $values
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
                            department
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
						    NOW(),
                            :jobTitle,
                            :jobLevel,
                            :department
						)";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':firstname', $values['firstname'], PDO::PARAM_STR);
            $stmn->bindValue(':lastname', $values['lastname'], PDO::PARAM_STR);
            $stmn->bindValue(':phone', $values['phone'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':user', $values['user'], PDO::PARAM_STR);
            $stmn->bindValue(':role', $values['role'], PDO::PARAM_STR);

            $stmn->bindValue(':password', password_hash($values['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);
            $stmn->bindValue(':clientId', $values['clientId'], PDO::PARAM_INT);

            $stmn->bindValue(':jobTitle', $values['jobTitle'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':jobLevel', $values['jobLevel'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':department', $values['department'] ?? '', PDO::PARAM_STR);

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
        public function deleteUser($id)
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
         * @param  string
         */
        public function setPicture($_FILE, $id)
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
                $sql = 'UPDATE `zp_user` SET profileId = :fileId WHERE id = :userId';

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindValue(':fileId', $lastId['fileId'], PDO::PARAM_INT);
                $stmn->bindValue(':userId', $id, PDO::PARAM_INT);

                $stmn->execute();
                $stmn->closeCursor();
            }
        }

        public function getProfilePicture($id)
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

            if ($value !== false && $value['profileId'] != '') {
                $files = app()->make(files::class);
                $file = $files->getFile($value['profileId']);

                if ($file) {
                    $return = $file['encName'] . "." . $file['extension'];
                }

                $filePath = ROOT . "/../userfiles/" . $file['encName'] . "." . $file['extension'];
                $type = $file['extension'];

                return array("filename" => $return, "type" => "uploaded");
            } elseif (isset($value['profileId']) && $value['profileId'] == '') {
                $imagename = md5($value['firstname'] . " " . $value['lastname']);

                if (file_exists(APP_ROOT . "/cache/avatars/" . $imagename . ".png")) {
                    return array("filename" => APP_ROOT . "/cache/avatars/" . $imagename . ".png", "type" => "generated");
                } else {
                    $avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();
                    $image = $avatar
                        ->name($value['firstname'] . " " . $value['lastname'])
                        ->font(ROOT . '/dist/fonts/roboto/Roboto-Regular.woff2')
                        ->fontSize(0.5)
                        ->size(96)
                        ->background('#81B1A8')->color("#fff");

                    if (is_writable(APP_ROOT . "/cache/avatars/")) {
                        $image->generate()->save(APP_ROOT . "/cache/avatars/" . $imagename . ".png", 100, "png");
                        return array("filename" => APP_ROOT . "/cache/avatars/" . $imagename . ".png", "type" => "generated");
                    } else {
                        return $image->generateSVG();
                    }
                }
            } else {
                //USer doesn't exist for whatever reason. Return ghost. Boo
                $avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();
                $image = $avatar
                    ->name("👻")
                    ->font(ROOT . '/dist/fonts/roboto/Roboto-Medium-webfont.woff')
                    ->fontName("Verdana")
                    ->background('#81B1A8')->color("#fff")
                    ->generateSvg();

                return $image;
            }

            return $return;
        }

        public function patchUser($id, $params)
        {

            $sql = "UPDATE zp_user SET ";

            foreach ($params as $key => $value) {
                $sql .= "" . core\db::sanitizeToColumnString($key) . "=:" . core\db::sanitizeToColumnString($key) . ", ";
            }

            $sql .= "id=:id WHERE id=:id2 LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->bindValue(':id2', $id, PDO::PARAM_STR);

            foreach ($params as $key => $value) {
                $cleanKey = core\db::sanitizeToColumnString($key);
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
         * @param  string $firstnam Firstname
         * @param  string $lastname Lastname
         * @return integer|boolean Identifier of user or false, if not found
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
