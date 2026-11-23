<?php

namespace Leantime\Domain\Recap\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

/**
 * Data access for recaps: what happened to a person's to-dos over a time range, read from the
 * to-do history (zp_tickethistory). Recaps add no tables of their own.
 *
 * Every date in and out is a UTC `Y-m-d H:i:s` string. Deciding which status counts as done,
 * and who may see what, is the service's job.
 */
class Recap
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * Status changes in the range on to-dos (not milestones) that this person changed the status
     * of in the range, or that are assigned to them. Every status change in the range on those
     * to-dos is returned, by anyone, oldest first, so the caller can see whether a to-do was
     * re-opened afterwards.
     *
     * @return array<int, array{ticketId: int, changedBy: int, changeValue: string, dateModified: string, headline: string, projectId: int, projectName: string, milestoneId: int|null, editorId: string, changedByFirstname: string, changedByLastname: string}>
     */
    public function statusChangesBetween(int $userId, string $startUtc, string $endUtc): array
    {
        $ticketIds = $this->db->table('zp_tickethistory as h')
            ->join('zp_tickets as t', 't.id', '=', 'h.ticketId')
            ->where('h.changeType', 'status')
            ->whereBetween('h.dateModified', [$startUtc, $endUtc])
            ->where(fn ($q) => $q->whereNull('t.type')->orWhere('t.type', '<>', 'milestone'))
            ->where(fn ($q) => $q->where('h.userId', $userId)->orWhere('t.editorId', (string) $userId))
            ->distinct()
            ->pluck('h.ticketId')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($ticketIds === []) {
            return [];
        }

        return $this->db->table('zp_tickethistory as h')
            ->join('zp_tickets as t', 't.id', '=', 'h.ticketId')
            ->join('zp_projects as p', 'p.id', '=', 't.projectId')
            ->leftJoin('zp_user as u', 'u.id', '=', 'h.userId')
            ->select([
                'h.ticketId', 'h.userId as changedBy', 'h.changeValue', 'h.dateModified',
                't.headline', 't.projectId', 't.milestoneid', 't.editorId', 'p.name as projectName',
                'u.firstname', 'u.lastname',
            ])
            ->where('h.changeType', 'status')
            ->whereBetween('h.dateModified', [$startUtc, $endUtc])
            ->whereIn('h.ticketId', $ticketIds)
            ->orderBy('h.dateModified')
            ->orderBy('h.id')
            ->get()
            ->map(fn ($row) => [
                'ticketId' => (int) $row->ticketId,
                'changedBy' => (int) ($row->changedBy ?? 0),
                'changeValue' => (string) ($row->changeValue ?? ''),
                'dateModified' => (string) $row->dateModified,
                'headline' => (string) ($row->headline ?? ''),
                'projectId' => (int) $row->projectId,
                'projectName' => (string) ($row->projectName ?? ''),
                'milestoneId' => is_numeric($row->milestoneid ?? null) && (int) $row->milestoneid > 0 ? (int) $row->milestoneid : null,
                'editorId' => trim((string) ($row->editorId ?? '')),
                'changedByFirstname' => (string) ($row->firstname ?? ''),
                'changedByLastname' => (string) ($row->lastname ?? ''),
            ])
            ->all();
    }
}
