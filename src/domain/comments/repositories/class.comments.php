<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;


    class comments
    {

        private $db;

        public function __construct()
        {

            $this->db = core\db::getInstance();
        }

        public function getComments($module,$moduleId,$parent = 0,$orderByState="0")
        {
			$orderBy = "DESC";

        	if ($orderByState == 1 || (isset($_SESSION["projectsettings"]['commentOrder']) && $_SESSION["projectsettings"]['commentOrder'] == 1))
			{
				$orderBy = "ASC";
			}

            $sql = "SELECT 
					comment.id, 
					comment.text, 
					comment.date, 
					DATE_FORMAT(comment.date, '%Y,%m,%e') AS timelineDate, 
					comment.moduleId, 
					comment.userId, 
					comment.commentParent,
					user.firstname, 
					user.lastname,
					user.profileId 
				FROM zp_comment as comment
					INNER JOIN zp_user as user ON comment.userId = user.id
				WHERE moduleId = :moduleId AND module = :module AND commentParent = :parent
				ORDER BY comment.date ".$orderBy;

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_INT);
            $stmn->bindvalue(':parent', $parent, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function countComments($module = null, $moduleId = null)
        {

            $sql = "SELECT count(id) as count
				FROM zp_comment as comment";

            if($module != null || $moduleId != null){
                $sql.=" WHERE ";
                if($module != null) {
                    $sql.="module = :module AND ";
                }

                if($moduleId != null) {
                    $sql.="moduleId = :moduleId AND ";
                }

                $sql.= "1=1";

            }

            $stmn = $this->db->database->prepare($sql);

            if($module != null) {
                $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            }

            if($module != null) {
                $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if(isset($values['count'])) {
                return $values['count'];
            }else{
                return 0;
            }
        }

        public function getReplies($id)
        {

            $sql = "SELECT 
					comment.id, comment.text, comment.date, comment.moduleId, comment.userId, comment.commentParent,
					user.firstname, user.lastname, user.profileId   
				FROM zp_comment as comment
				INNER JOIN zp_user as user ON comment.userId = user.id 
				WHERE commentParent = :id";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function getComment($id)
        {

            $sql = "SELECT 
					comment.id, comment.text, comment.date, comment.moduleId, comment.userId, comment.commentParent,
					user.firstname, user.lastname  
				FROM zp_comment as comment
				INNER JOIN zp_user as user ON comment.userId = user.id
				WHERE comment.id=:id";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->closeCursor();
        }

        public function addComment($values, $module)
        {

            $sql = "INSERT INTO zp_comment (
			text, userId, date, moduleId, module, commentParent
		) VALUES (:text, :userId, :date, :moduleId, :module, :commentParent)";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':moduleId', $values['moduleId'], PDO::PARAM_INT);
            $stmn->bindValue(':userId', $values['userId'], PDO::PARAM_INT);
            $stmn->bindValue(':commentParent', $values['commentParent'], PDO::PARAM_INT);
            $stmn->bindValue(':text', $values['text'], PDO::PARAM_STR);
            $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            $stmn->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);

            $result = $stmn->execute();
            $stmn->closeCursor();

            return $result;
        }

        public function deleteComment($id)
        {

            $sql = "DELETE FROM zp_comment WHERE id = :id";
            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $result = $stmn->execute();
            $stmn->closeCursor();

            return $result;
        }

        public function editComment($text, $id)
        {

            $sql = "UPDATE zp_comment SET text = :text WHERE id = :id";
            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);
            $stmn->bindValue(':text', $text, PDO::PARAM_INT);

            $result = $stmn->execute();
            $stmn->closeCursor();

            return $result;
        }

    }
}
