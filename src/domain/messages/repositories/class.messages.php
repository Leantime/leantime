<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class messages
    {

        public function __construct()
        {

            $this->db = core\db::getInstance();
        }

        public function getInbox($id, $limit = 0, $unread = false)
        {

            $and = '';
            if ($unread!==false) {
                $and = "AND msg.read=:read";
            }

            $sql = "SELECT msg.id, msg.subject, msg.content, msg.date_sent, msg.parent_id, msg.last_message, msg.read,
					user.firstname, user.lastname, user.username, user.id as user_id
				 FROM zp_message as msg 
				 INNER JOIN zp_user as user ON msg.from_id = user.id 
					WHERE to_id=:id AND last_message=1 ".$and." AND from_id != :id
				 ORDER BY msg.id DESC";

            if ($limit != 0 ) {
                $sql .= " LIMIT :limit";
            }

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            if ($limit != 0 ) {
                $stmn->bindValue(':limit', $limit, PDO::PARAM_INT);
            }

            if($unread!==false) {
                $stmn->bindValue(':read', $unread, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function getMessageChain($id,$parentId)
        {

            $sql = "SELECT msg.id, msg.subject, msg.content, msg.date_sent, msg.parent_id,
					   fromUser.firstname as fromFirstname, fromUser.lastname as fromLastname, fromUser.username as fromUsername, fromUser.id as fromUserId,
					   toUser.firstname as toFirstname, toUser.lastname as toLastName, toUser.username as toUsername, toUser.id as toUserId
				FROM zp_message as msg
				INNER JOIN zp_user as fromUser ON msg.from_id = fromUser.id
				INNER JOIN zp_user as toUser ON msg.to_id = toUser.id
				WHERE (msg.parent_id=:parent_id OR msg.id=:parent_id) OR (msg.parent_id=:id OR msg.id=:id)
				ORDER BY id ASC";

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->bindValue(':id', $id, PDO::PARAM_INT);
            $stmn->bindValue(':parent_id', $parentId, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            //        var_dump($id); var_dump($parentId); var_dump($values); die();


            return $values;
        }

        public function getReplies($parentId)
        {

            $sql = "SELECT msg.id, msg.subject, msg.content, msg.date_sent, msg.parent_id,
					   fromUser.firstname as fromFirstname, fromUser.lastname as fromLastname, fromUser.username as fromUsername, fromUser.id as fromUserId,
					   toUser.firstname as toFirstname, toUser.lastname as toLastName, toUser.username as toUsername, toUser.id as toUserId
				FROM zp_message as msg
				INNER JOIN zp_user as fromUser ON msg.from_id = fromUser.id
				INNER JOIN zp_user as toUser ON msg.to_id = toUser.id
				WHERE parent_id=:id";

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->bindValue(':id', $parentId, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function getMessages($id, $limit = 9999)
        {

            $sql = "SELECT DISTINCT msg.id, msg.parent_id, msg.subject, msg.content, msg.date_sent,  msg.last_message,
					user.firstname, user.lastname, user.username, user.id as user_id
				 FROM zp_message as msg 
				 INNER JOIN zp_user as user ON msg.to_id = user.id
				 WHERE msg.from_id=:id OR msg.to_id=:id
				 	AND msg.last_message = 1
				 ORDER BY msg.id DESC LIMIT :limit";

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->bindValue(':id', $id, PDO::PARAM_INT);
            $stmn->bindValue(':limit', $limit, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            $returnValues = array();
            foreach ($values as $value) {
                $returnValues[$value['id']] = $value;
            }

            return $returnValues;
        }

        public function getSent($id)
        {

            $sql = "SELECT msg.id, msg.subject, msg.content, msg.date_sent, msg.parent_id, msg.last_message,
					user.firstname, user.lastname, user.username, user.id as user_id
				 FROM zp_message as msg 
				 INNER JOIN zp_user as user ON msg.to_id = user.id
				 WHERE msg.from_id=:id 
				 	AND msg.last_message = 1
				 ORDER BY msg.id DESC";

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function hasReplies($id)
        {

            $sql = "SELECT id FROM zp_message WHERE parent_id=:id LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            if ($values!=null) {
                return true;
            } else {
                return false;
            }
        }

        public function isReply($parentId)
        {

            $sql = "SELECT id FROM zp_message WHERE id=:id LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->bindValue(':id', $parentId, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            $return = false;
            if(count($values)) {
                $return = true;
            }

            return $return;
        }

        public function sendMessage($values)
        {

            $sql = 'INSERT INTO zp_message (
					to_id,
					from_id,
					subject,
					content,
					date_sent,
					last_message
				) VALUES (
					:to,
					:from,
					:subject,
					:content,
					NOW(),
					1
				)';

            $stmn = $this->db->{'database'}->prepare($sql);

            if (strpos($values['to_id'], ',') !== false) {
                $users = explode(',', $values['to_id']);
            } else {
                $users = array($values['to_id']);
            }

            foreach ($users as $to) {
                if ($to && $to > 0) {
                    $stmn->bindValue(':to', $to, PDO::PARAM_INT);
                    $stmn->bindValue(':from', $values['from_id'], PDO::PARAM_INT);
                    $stmn->bindValue(':subject', $values['subject'], PDO::PARAM_STR);
                    $stmn->bindValue(':content', $values['content'], PDO::PARAM_STR);

                    $query = $stmn->execute();
                }
            }

            $stmn->closeCursor();

            $lastID =  $this->db->{'database'}->lastInsertId();
        }

        public function getParent($id)
        {

            $sql = 'SELECT parent_id from zp_message WHERE id=:id';

            $stmn = $this->db->{'database'}->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $value = $stmn->fetch();
            $stmn->closeCursor();

            if (!isset($value['parent_id'])) {
                $value = false;
            }

            return $value['parent_id'];
        }

        public function reply($values, $parentId)
        {

            if (strpos($values['to_id'], ',') !== false) {
                $users = explode($values['to_id']);
            } else {
                $users = array($values['to_id']);
            }

            $this->db->{'database'}->beginTransaction();

            $parent = $this->getParent($parentId);
            if ($parent!=false) {
                $parentId = $parent;

                $update = "UPDATE zp_message SET last_message = 0 WHERE parent_id = :parentId OR id = :parentId";

                $stmn = $this->db->{'database'}->prepare($update);
                $stmn->bindValue(':parentId', $parentId, PDO::PARAM_STR);

                $stmn->execute();
                $stmn->closeCursor();
            }

            $sql = 'SELECT subject FROM zp_message WHERE id=:id';

            $stmn = $this->db->{'database'}->prepare($sql);
            $stmn->bindValue(':id', $parentId, PDO::PARAM_INT);

            $stmn->execute();
            $value = $stmn->fetch();
            $stmn->closeCursor();

            $subject = 'Reply: ' . $value['subject'];

            $insert = 'INSERT INTO zp_message (
					to_id,
					from_id,
					parent_id,
					subject,
					content,
					date_sent,
					last_message
				) VALUES (
					:to,
					:from,
					:parent,
					:subject,
					:content,
					NOW(),
					1
				)';


            $stmn = $this->db->{'database'}->prepare($insert);

            foreach($users as $to) {
                if ($to && $to > 0) {
                    $stmn->bindValue(':to', $to, PDO::PARAM_INT);
                    $stmn->bindValue(':from', $values['from_id'], PDO::PARAM_INT);
                    $stmn->bindValue(':parent', $parentId, PDO::PARAM_INT);
                    $stmn->bindValue(':subject', $subject, PDO::PARAM_STR);
                    $stmn->bindValue(':content', $values['content'], PDO::PARAM_STR);

                    $stmn->execute();
                }
            }
            $stmn->closeCursor();


            $this->db->{'database'}->commit();


        }

        public function deleteMessage($id)
        {


        }

        public function markAsRead($id)
        {

            $update = "UPDATE `zp_message`  
					SET zp_message.read=1 
					WHERE id=:id";

            $stmn = $this->db->{'database'}->prepare($update);

            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->closeCursor();
        }

        public function getPeople()
        {

            $sql = "SELECT 
			DISTINCT(friends.userId) AS friendId,
			zp_user.*
			
			FROM zp_relationuserproject AS relation
		
		LEFT JOIN zp_relationuserproject AS friends ON relation.projectId = friends.projectId
		LEFT JOIN zp_user ON zp_user.id =  friends.userId
		WHERE relation.userId = '".$_SESSION['userdata']['id']."' AND friends.userId <> '".$_SESSION['userdata']['id']."' ";

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

    }
}
