<?php

namespace Leantime\Domain\Search\Repositories;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Search\Models\SearchQuery;

/**
 * Narrow, purpose-built queries for global search: one per entity type, selecting only
 * what a result row needs, always limited, and all applying the same project scope.
 *
 * Keeping every search query in this file is deliberate: the access predicate cannot drift
 * between entity types and the matching strategy lives in one place.
 *
 * Text matching has two paths (see applyTextMatch):
 *  - Full-text (MySQL/MariaDB): boolean-mode MATCH ... AGAINST on the indexed columns,
 *    ordered by relevance so InnoDB can stop at the LIMIT. Measured on 500k to-dos this is
 *    1–100 ms where a LIKE scan took ~600 ms. Used when the driver supports it and every
 *    token is long enough for the index; falls back to LIKE if the index is missing.
 *  - LIKE (other drivers, short tokens): every token must match one of the columns; title
 *    prefix hits first, then title hits, then newest first.
 *
 * Shared filters every query honours (see applyCommonFilters): a project, a modified-date
 * range (UTC bounds prepared by the service) and "only mine" (author/owner columns).
 */
class Search
{
    /**
     * MySQL/MariaDB error 1191: "Can't find FULLTEXT index matching the column list".
     */
    private const ERROR_FULLTEXT_INDEX_MISSING = 1191;

    /**
     * Set once a full-text query failed for lack of an index, so the request does not keep
     * retrying it for every entity type.
     */
    private static bool $fullTextUnavailable = false;

    private ConnectionInterface $connection;

    public function __construct(DbCore $db, private DatabaseHelper $dbHelper)
    {
        $this->connection = $db->getConnection();
    }

    /**
     * Search tasks, subtasks and milestones by headline or description. A purely numeric
     * term additionally matches the ticket id exactly (first result of the first page).
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchTickets(SearchQuery $query): array
    {
        $results = $this->runTextSearch($query, function (bool $fullText) use ($query): Builder {
            $builder = $this->ticketBaseQuery($query);

            $this->applyTextMatch(
                $builder,
                $fullText,
                ['zp_tickets.headline', 'zp_tickets.description'],
                'zp_tickets.headline',
                'zp_tickets.modified',
                $query
            );

            return $builder;
        });

        if ($query->offset === 0 && ctype_digit($query->term)) {
            $exact = $this->ticketBaseQuery($query)->where('zp_tickets.id', (int) $query->term)->first();

            if ($exact !== null) {
                $results = array_values(array_filter($results, fn (array $row) => (int) $row['id'] !== (int) $exact->id));
                array_unshift($results, (array) $exact);
                $results = array_slice($results, 0, $query->limit);
            }
        }

        return $results;
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
        $this->applyCommonFilters($builder, 'project.id', 'project.modified', [], $query);

        foreach ($query->tokens as $token) {
            $builder->where(function (Builder $group) use ($token, $query) {
                $this->whereAnyLike($group, ['project.name', 'project.details', 'client.name'], $token, $query);
            });
        }

        $this->orderByRelevance($builder, 'project.name', $query);
        $builder->orderBy('project.name');

        return $this->fetch($builder, $query);
    }

    /**
     * Search items of the shared canvas tables (wiki articles, ideas, goals, blueprint items).
     *
     * The canvas type join is mandatory: zp_canvas_items shares one id sequence across every
     * canvas kind, so a type-less read could surface another kind's rows.
     *
     * The full-text index covers title, description and data. $textColumns only widens the
     * LIKE path (tags, assumptions, conclusion); the full-text path always matches the three
     * indexed columns.
     *
     * @param  string[]  $canvasTypes  Allowed zp_canvas.type values.
     * @param  string|null  $box  Required zp_canvas_items.box value, or null for any box.
     * @param  string[]  $textColumns  Item columns to match tokens against on the LIKE path (unqualified).
     * @param  bool  $publishedOrOwnDrafts  Wiki rule: published items, or the user's own drafts.
     * @return array<int, array<string, mixed>>
     */
    public function searchCanvasItems(SearchQuery $query, array $canvasTypes, ?string $box, array $textColumns, bool $publishedOrOwnDrafts = false): array
    {
        $lastChange = 'COALESCE(item.modified, item.created)';

        return $this->runTextSearch($query, function (bool $fullText) use ($query, $canvasTypes, $box, $textColumns, $publishedOrOwnDrafts, $lastChange): Builder {
            $builder = $this->connection->table('zp_canvas_items as item')
                ->select([
                    'item.id',
                    'item.title',
                    'item.description',
                    'item.data',
                    'item.box',
                    'item.status',
                    'item.author',
                    'item.canvasId',
                    'canvas.type as canvasType',
                    'canvas.title as canvasTitle',
                    'canvas.projectId',
                    'project.name as projectName',
                ])
                ->selectRaw($lastChange.' as '.$this->dbHelper->wrapColumn('modified'))
                ->join('zp_canvas as canvas', 'item.canvasId', '=', 'canvas.id')
                ->join('zp_projects as project', 'canvas.projectId', '=', 'project.id')
                ->whereIn('canvas.type', $canvasTypes);

            if ($box !== null) {
                $builder->where('item.box', $box);
            }

            if ($publishedOrOwnDrafts) {
                $this->wherePublishedOrOwnDraft($builder, $query->userId);
            }

            $this->applyProjectScope($builder, 'project', 'canvas.projectId', $query);
            $this->applyCommonFilters($builder, 'canvas.projectId', $lastChange, ['item.author'], $query);

            $this->applyTextMatch(
                $builder,
                $fullText,
                ['item.title', 'item.description', 'item.data'],
                'item.'.$textColumns[0],
                $lastChange,
                $query,
                likeColumns: array_map(fn (string $column) => 'item.'.$column, $textColumns)
            );

            return $builder;
        });
    }

