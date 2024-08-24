<?php

namespace Leantime\Domain\Tickets\Repositories;

use Leantime\Core\Db\Db as DbCore;
use PDO;

/**
 *
 */
class TicketHistory
{
    private DbCore $db;

    /**
     * __construct - get database connection
     *
     * @access public
     */
    public function __construct(DbCore $db)
    {
        $this->db = $db;
    }

    public function getRecentTicketHistory(\DateTime $startingFrom, int $ticketId): array
    {
        $query = "SELECT *
                    FROM zp_tickethistory
                    WHERE
                    dateModified >= :startingFrom";

        if ($ticketId !== null) {
            $query .= " AND ticketId = :ticketId";
        }

        $query .= " ORDER BY dateModified DESC";


        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':startingFrom', $startingFrom->format("Y-m-d"), PDO::PARAM_STR);

        if ($ticketId !== null) {
            $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
        }
        $stmn->execute();
        $values = $stmn->fetchAll();
        $stmn->closeCursor();

        return $values;
    }
}
