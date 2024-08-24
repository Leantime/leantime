<?php

namespace Leantime\Domain\Audit\Repositories;

use Leantime\Core\Db\Db as DbCore;
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
     * @param string $action
     * @param string $values
     * @param string $entity
     * @param int    $entityId
     * @param int    $userId
     * @param int    $projectId
     * @param string $thedate
     *
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
        $stmn->bindValue(':action', $action);
        $stmn->bindValue(':entity', $entity);
        $stmn->bindValue(':entityId', $entityId, PDO::PARAM_INT);
        $stmn->bindValue(':values', $values);
        $stmn->bindValue(':thedate', $thedate2);

        $stmn->execute();
        $stmn->closeCursor();
    }

    /**
     * @param string $action
     *
     * @return mixed|null
     */
    public function getLastEvent(string $action = ''): mixed
    {
        $sql = 'SELECT * FROM zp_audit';

        if ($action != '') {
            $sql .= ' WHERE `action` = :action';
        }
        $sql .= ' ORDER BY `date` DESC LIMIT 1';

        $stmn = $this->db->database->prepare($sql);

        if ($action != '') {
            $stmn->bindValue(':action', $action);
        }

        $stmn->execute();

        $values = $stmn->fetchAll();
        $stmn->closeCursor();

        if (isset($values[0])) {
            return $values[0];
        }

        return null;
    }

    /**
     * @param int $ageDays
     *
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
