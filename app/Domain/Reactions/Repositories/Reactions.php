<?php

namespace Leantime\Domain\Reactions\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

/**
 * Repository for managing user reactions on entities.
 */
class Reactions
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * Add a reaction to an entity.
     */
    public function addReaction(int $userId, string $module, int $moduleId, string $reaction): bool
    {
        return $this->db->table('zp_reactions')->insert([
            'module' => $module,
            'moduleId' => $moduleId,
            'userId' => $userId,
            'reaction' => $reaction,
            'date' => dtHelper()->userNow()->formatDateTimeForDb(),
        ]);
    }

    /**
     * getGroupedEntityReactions - gets all reactions for a given entity grouped and counted by reactions
     *
     * @return array|bool returns the array on success or false on failure
     */
    public function getGroupedEntityReactions(string $module, int $moduleId): array|false
    {
        $results = $this->db->table('zp_reactions')
            ->selectRaw('COUNT(reaction) AS reactionCount, reaction')
            ->where('module', $module)
            ->where('moduleId', $moduleId)
            ->groupBy('reaction')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * getMyReactions - gets user reactions. Can be very broad or very targeted
     */
    public function getUserReactions(int $userId, string $module = '', ?int $moduleId = null, string $reaction = ''): array|false
    {
        $query = $this->db->table('zp_reactions')
            ->select('id', 'reaction', 'date', 'module', 'moduleId', 'userId')
            ->where('userId', $userId);

        if ($module !== '') {
            $query->where('module', $module);
        }
        if ($moduleId !== null) {
            $query->where('moduleId', $moduleId);
        }
        if ($reaction !== '') {
            $query->where('reaction', $reaction);
        }

        $results = $query->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * removeReactionById - removes a reaction by reaction id
     */
    public function removeReactionById(int $id): bool
    {
        return $this->db->table('zp_reactions')
            ->where('id', $id)
            ->limit(1)
            ->delete() > 0;
    }

    /**
     * removeUserReaction - removes a users reaction to an entity
     */
    public function removeUserReaction(int $userId, string $module, int $moduleId, string $reaction): bool
    {
        return $this->db->table('zp_reactions')
            ->where('module', $module)
            ->where('moduleId', $moduleId)
            ->where('userId', $userId)
            ->where('reaction', $reaction)
            ->limit(1)
            ->delete() > 0;
    }

    /**
     * getReactionsByModule - gets reactions count by module
     */
    public function getReactionsByModule(string $module, ?int $moduleId = null): array|false
    {
        $query = $this->db->table('zp_reactions')
            ->selectRaw('reaction, COUNT(reaction) as reactionCount')
            ->where('module', $module);

        if ($moduleId !== null) {
            $query->where('moduleId', $moduleId);
        }

        $results = $query->groupBy('reaction')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * getEntityReactionsWithUsers - gets all reactions for an entity with user names
     *
     * @return array returns array grouped by reaction with user names
     */
    public function getEntityReactionsWithUsers(string $module, int $moduleId): array
    {
        $results = $this->db->table('zp_reactions')
            ->select('zp_reactions.reaction', 'zp_reactions.userId', 'zp_user.firstname', 'zp_user.lastname')
            ->leftJoin('zp_user', 'zp_reactions.userId', '=', 'zp_user.id')
            ->where('zp_reactions.module', $module)
            ->where('zp_reactions.moduleId', $moduleId)
            ->get();

        // Group by reaction and collect user names
        $grouped = [];
        foreach ($results as $row) {
            $reaction = $row->reaction;
            if (! isset($grouped[$reaction])) {
                $grouped[$reaction] = [
                    'reaction' => $reaction,
                    'reactionCount' => 0,
                    'users' => [],
                ];
            }
            $grouped[$reaction]['reactionCount']++;
            $grouped[$reaction]['users'][] = [
                'userId' => $row->userId,
                'name' => trim(($row->firstname ?? '').' '.($row->lastname ?? '')),
            ];
        }

        return array_values($grouped);
    }
}
