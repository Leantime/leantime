<?php

namespace Leantime\Domain\Search\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Search\Models\SearchQuery;

/**
 * Narrow, purpose-built queries for global search: one per entity type, selecting only
 * what a result row needs, always limited, and all applying the same project scope.
 *
 * Keeping every search query in this file is deliberate. The access predicate cannot drift
 * between entity types, and swapping LIKE for a full-text index later is a one-file change.
 */
class Search
{
    private ConnectionInterface $connection;

    public function __construct(DbCore $db, private DatabaseHelper $dbHelper)
    {
        $this->connection = $db->getConnection();
    }

    /**
     * Search tasks, subtasks and milestones by headline, description or exact id.
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchTickets(SearchQuery $query): array
    {
        $like = $this->dbHelper->likeOperator();

        $builder = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.id',
                'zp_tickets.headline',
                'zp_tickets.description',
                'zp_tickets.projectId',
                'zp_tickets.type',
                'zp_tickets.modified',
                'zp_projects.name as projectName',
            ])
            ->join('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->where('zp_tickets.status', '<>', -1);

        $this->applyProjectScope($builder, 'zp_projects', 'zp_tickets.projectId', $query);

        if ((int) $query->filter('projectId') > 0) {
            $builder->where('zp_tickets.projectId', (int) $query->filter('projectId'));
        }

        foreach ($query->tokens as $token) {
            $builder->where(function (Builder $group) use ($token, $like, $query) {
                $group->where('zp_tickets.headline', $like, $query->containsPattern($token))
                    ->orWhere('zp_tickets.description', $like, $query->containsPattern($token));

                if (ctype_digit($token)) {
                    $group->orWhere('zp_tickets.id', (int) $token);
                }
            });
        }

        $this->orderByRelevance($builder, 'zp_tickets.headline', $query);
        $builder->orderBy('zp_tickets.modified', 'desc')
            ->limit($query->limit)
            ->offset($query->offset);

        return array_map(fn ($row) => (array) $row, $builder->get()->all());
    }

    /**
     * Owning project and type of a non-archived ticket, or null when it does not exist.
     *
     * @return array{projectId: int, type: string}|null
     */
    public function getTicketTarget(int $id): ?array
    {
        $row = $this->connection->table('zp_tickets')
            ->select(['projectId', 'type'])
            ->where('id', $id)
            ->where('status', '<>', -1)
            ->first();

        if ($row === null) {
            return null;
        }

        return ['projectId' => (int) $row->projectId, 'type' => (string) ($row->type ?? '')];
    }

    /**
     * Search open projects by name, description or client name.
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchProjects(SearchQuery $query): array
    {
        $like = $this->dbHelper->likeOperator();

        $builder = $this->connection->table('zp_projects as project')
            ->select([
                'project.id',
                'project.name',
                'project.details',
                'project.type',
                'project.modified',
                'client.name as clientName',
            ])
            ->leftJoin('zp_clients as client', 'project.clientId', '=', 'client.id');

        $this->applyProjectScope($builder, 'project', 'project.id', $query);

        foreach ($query->tokens as $token) {
            $builder->where(function (Builder $group) use ($token, $like, $query) {
                $group->where('project.name', $like, $query->containsPattern($token))
                    ->orWhere('project.details', $like, $query->containsPattern($token))
                    ->orWhere('client.name', $like, $query->containsPattern($token));
            });
        }

        $this->orderByRelevance($builder, 'project.name', $query);
        $builder->orderBy('project.name')
            ->limit($query->limit)
            ->offset($query->offset);

        return array_map(fn ($row) => (array) $row, $builder->get()->all());
    }

    /**
     * Restrict a query to open, non-deleted projects the user may access.
     *
     * Closed projects (state = -1) are never searched. The accessible id list already
     * excludes them for regular users; the explicit state check keeps admins (unrestricted
     * scope) consistent.
     */
    private function applyProjectScope(Builder $builder, string $projectAlias, string $projectIdColumn, SearchQuery $query): void
    {
        $builder->where(function (Builder $group) use ($projectAlias) {
            $group->where($projectAlias.'.state', '<>', -1)
                ->orWhereNull($projectAlias.'.state');
        })
            ->where(function (Builder $group) use ($projectAlias) {
                $group->where($projectAlias.'.active', '>', -1)
                    ->orWhereNull($projectAlias.'.active');
            });

        if ($query->accessibleProjectIds !== null) {
            $builder->whereIn($projectIdColumn, $query->accessibleProjectIds);
        }
    }

    /**
     * Rank title prefix matches first, then title matches, then everything else.
     * Uses the first token only; the following orderBy decides within each rank.
     */
    private function orderByRelevance(Builder $builder, string $titleColumn, SearchQuery $query): void
    {
        $firstToken = $query->tokens[0] ?? '';
        if ($firstToken === '') {
            return;
        }

        $like = $this->dbHelper->likeOperator();
        $column = $this->dbHelper->wrapColumn($titleColumn);

        $builder->orderByRaw(
            "CASE WHEN {$column} {$like} ? THEN 0 WHEN {$column} {$like} ? THEN 1 ELSE 2 END",
            [$query->prefixPattern($firstToken), $query->containsPattern($firstToken)]
        );
    }
}