    /**
     * Owning canvas and project of a canvas item, restricted to the given canvas types.
     *
     * @param  string[]  $canvasTypes
     * @param  int|null  $publishedOrOwnDraftsFor  Apply the wiki rule (published, or this user's own draft).
     * @return array{projectId: int, canvasId: int, canvasType: string}|null
     */
    public function getCanvasItemTarget(int $id, array $canvasTypes, ?string $box = null, ?int $publishedOrOwnDraftsFor = null): ?array
    {
        $builder = $this->connection->table('zp_canvas_items as item')
            ->select(['canvas.projectId', 'canvas.id as canvasId', 'canvas.type as canvasType'])
            ->join('zp_canvas as canvas', 'item.canvasId', '=', 'canvas.id')
            ->where('item.id', $id)
            ->whereIn('canvas.type', $canvasTypes);

        if ($box !== null) {
            $builder->where('item.box', $box);
        }

        if ($publishedOrOwnDraftsFor !== null) {
            $this->wherePublishedOrOwnDraft($builder, $publishedOrOwnDraftsFor);
        }

        $row = $builder->first();

        if ($row === null) {
            return null;
        }

        return ['projectId' => (int) $row->projectId, 'canvasId' => (int) $row->canvasId, 'canvasType' => (string) $row->canvasType];
    }

    /**
     * Search canvas boards themselves (title and description), e.g. strategy blueprints.
     * Boards are few, so this is LIKE only.
     *
     * @param  string[]  $canvasTypes
     * @return array<int, array<string, mixed>>
     */
    public function searchCanvasBoards(SearchQuery $query, array $canvasTypes): array
    {
        $lastChange = 'COALESCE(canvas.modified, canvas.created)';

        $builder = $this->connection->table('zp_canvas as canvas')
            ->select([
                'canvas.id',
                'canvas.title',
                'canvas.description',
                'canvas.type as canvasType',
                'canvas.projectId',
                'project.name as projectName',
            ])
            ->selectRaw($lastChange.' as '.$this->dbHelper->wrapColumn('modified'))
            ->join('zp_projects as project', 'canvas.projectId', '=', 'project.id')
            ->whereIn('canvas.type', $canvasTypes);

        $this->applyProjectScope($builder, 'project', 'canvas.projectId', $query);
        $this->applyCommonFilters($builder, 'canvas.projectId', $lastChange, ['canvas.author'], $query);

        $this->applyTextMatch($builder, false, ['canvas.title', 'canvas.description'], 'canvas.title', $lastChange, $query);

        return $this->fetch($builder, $query);
    }

    /**
     * Project and type of a canvas board, restricted to the given types.
     *
     * @param  string[]  $canvasTypes
     * @return array{projectId: int, canvasType: string}|null
     */
    public function getCanvasBoardTarget(int $id, array $canvasTypes): ?array
    {
        $row = $this->connection->table('zp_canvas')
            ->select(['projectId', 'type'])
            ->where('id', $id)
            ->whereIn('type', $canvasTypes)
            ->first();

        if ($row === null) {
            return null;
        }

        return ['projectId' => (int) $row->projectId, 'canvasType' => (string) $row->type];
    }

