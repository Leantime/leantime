<?php

namespace Leantime\Domain\Read\Repositories;

use Leantime\Infrastructure\Database\Db as DbCore;
use PDO;

class Read
{
    private DbCore $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db;
    }

    public function markAsRead($module, $moduleId, $userId): void
    {

        $sql = 'INSERT INTO zp_read (module,moduleId,userId) VALUES (:module,:moduleId,:userId)';

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':module', $module, PDO::PARAM_STR);
        $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_STR);
        $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);

        $stmn->execute();
        $stmn->closeCursor();
    }

    public function isRead($module, $moduleId, $userId): bool
    {

        $sql = 'SELECT * FROM zp_read
					WHERE module=:module AND moduleId=:moduleId AND userId=:userId';

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':module', $module, PDO::PARAM_STR);
        $stmn->bindValue(':moduleId', $moduleId, PDO::PARAM_STR);
        $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);

        $stmn->execute();
        $values = $stmn->fetch();
        $stmn->closeCursor();

        $return = false;
        if ($values) {
            $return = true;
        }

        return $return;
    }
}
