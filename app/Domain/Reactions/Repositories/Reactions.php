<?php

namespace Leantime\Domain\Reactions\Repositories {

    use Leantime\Core\Db as DbCore;
    use PDO;

    /**
     *
     */
    class Reactions
    {
        /**
         * @access private
         * @var    DbCore $db db object
         */
        private DbCore $db;

        /**
         * @param DbCore $db
         */
        public function __construct(DbCore $db)
        {
            $this->db = $db;
        }

        /**
         * addReaction - adds a reaction to an entity
         * @access public
         *
         * @param string  $module
         * @param integer $moduleId
         * @param integer $userId
         * @param string  $reaction
         *
         * @return boolean
         */
        public function addReaction(int $userId, string $module, int $moduleId, string $reaction): bool
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
         * @param integer $moduleId
         *
         * @return array|boolean returns the array on success or false on failure
         */
        public function getGroupedEntityReactions(string $module, int $moduleId): array|false
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
         * @param integer      $userId
         * @param string       $module
         * @param integer|null $moduleId
         * @param string       $reaction
         *
         * @return array|false
         */
        public function getUserReactions(int $userId, string $module = '', ?int $moduleId = null, string $reaction = ''): array|false
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
         * removeReactionById - removes a reaction by reaction id
         * @access public
         *
         * @param integer $id
         *
         * @return boolean
         */
        public function removeReactionById(int $id): bool
        {

            $sql = 'DELETE FROM zp_reactions WHERE id = :id LIMIT 1';

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;
        }

        /**
         * removeUserReaction - removes a users reaction to an entity
         * @access public
         *
         * @param integer $userId
         * @param string  $module
         * @param integer $moduleId
         * @param string  $reaction
         * @return boolean
         */
        public function removeUserReaction(int $userId, string $module, int $moduleId, string $reaction): bool
        {

            $sql = 'DELETE FROM zp_reactions WHERE
                             module = :module
                         AND moduleId = :moduleId
                          AND userId = :userId
                          AND reaction = :reaction
                         LIMIT 1';

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':module', $module, PDO::PARAM_STR);
            $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_INT);
            $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmn->bindValue(':reaction', $reaction, PDO::PARAM_STR);

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;
        }
    }
}