    /**
     * Search comment text. The owning project is resolved per module the same way
     * Comments::resolveModuleProjectId() does, in SQL: tickets via their project, project
     * comments via the module id, canvas-backed comments via the canvas type the module
     * implies (article → wiki, idea → idea, {x}canvasitem → {x}canvas). Comments whose
     * project cannot be resolved are never returned.
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchComments(SearchQuery $query): array
    {
        return $this->runTextSearch($query, function (bool $fullText) use ($query): Builder {
            $projectIdExpression = $this->commentProjectIdExpression();

            $builder = $this->connection->table('zp_comment as comment')
                ->select([
                    'comment.id',
                    'comment.text',
                    'comment.module',
                    'comment.moduleId',
                    'comment.userId',
                    'comment.date as modified',
                    'project.name as projectName',
                    'author.firstname as authorFirstname',
                    'author.lastname as authorLastname',
                ])
                ->selectRaw($projectIdExpression.' as '.$this->dbHelper->wrapColumn('projectId'))
                ->selectRaw('COALESCE(ticket.headline, NULLIF(item.title, \'\'), item.description, hostProject.name) as '.$this->dbHelper->wrapColumn('hostTitle'));

            $this->joinCommentHosts($builder);

            $builder->join('zp_projects as project', function (JoinClause $join) use ($projectIdExpression) {
                $join->on('project.id', '=', $this->connection->raw($projectIdExpression));
            })
                ->leftJoin('zp_user as author', 'comment.userId', '=', 'author.id');

            $this->applyProjectScope($builder, 'project', 'project.id', $query);
            $this->whereCommentHostVisible($builder, $query->userId);
            $this->applyCommonFilters($builder, 'project.id', 'comment.date', ['comment.userId'], $query);

            // Comments have no title to rank by; the LIKE path is newest first.
            $this->applyTextMatch($builder, $fullText, ['comment.text'], null, 'comment.date', $query);

            return $builder;
        });
    }

    /**
     * Module, host id and resolved project of a comment, or null when unresolvable or when
     * the host is another user's draft article.
     *
     * @return array{module: string, moduleId: int, projectId: int}|null
     */
    public function getCommentTarget(int $id, int $userId): ?array
    {
        $projectIdExpression = $this->commentProjectIdExpression();

        $builder = $this->connection->table('zp_comment as comment')
            ->select(['comment.module', 'comment.moduleId'])
            ->selectRaw($projectIdExpression.' as '.$this->dbHelper->wrapColumn('projectId'))
            ->where('comment.id', $id);

        $this->joinCommentHosts($builder);
        $this->whereCommentHostVisible($builder, $userId);

        $row = $builder->first();

        if ($row === null || $row->projectId === null) {
            return null;
        }

        return ['module' => (string) $row->module, 'moduleId' => (int) $row->moduleId, 'projectId' => (int) $row->projectId];
    }

