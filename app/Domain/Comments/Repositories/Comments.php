<?php

namespace Leantime\Domain\Comments\Repositories {

    use Leantime\Core\Db as DbCore;
    use PDO;

    /**
     *
     */
    class Comments
    {
        private DbCore $db;

        /**
         * @param DbCore $db
         */
        public function __construct(DbCore $db)
        {
            $this->db = $db;
        }

        /**
         * @param $module
         * @param $moduleId
         * @param $parent
         * @param $orderByState
         * @return array|false
         */
        /**
         * @param $module
         * @param $moduleId
         * @param int      $parent
         * @param string   $orderByState
         * @return array|false
         */
        public function getComments($module, $moduleId, int $parent = 0, string $orderByState = "0"): false|array
        {
            $orderBy = "DESC";

            if ($orderByState == 1) {
                $orderBy = "ASC";
            }

            $sql = "SELECT
					comment.id,
					comment.text,
					comment.date,
					comment.moduleId,
					comment.userId,
					comment.commentParent,
                    comment.status,
					user.firstname,
					user.lastname,
					user.profileId,
					user.modified AS userModified,
                    replies.id AS repliesId,
					replies.text AS repliesText,
					replies.date AS repliesDate,
					replies.moduleId AS repliesModuleId,
					replies.userId AS repliesUserId,
					replies.commentParent AS repliesCommentParent,
                    replies.status AS repliesStatus,
					replyUser.firstname AS repliesFirstname,
					replyUser.lastname AS repliesLastname,
					replyUser.profileId AS repliesProfileId,
					replyUser.modified AS repliesUserModified
				FROM zp_comment as comment
				LEFT JOIN zp_user as user ON comment.userId = user.id
				LEFT JOIN zp_comment as replies ON comment.id = replies.commentParent
				LEFT JOIN zp_user as replyUser ON replies.userId = replyUser.id
				WHERE comment.moduleId = :moduleId AND comment.module = :module AND comment.commentParent = :parent
				ORDER BY comment.date " . $orderBy;

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_INT);
            $stmn->bindvalue(':parent', $parent, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $module
         * @param $moduleId
         * @return int|mixed
         */
        /**
         * @param $module
         * @param $moduleId
         * @return int|mixed
         */
        public function countComments($module = null, $moduleId = null): mixed
        {

            $sql = "SELECT count(id) as count
				FROM zp_comment as comment";

            if ($module != null || $moduleId != null) {
                $sql .= " WHERE ";
                if ($module != null) {
                    $sql .= "module = :module AND ";
                }

                if ($moduleId != null) {
                    $sql .= "moduleId = :moduleId AND ";
                }

                $sql .= "1=1";
            }

            $stmn = $this->db->database->prepare($sql);

            if ($module != null) {
                $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            }

            if ($moduleId != null) {
                $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values['count'] ?? 0;
        }

        /**
         * @param $id
         * @return array|false
         */
        /**
         * @param $id
         * @return array|false
         */
        public function getReplies($id): false|array
        {

            $sql = "SELECT
					comment.id,
					comment.text,
					comment.date,
					comment.moduleId,
					comment.userId,
					comment.commentParent,
					user.firstname,
					user.lastname,
					user.profileId,
					user.modified AS userModified
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

        /**
         * @param $id
         * @return void
         */
        /**
         * @param $id
         * @return void
         */
        public function getComment($id): void
        {

            $sql = "SELECT
					comment.id, comment.text, comment.date, comment.moduleId, comment.userId, comment.commentParent, comment.status,
					user.firstname, user.lastname
				FROM zp_comment as comment
				INNER JOIN zp_user as user ON comment.userId = user.id
				WHERE comment.id=:id";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->closeCursor();
        }

        /**
         * @param $values
         * @param $module
         * @return false|string
         */
        /**
         * @param $values
         * @param $module
         * @return false|string
         */
        public function addComment($values, $module): false|string
        {

            $sql = "INSERT INTO zp_comment (
			text, userId, date, moduleId, module, commentParent, status
		) VALUES (:text, :userId, :date, :moduleId, :module, :commentParent, :status)";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':moduleId', $values['moduleId'], PDO::PARAM_INT);
            $stmn->bindValue(':userId', $values['userId'], PDO::PARAM_INT);
            $stmn->bindValue(':commentParent', $values['commentParent'], PDO::PARAM_INT);
            $stmn->bindValue(':text', $values['text'], PDO::PARAM_STR);
            $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            $stmn->bindValue(':date', $values["date"], PDO::PARAM_STR);
            $stmn->bindValue(':status', $values['status'] ?? '', PDO::PARAM_STR);

            $result = $stmn->execute();

            $insertId = $this->db->database->lastInsertId();

            $stmn->closeCursor();

            if ($result) {
                return $insertId;
            } else {
                return false;
            }
        }

        /**
         * @param $id
         * @return bool
         */
        /**
         * @param $id
         * @return bool
         */
        public function deleteComment($id): bool
        {

            $sql = "DELETE FROM zp_comment WHERE id = :id";
            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $result = $stmn->execute();
            $stmn->closeCursor();

            return $result;
        }

        /**
         * @param $text
         * @param $id
         * @return bool
         */
        /**
         * @param $text
         * @param $id
         * @return bool
         */
        public function editComment($text, $id): bool
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
