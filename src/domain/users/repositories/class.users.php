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
        public $status = array('active' => 'label.active', 'inactive' => 'label.inactive');

        /**
         * @access public
         * @var    object
         */
        private $db;

        /**
         * __construct - neu db connection
         *
         * @access public
         */
        public function __construct()
        {

            $this->db = core\db::getInstance();

        }

        /**
         * getUser - get on user from db
         *
         * @access public
         * @param  $id
         * @return array
         */
        public function getUser($id)
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
         * getUserByEmail - get on user from db
         *
         * @access public
         * @param  $id
         * @return array
         */
        public function getUserByEmail($email)
        {

            $sql = "SELECT * FROM `zp_user` WHERE username = :email LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':email', $email, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;
        }


        public function getNumberOfUsers()
        {

            $sql = "SELECT COUNT(id) AS userCount FROM `zp_user`";

            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if(isset($values['userCount']) === true) {
                return $values['userCount'];
            }else{
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
			zp_user.firstname,
			zp_user.lastname,
			zp_roles.roleDescription,
			zp_roles.roleName AS role
		 FROM zp_user LEFT JOIN zp_roles ON zp_user.role = zp_roles.id WHERE zp_roles.roleName IN('developer','admin','manager') ORDER BY lastname";

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
        public function getAll()
        {

            $query = "SELECT 
                      zp_user.id, 
                      lastname, 
                      firstname, 
                      role, 
                      profileId, 
                      username,
                      twoFAEnabled,
                      clientId,
                      zp_clients.name AS clientName
					FROM `zp_user` 
					LEFT JOIN zp_clients ON zp_clients.id = zp_user.clientId
					ORDER BY lastname";

            $stmn = $this->db->database->prepare($query);

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
                        username,
                        twoFAEnabled,
                        zp_clients.name AS clientName
					FROM `zp_user` 
					LEFT JOIN zp_clients ON zp_clients.id = zp_user.id
					WHERE clientId = :clientId 
					ORDER BY lastname";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':clientId', $clientId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function isAdmin($userId)
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

            $query = "UPDATE `zp_user` SET
				firstname = :firstname,
				lastname = :lastname,
				username = :username,
				phone = :phone,
				status = :status,
				role = :role,
				hours = :hours,
				wage = :wage,
				clientId = :clientId,
				password = :password
				
			 WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':firstname', $values['firstname'], PDO::PARAM_STR);
            $stmn->bindValue(':lastname', $values['lastname'], PDO::PARAM_STR);
            $stmn->bindValue(':username', $values['user'], PDO::PARAM_STR);
            $stmn->bindValue(':phone', $values['phone'], PDO::PARAM_STR);
            $stmn->bindValue(':status', $values['status'], PDO::PARAM_STR);
            $stmn->bindValue(':role', $values['role'], PDO::PARAM_STR);
            $stmn->bindValue(':hours', $values['hours'], PDO::PARAM_STR);
            $stmn->bindValue(':wage', $values['wage'], PDO::PARAM_STR);
            $stmn->bindValue(':clientId', $values['clientId'], PDO::PARAM_STR);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
			$stmn->bindValue(':password', $values['password'], PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();

        }

        /**
         * usernameExist - Check if a username is already in db
         *
         * @access public
         * @param  $username
         * @param  $userId
         * @return boolean
         */
        public function usernameExist($username, $userId ='')
        {

            if ($userId != '') {

                $queryOwn = " AND id != :id ";

            } else {

                $queryOwn = "";
            }

            $query = "SELECT COUNT(username) AS numUser FROM `zp_user` WHERE username = :username ".$queryOwn." LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':username', $username, PDO::PARAM_STR);

            if ($userId != '') {
                $stmn->bindValue(':id', $userId, PDO::PARAM_STR);
            }

            $stmn->execute();

            $result = $stmn->fetch();
            $stmn->closeCursor();



            if($result['numUser'] == 1) {

                return true;

            }else{

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

            if($values['password'] != '') {

                $chgPW = " password = :password, ";

            }else{

                $chgPW = "";

            }

            $query = "UPDATE `zp_user` SET
				lastname = :lastname, 
				firstname = :firstname, 
				username = :username, 
				".$chgPW."
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

            if($values['password'] != '') {

                $stmn->bindValue(':password', $values['password'], PDO::PARAM_STR);

            }

            $stmn->execute();
            $stmn->closeCursor();


        }


        /**
         * addUser - add User to db with postback test
         *
         * @access public
         * @param  array $values
         */
        public function addUser(array $values)
        {

            $query = "INSERT INTO `zp_user` (
							firstname, 
							lastname, 
							phone, 
							username, 
							role,
					        notifications,
							clientId, 
							password
						) VALUES (
							:firstname,
							:lastname,
							:phone,
							:user,
							:role,
							1,
							:clientId,
							:password
						)";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':firstname', $values['firstname'], PDO::PARAM_STR);
            $stmn->bindValue(':lastname', $values['lastname'], PDO::PARAM_STR);
            $stmn->bindValue(':phone', $values['phone'], PDO::PARAM_STR);
            $stmn->bindValue(':user', $values['user'], PDO::PARAM_STR);
            $stmn->bindValue(':role', $values['role'], PDO::PARAM_STR);

            $stmn->bindValue(':password', $values['password'], PDO::PARAM_STR);
            $stmn->bindValue(':clientId', $values['clientId'], PDO::PARAM_STR);


            $stmn->execute();
            $userId = $this->db->database->lastInsertId();

            $stmn->closeCursor();

            return  $userId;

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
        public function setPicture($_FILE,$id)
        {

            $sql = "SELECT * FROM `zp_user` WHERE id=:id";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            $files = new files();

            if (isset($values['profileId']) && $values['profileId'] > 0) {
                $file = $files->getFile($values['profileId']);
                $img = 'userdata/'.$file['encName'].$file['extension'];

                $files->deleteFile($values['profileId']);

            }


            $lastId = $files->upload($_FILE, 'user', $id, true, 200, 200);

            if(isset($lastId['fileId'])) {
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

            $sql = "SELECT profileId FROM `zp_user` WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $value = $stmn->fetch();
            $stmn->closeCursor();

            $files = new files();
            $file = $files->getFile($value['profileId']);

            $return = BASE_URL.'/images/default-user.png';
            if ($file) {
                $return = BASE_URL."/download.php?module=".$file['module'] ."&encName=".$file['encName']."&ext=".$file['extension']."&realName=".$file['realName'];
            }

            return $return;
        }


        public function patchUser($id,$params)
        {

            $sql = "UPDATE zp_user SET ";

            foreach($params as $key=>$value){
                $sql .= "".core\db::sanitizeToColumnString($key)."=:".core\db::sanitizeToColumnString($key).", ";
            }

            $sql .= "id=:id WHERE id=:id LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            foreach($params as $key=>$value){
                $stmn->bindValue(':'.core\db::sanitizeToColumnString($key), $value, PDO::PARAM_STR);
            }

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;
        }

    }

}