    /**
     * Search file names. Project, ticket and wiki files follow the project scope; private
     * and user files are visible to their uploader only. Other modules are not searched.
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchFiles(SearchQuery $query): array
    {
        $projectIdExpression = $this->fileProjectIdExpression();

        $builder = $this->connection->table('zp_file as file')
            ->select([
                'file.id',
                'file.realName',
                'file.extension',
                'file.encName',
                'file.module',
                'file.moduleId',
                'file.userId',
                'file.date as modified',
                'project.name as projectName',
            ])
            ->selectRaw($projectIdExpression.' as '.$this->dbHelper->wrapColumn('projectId'));

        $this->joinFileHosts($builder);

        $builder->leftJoin('zp_projects as project', function (JoinClause $join) use ($projectIdExpression) {
            $join->on('project.id', '=', $this->connection->raw($projectIdExpression));
        });

        $builder->where(function (Builder $visibility) use ($query) {
            $visibility->where(function (Builder $projectFiles) use ($query) {
                $projectFiles->whereIn('file.module', ['project', 'ticket', 'wiki'])
                    ->whereNotNull('project.id');
                $this->applyProjectScope($projectFiles, 'project', 'project.id', $query);
            })->orWhere(function (Builder $ownFiles) use ($query) {
                $ownFiles->whereIn('file.module', ['private', 'user'])
                    ->where('file.userId', $query->userId);
            });
        });

        $this->applyCommonFilters($builder, 'project.id', 'file.date', ['file.userId'], $query);

        foreach ($query->tokens as $token) {
            $builder->where(function (Builder $group) use ($token, $query) {
                $this->whereAnyLike($group, ['file.realName'], $token, $query);
            });
        }

        $this->orderByRelevance($builder, 'file.realName', $query);
        $builder->orderBy('file.date', 'desc');

        return $this->fetch($builder, $query);
    }

    /**
     * Download parameters, owner and resolved project of a file.
     *
     * @return array{module: string, moduleId: int, userId: int, encName: string, extension: string, realName: string, projectId: int|null}|null
     */
    public function getFileTarget(int $id): ?array
    {
        $projectIdExpression = $this->fileProjectIdExpression();

        $builder = $this->connection->table('zp_file as file')
            ->select(['file.module', 'file.moduleId', 'file.userId', 'file.encName', 'file.extension', 'file.realName'])
            ->selectRaw($projectIdExpression.' as '.$this->dbHelper->wrapColumn('projectId'))
            ->where('file.id', $id);

        $this->joinFileHosts($builder);

        $row = $builder->first();

        if ($row === null) {
            return null;
        }

        return [
            'module' => (string) $row->module,
            'moduleId' => (int) $row->moduleId,
            'userId' => (int) $row->userId,
            'encName' => (string) $row->encName,
            'extension' => (string) $row->extension,
            'realName' => (string) $row->realName,
            'projectId' => $row->projectId !== null ? (int) $row->projectId : null,
        ];
    }

    /**
     * Search active users by name, email, job title or department. The caller decides who
     * may run this (admins/owners only).
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchUsers(SearchQuery $query): array
    {
        $builder = $this->connection->table('zp_user as user')
            ->select(['user.id', 'user.firstname', 'user.lastname', 'user.username', 'user.jobTitle', 'user.department', 'user.modified', 'client.name as clientName'])
            ->leftJoin('zp_clients as client', 'user.clientId', '=', 'client.id')
            ->whereRaw('LOWER(user.status) = ?', ['a']);

        $this->applyCommonFilters($builder, null, 'user.modified', [], $query);

        foreach ($query->tokens as $token) {
            $builder->where(function (Builder $group) use ($token, $query) {
                $this->whereAnyLike($group, ['user.firstname', 'user.lastname', 'user.username', 'user.jobTitle', 'user.department'], $token, $query);
            });
        }

        $this->orderByRelevance($builder, 'user.lastname', $query);
        $builder->orderBy('user.lastname')->orderBy('user.firstname');

        return $this->fetch($builder, $query);
    }

    /**
     * Whether an active user with this id exists.
     */
    public function userExists(int $id): bool
    {
        return $this->connection->table('zp_user')
            ->where('id', $id)
            ->whereRaw('LOWER(status) = ?', ['a'])
            ->exists();
    }

    /**
     * Ticket rows with the project join, scope and shared filters applied; the text match
     * (or the exact-id lookup) is added by the caller.
     */
    private function ticketBaseQuery(SearchQuery $query): Builder
    {
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
        $this->applyCommonFilters($builder, 'zp_tickets.projectId', 'zp_tickets.modified', ['zp_tickets.editorId', 'zp_tickets.userId'], $query);

        return $builder;
    }

    /**
     * Run a text search on the full-text path when possible, falling back to LIKE when the
     * index turns out to be missing (database update not run yet).
     *
     * @param  callable(bool): Builder  $buildQuery  Builds the query for the given path.
     * @return array<int, array<string, mixed>>
     */
    private function runTextSearch(SearchQuery $query, callable $buildQuery): array
    {
        if ($this->useFullText($query)) {
            try {
                return $this->fetch($buildQuery(true), $query);
            } catch (QueryException $exception) {
                if ((int) ($exception->errorInfo[1] ?? 0) !== self::ERROR_FULLTEXT_INDEX_MISSING) {
                    throw $exception;
                }

                self::$fullTextUnavailable = true;
                Log::warning('Global search: full-text index missing, using LIKE for this request. Run the database update (migration 30529) to restore fast search. '.$exception->getMessage());
            }
        }

        return $this->fetch($buildQuery(false), $query);
    }

