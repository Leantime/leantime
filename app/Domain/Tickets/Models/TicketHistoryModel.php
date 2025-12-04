<?php

namespace Leantime\Domain\Tickets\Models;
use Leantime\Core\Db\Db as DbCore;
use PDO;

class TicketHistoryModel
{
    private DbCore $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db;
    }

    public function addStatusChange($ticketId, $oldStatus, $newStatus, $oldStatusText, $newStatusText, $changedBy, $detailsAttributeId )
    {
        $sql = "INSERT INTO zp_ticket_status_changes 
                (ticketId, oldStatus, newStatus, oldStatusText, newStatusText, changedBy, changedAt, detailsAttributeId) 
                VALUES (:ticketId, :oldStatus, :newStatus, :oldStatusText, :newStatusText, :changedBy, NOW(), :detailsAttributeId)";

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
        $stmn->bindValue(':oldStatus', $oldStatus, PDO::PARAM_STR);
        $stmn->bindValue(':newStatus', $newStatus, PDO::PARAM_STR);
        $stmn->bindValue(':oldStatusText', $oldStatusText, PDO::PARAM_STR);
        $stmn->bindValue(':newStatusText', $newStatusText, PDO::PARAM_STR);
        $stmn->bindValue(':changedBy', $changedBy, PDO::PARAM_STR);
        $stmn->bindValue(':detailsAttributeId', $detailsAttributeId, PDO::PARAM_STR);

        if ($stmn->execute()) {
            return $this->db->database->lastInsertId();
        }

        return false;
    }

    public function getStatusChangesByTicket($ticketId)
    {
        $sql = "SELECT * FROM zp_ticket_status_changes 
                WHERE ticketId = :ticketId 
                ORDER BY changedAt DESC";

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
        $stmn->execute();

        return $stmn->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestStatusChange($ticketId)
    {
        $sql = "SELECT * FROM zp_ticket_status_changes 
                WHERE ticketId = :ticketId 
                ORDER BY changedAt DESC 
                LIMIT 1";

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
        $stmn->execute();

        return $stmn->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteStatusChangesByTicket($ticketId)
    {
        $sql = "DELETE FROM zp_ticket_status_changes WHERE ticketId = :ticketId";

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);

        return $stmn->execute();
    }
}