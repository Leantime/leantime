<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class reactions
    {
        /**
         * @access private
         * @var    core\db $db db object
         */
        private core\db $db;

        /**
         * @access public
         * @var    array $reactionTypes list of available reactions by reaction type
         */
        public array $reactionTypes = array(
            "sentimentReactions" => array(
                "like" => "👍",
                "anger" => "😡",
                "love" => "❤",
                "support" => "💯",
                "celebrate" => "🎉",
                "interesting" => "💡",
                "sad" => "😥",
                "funny" => "😂"
            ),
            "contentReactions" => array(
                "upvote" => "<i class='fa-solid fa-up'></i>",
                "downvote" => "<i class='fa-solid fa-down'></i>",
            ),
            "entityReactions" => array(
                "favorite" => "<i class='fa fa-star'></i>",
                "watch" => "<i class='fa fa-eye'></i>"
            ),

        );

        public function __construct()
        {

            $this->db = core\db::getInstance();
        }

        /**
         * addReaction - adds a reaction to an entity
         * @access public
         *
         * @param string $module
         * @param int $moduleId
         * @param int $userId
         * @param string $reaction
         *
         * @return bool
         */
        public function addReaction(string $module, int $moduleId, int $userId, string $reaction): bool
        {

            $sql = 'INSERT INTO zp_reactions
                        (module,moduleId,userId,reaction,date)
                    VALUES
                        (:module,:moduleId,:userId,:reaction,:date)';

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_INT);
            $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmn->bindValue(':reaction', $reaction, PDO::PARAM_STR);
            $stmn->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;
        }

        /**
         * getGroupedEntityReactions - gets all reactions for a given entity grouped and counted by reactions
         * @access public
         *
         * @param string $module
         * @param int $moduleId
         *
         * @return array|bool returns the array on success or false on failure
         */
        public function getGroupedEntityReactions($module, $moduleId): array|false
        {

            $sql = "SELECT
                        COUNT(reaction) AS reactionCount,
                        reaction
                         FROM zp_reactions
					WHERE module=:module AND moduleId=:moduleId
					GROUP BY reaction";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * getMyReactions - gets user reactions. Can be very broad or very targeted
         * @access public
         *
         * @param int $userId
         * @param string $module
         * @param ?int $moduleId
         * @param string $reaction
         *
         * @return array|false
         */
        public function getMyReactions(int $userId, string $module = '', ?int $moduleId = null, string $reaction = ''): array|false
        {

            $sql = "SELECT
                        id,
                        reaction,
                        date,
                        module,
                        moduleId,
                        reaction,
                        userId
                    FROM zp_reactions
					WHERE
					    userId = :userId
					";

            if ($module != '') {
                $sql .= " AND module=:module";
            }
            if ($moduleId != null) {
                $sql .= " AND moduleId=:moduleId";
            }
            if ($reaction != '') {
                $sql .= " AND reaction = :reaction";
            }

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);

            if ($module != '') {
                $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            }
            if ($moduleId != '') {
                $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_STR);
            }
            if ($reaction != '') {
                $stmn->bindValue(':reaction', $reaction, PDO::PARAM_STR);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * removeReaction - removes a reaction
         * @access public
         *
         * @param int $id
         *
         * @return bool
         */
        public function removeReaction(string $id): bool
        {

            $sql = 'DELETE FROM zp_reactions WHERE id = :id LIMIT 1';

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;
        }
    }
}