    /**
     * Full-text applies when the driver supports it and every token is long enough to be
     * in the index; otherwise the index could never match and LIKE is the only option.
     */
    private function useFullText(SearchQuery $query): bool
    {
        return ! self::$fullTextUnavailable
            && $this->dbHelper->supportsFullTextSearch()
            && $query->fullTextTokens() !== null;
    }

    /**
     * Match the query's tokens against text columns and order the result.
     *
     * Full-text path: MATCH ... AGAINST in boolean mode on $fullTextColumns (which must be
     * exactly one index's column list) ordered by relevance only — any other ORDER BY makes
     * InnoDB materialize every hit before the LIMIT (measured 42 ms vs 325 ms on 78k hits).
     * LIKE path: every token must match one of the columns; title prefix hits first, then
     * title hits, then newest first.
     *
     * @param  string[]  $fullTextColumns  Indexed columns, qualified.
     * @param  string|null  $titleColumn  Column for the LIKE-path relevance ranking; null for date order only.
     * @param  string  $dateExpression  Column or raw expression for the LIKE-path recency order.
     * @param  string[]|null  $likeColumns  Columns for the LIKE path; defaults to $fullTextColumns.
     */
    private function applyTextMatch(Builder $builder, bool $fullText, array $fullTextColumns, ?string $titleColumn, string $dateExpression, SearchQuery $query, ?array $likeColumns = null): void
    {
        if ($fullText) {
            $match = $this->matchExpression($fullTextColumns);
            $against = $query->booleanModeQuery();

            $builder->whereRaw($match, [$against])
                ->orderByRaw($match.' DESC', [$against]);

            return;
        }

        foreach ($query->tokens as $token) {
            $builder->where(function (Builder $group) use ($likeColumns, $fullTextColumns, $token, $query) {
                $this->whereAnyLike($group, $likeColumns ?? $fullTextColumns, $token, $query);
            });
        }

        if ($titleColumn !== null) {
            $this->orderByRelevance($builder, $titleColumn, $query);
        }

        $builder->orderByRaw($this->wrapIfColumn($dateExpression).' desc');
    }

