<?php
namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class files
    {

        private $adminModules = array('project'=>'Projects','ticket'=>'Tickets','client'=>'Clients','lead' => 'Lead','private'=>'General'); // 'user'=>'Users',

        private $userModules = array('project'=>'Projects','ticket'=>'Tickets','private'=>'General');

        public function __construct()
        {

            $this->db = core\db::getInstance();
        }

        public function getModules($id)
        {

            $users = new users();

            $modules = $this->userModules;
            if ($users->isAdmin($id)) {
                $modules = $this->adminModules;
            }

            return $modules;
        }

        public function addFile($values,$module)
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

        public function getFile($id)
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

        public function getFiles($userId = 0)
        {

            $sql = "SELECT 
					file.id, file.moduleId, file.extension, file.realName, file.encName, file.date, file.module, 
					user.firstname, user.lastname  
				FROM zp_file as file
				INNER JOIN zp_user as user ON file.userId = user.id ";

            if ($userId && $userId > 0) {
                $sql .= " WHERE file.userId = ".$userId;
            }

            $sql .= " ORDER BY file.module, file.moduleId";

            $stmn = $this->db->database->prepare($sql);
            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function getFolders($module)
        {

            $folders = array();
            $files = $this->getFiles($_SESSION['userdata']['id'], true);

            switch($module) {
            case 'ticket':
                $sql = "SELECT headline as title, id FROM zp_tickets WHERE id=:moduleId LIMIT 1";
                break;
            case 'client':
                $sql = "SELECT name as title, id FROM zp_clients WHERE id=:moduleId LIMIT 1";
                break;
            case 'project':
                $sql = "SELECT name as title, id FROM zp_projects WHERE id=:moduleId LIMIT 1";
                break;
            case 'lead':
                $sql = "SELECT name as title, id FROM zp_lead WHERE id=:moduleId LIMIT 1";
                break;
            default:
                $sql = "SELECT headline as title, id FROM zp_tickets WHERE id=:moduleId LIMIT 1";
                break;
            }

            $stmn = $this->db->database->prepare($sql);

            $ids = array();
            foreach($files as $file) {
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

        public function getFilesByModule($module = '', $moduleId = null, $userId = 0)
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

            if ($module!='') {
                $sql .= " WHERE file.module=:module ";
            } else {
                $sql .= " WHERE file.module <> '' ";
            }

            if ($moduleId!=null) {
                $sql .= " AND moduleId=:moduleId";
            }

            if($userId && $userId > 0) {
                $sql .= " AND userId=".$userId;
            }

            $stmn = $this->db->database->prepare($sql);
            if ($module!='') {
                $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            }

            if ($moduleId!=null) {
                $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function deleteFile($id)
        {

            $sql = "SELECT encName, extension FROM zp_file WHERE id=:id";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if (isset($values['encName']) && isset($values['extension'])) {

	            $file = ROOT . '/../userfiles/'.$values['encName'].'.'.$values['extension'];
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

        public function upload($file,$module,$moduleId)
        {

            $upload = new core\fileupload();

            $path = $file['file']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);

            $upload->initFile($file['file']);

            $return = false;

            if ($upload->error == '') {

                //Just something unique to avoid collision in s3 (each customer has their own folder)
                $newname = md5($_SESSION['userdata']['id'].time());

                $upload->renameFile($newname);

                if ($upload->upload()===true) {

                    $values = array(
                        'encName'     => $newname,
                        'realName'     => str_replace('.'.$ext, '', $file['file']['name']),
                        'extension' => $ext,
                        'moduleId'     => $moduleId,
                        'userId'     => $_SESSION['userdata']['id'],
                        'fileId' => ''
                    );

                    $fileAddResults = $this->addFile($values, $module);

                    if($fileAddResults) {
                        $values['fileId'] = $fileAddResults;
                        $return = $values;
                    }else{
                        $return = false;
                    }

                }
            }

            return $return;
        }

        public function uploadCloud($name, $url, $module, $moduleId){

           //Add cloud stuff ehre.

        }

    }

}
