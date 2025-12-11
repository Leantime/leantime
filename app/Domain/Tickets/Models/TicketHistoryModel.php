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

    /**
     * Add status change to history
     *
     * @param int $ticketId
     * @param string $oldStatus
     * @param string $newStatus
     * @param string $oldStatusText
     * @param string $newStatusText
     * @param string $changedBy
     * @return int|false Last insert ID or false on failure
     */
    public function addStatusChange($ticketId, $oldStatus, $newStatus, $oldStatusText, $newStatusText, $changedBy)
    {
        $sql = "INSERT INTO zp_ticket_status_changes 
                (ticketId, oldStatus, newStatus, oldStatusText, newStatusText, changedBy, changedAt) 
                VALUES (:ticketId, :oldStatus, :newStatus, :oldStatusText, :newStatusText, :changedBy, NOW())";

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
        $stmn->bindValue(':oldStatus', $oldStatus, PDO::PARAM_STR);
        $stmn->bindValue(':newStatus', $newStatus, PDO::PARAM_STR);
        $stmn->bindValue(':oldStatusText', $oldStatusText, PDO::PARAM_STR);
        $stmn->bindValue(':newStatusText', $newStatusText, PDO::PARAM_STR);
        $stmn->bindValue(':changedBy', $changedBy, PDO::PARAM_STR);

        if ($stmn->execute()) {
            return $this->db->database->lastInsertId();
        }

        return false;
    }

    /**
     * Get all status changes for a ticket
     *
     * @param int $ticketId
     * @return array
     */
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

    /**
     * Get latest status change for a ticket
     *
     * @param int $ticketId
     * @return array|false
     */
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

    /**
     * Delete all status changes for a ticket
     *
     * @param int $ticketId
     * @return bool
     */
    public function deleteStatusChangesByTicket($ticketId)
    {
        $sql = "DELETE FROM zp_ticket_status_changes WHERE ticketId = :ticketId";

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);

        return $stmn->execute();
    }
}