    /**
     * "MATCH(a, b) AGAINST(? IN BOOLEAN MODE)" for the given columns.
     *
     * @param  string[]  $columns
     */
    private function matchExpression(array $columns): string
    {
        $wrapped = implode(', ', array_map(fn (string $column) => $this->dbHelper->wrapColumn($column), $columns));

        return "MATCH({$wrapped}) AGAINST(? IN BOOLEAN MODE)";
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
     * Apply the results-page filters shared by every entity type.
     *
     * @param  string|null  $projectIdColumn  Column (or raw expression) holding the owning project; null when the entity has none.
     * @param  string  $dateColumn  Column or raw expression of the last change, compared against UTC bounds.
     * @param  string[]  $authorColumns  Columns that identify "my" rows; "mine" matches any of them.
     */
    private function applyCommonFilters(Builder $builder, ?string $projectIdColumn, string $dateColumn, array $authorColumns, SearchQuery $query): void
    {
        $projectId = (int) $query->filter('projectId');
        if ($projectId > 0 && $projectIdColumn !== null) {
            $builder->whereRaw($this->wrapIfColumn($projectIdColumn).' = ?', [$projectId]);
        }

        $from = $query->filter('from');
        if (is_string($from) && $from !== '') {
            $builder->whereRaw($this->wrapIfColumn($dateColumn).' >= ?', [$from]);
        }

        $to = $query->filter('to');
        if (is_string($to) && $to !== '') {
            $builder->whereRaw($this->wrapIfColumn($dateColumn).' <= ?', [$to]);
        }

        // Bound as a string: zp_tickets.editorId is varchar and PostgreSQL will not compare it to an int.
        if ($query->filter('mine') === true && $authorColumns !== []) {
            $builder->where(function (Builder $group) use ($authorColumns, $query) {
                foreach ($authorColumns as $column) {
                    $group->orWhere($column, (string) $query->userId);
                }
            });
        }
    }

    /**
     * OR together a case-insensitive LIKE on every column for one token.
     *
     * @param  string[]  $columns
     */
    private function whereAnyLike(Builder $group, array $columns, string $token, SearchQuery $query): void
    {
        $like = $this->dbHelper->likeOperator();

        foreach ($columns as $column) {
            $group->orWhere($column, $like, $query->containsPattern($token));
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

    /**
     * Apply limit/offset and return plain arrays.
     *
     * @return array<int, array<string, mixed>>
     */
    private function fetch(Builder $builder, SearchQuery $query): array
    {
        $rows = $builder->limit($query->limit)->offset($query->offset)->get()->all();

        return array_map(fn ($row) => (array) $row, $rows);
    }

    /**
     * Quote a plain column reference; leave raw expressions (anything with a space or
     * parenthesis) untouched.
     */
    private function wrapIfColumn(string $columnOrExpression): string
    {
        if (preg_match('/[\s(]/', $columnOrExpression) === 1) {
            return $columnOrExpression;
        }

        return $this->dbHelper->wrapColumn($columnOrExpression);
    }

    /**
     * Joins the possible comment hosts: the ticket, the project, and the canvas item plus its
     * board (type-checked against the module name).
     */
    private function joinCommentHosts(Builder $builder): void
    {
        $builder->leftJoin('zp_tickets as ticket', function (JoinClause $join) {
            $join->on('ticket.id', '=', 'comment.moduleId')->where('comment.module', 'ticket');
        })
            ->leftJoin('zp_projects as hostProject', function (JoinClause $join) {
                $join->on('hostProject.id', '=', 'comment.moduleId')->where('comment.module', 'project');
            })
            ->leftJoin('zp_canvas_items as item', function (JoinClause $join) {
                $join->on('item.id', '=', 'comment.moduleId')
                    ->where(function (Builder $modules) {
                        $modules->whereIn('comment.module', ['article', 'idea'])
                            ->orWhere('comment.module', 'LIKE', '%canvasitem');
                    });
            })
            ->leftJoin('zp_canvas as canvas', function (JoinClause $join) {
                $join->on('canvas.id', '=', 'item.canvasId')
                    ->on('canvas.type', '=', $this->connection->raw(
                        "CASE comment.module WHEN 'article' THEN 'wiki' WHEN 'idea' THEN 'idea' ELSE REPLACE(comment.module, 'canvasitem', 'canvas') END"
                    ));
            });
    }

    /**
     * Wiki visibility rule: published items, or the user's own drafts.
     */
    private function wherePublishedOrOwnDraft(Builder $builder, int $userId): void
    {
        $builder->where(function (Builder $group) use ($userId) {
            $group->where('item.status', 'published')
                ->orWhere(function (Builder $own) use ($userId) {
                    $own->where('item.status', 'draft')->where('item.author', $userId);
                });
        });
    }

    /**
     * A comment on a wiki article inherits the article's visibility: another user's draft
     * must not leak its comments (or title) through search.
     */
    private function whereCommentHostVisible(Builder $builder, int $userId): void
    {
        $builder->where(function (Builder $group) use ($userId) {
            $group->where('comment.module', '<>', 'article')
                ->orWhere('item.status', 'published')
                ->orWhere(function (Builder $own) use ($userId) {
                    $own->where('item.status', 'draft')->where('item.author', $userId);
                });
        });
    }

    /**
     * SQL expression for a comment's owning project id (NULL when unresolvable).
     */
    private function commentProjectIdExpression(): string
    {
        return "COALESCE(ticket.projectId, CASE WHEN comment.module = 'project' THEN comment.moduleId END, canvas.projectId)";
    }

    /**
     * Joins the possible file hosts: the ticket and the wiki article's board.
     */
    private function joinFileHosts(Builder $builder): void
    {
        $builder->leftJoin('zp_tickets as ticket', function (JoinClause $join) {
            $join->on('ticket.id', '=', 'file.moduleId')->where('file.module', 'ticket');
        })
            ->leftJoin('zp_canvas_items as article', function (JoinClause $join) {
                $join->on('article.id', '=', 'file.moduleId')
                    ->where('file.module', 'wiki')
                    ->where('article.box', 'article');
            })
            ->leftJoin('zp_canvas as wiki', function (JoinClause $join) {
                $join->on('wiki.id', '=', 'article.canvasId')->where('wiki.type', 'wiki');
            });
    }

    /**
     * SQL expression for a file's owning project id (NULL for owner-only modules).
     */
    private function fileProjectIdExpression(): string
    {
        return "COALESCE(CASE WHEN file.module = 'project' THEN file.moduleId END, ticket.projectId, wiki.projectId)";
    }
}
