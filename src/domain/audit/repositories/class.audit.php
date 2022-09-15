<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class audit
    {

        public function __construct()
        {

            $this->db = core\db::getInstance();
        }

        public function storeEvent($action = "ping", $values = "", $entity = "" , $entityId = 0, $userId = 0, $projectId = 0, $thedate = '')
        {
           
            if ( $thedate == '') 
            {
                $thedate2 = date('Y-m-d H:i:s');
	    } else
	    {
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

	public function getLastEvent ($action = '')
        {
            $sql = 'SELECT * FROM zp_audit';

	        if ( $action != '' )
            {
                $sql .= ' WHERE `action` = :action';
            }
            $sql .= ' ORDER BY `date` DESC LIMIT 1';

            $stmn = $this->db->database->prepare($sql);

	        if ( $action != '' )
            {
                $stmn->bindValue(':action', $action, PDO::PARAM_STR);
            }

            $stmn->execute();

            $values = $stmn->fetchAll();
            $stmn->closeCursor();

	        if (isset($values[0]))
            {
                return $values[0];
            }

        }

	public function pruneEvents($ageDays = 30)
        {
            $sql = 'DELETE FROM zp_audit WHERE DATE(`date`) < CURDATE() - INTERVAL :age DAY';

            $stmn = $this->db->database->prepare($sql);
	    $stmn->bindValue(':age', $ageDays, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->closeCursor();

        }
    }

}
