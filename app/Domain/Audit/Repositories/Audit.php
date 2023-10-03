<?php

namespace Leantime\Domain\Audit\Repositories {

    use Leantime\Core\Db as DbCore;
    use PDO;

    /**
     *
     */
    class Audit
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
         * @param $action
         * @param $values
         * @param $entity
         * @param $entityId
         * @param $userId
         * @param $projectId
         * @param $thedate
         * @return void
         */
        /**
         * @param string $action
         * @param string $values
         * @param string $entity
         * @param int $entityId
         * @param int $userId
         * @param int $projectId
         * @param string $thedate
         * @return void
         */
        public function storeEvent(string $action = "ping", string $values = "", string $entity = "", int $entityId = 0, int $userId = 0, int $projectId = 0, string $thedate = ''): void
        {

            if ($thedate == '') {
                $thedate2 = date('Y-m-d H:i:s');
            } else {
                $thedate2 = $thedate;
            }

            $sql = 'INSERT INTO zp_audit (`userId`,`projectId`,`action`,`entity`,`entityId`,`values`,`date`) VALUES (:userId,:projectId,:action,:entity,:entityId,:values,:thedate)';

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            $stmn->bindValue(':action', $action, PDO::PARAM_STR);
            $stmn->bindValue(':entity', $entity, PDO::PARAM_STR);
            $stmn->bindValue(':entityId', $entityId, PDO::PARAM_INT);
            $stmn->bindValue(':values', $values, PDO::PARAM_STR);
            $stmn->bindValue(':thedate', $thedate2, PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();
        }

        /**
         * @param $action
         * @return mixed|void
         */
        /**
         * @param string $action
         * @return mixed|void
         */
        public function getLastEvent(string $action = '')
        {
            $sql = 'SELECT * FROM zp_audit';

            if ($action != '') {
                $sql .= ' WHERE `action` = :action';
            }
            $sql .= ' ORDER BY `date` DESC LIMIT 1';

            $stmn = $this->db->database->prepare($sql);

            if ($action != '') {
                $stmn->bindValue(':action', $action, PDO::PARAM_STR);
            }

            $stmn->execute();

            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            if (isset($values[0])) {
                return $values[0];
            }
        }

        /**
         * @param $ageDays
         * @return void
         */
        /**
         * @param int $ageDays
         * @return void
         */
        public function pruneEvents(int $ageDays = 30): void
        {
            $sql = 'DELETE FROM zp_audit WHERE DATE(`date`) < CURDATE() - INTERVAL :age DAY';

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':age', $ageDays, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->closeCursor();
        }
    }

}
