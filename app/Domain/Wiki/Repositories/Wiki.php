<?php

namespace Leantime\Domain\Wiki\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Blueprints\Repositories\Blueprints;
use Leantime\Domain\Tickets\Repositories\Tickets;
use Leantime\Domain\Wiki\Models\Article;
use Leantime\Domain\Wiki\Models\Wiki as WikiModel;

class Wiki extends Blueprints
{
    /**
     * Canvas type slug used to derive the Blueprints canvas type ("wikicanvas")
     * and comment module ("wikicanvasitem").
     */
    protected const CANVAS_NAME = 'wiki';

    protected ConnectionInterface $dbConnection;

    public function __construct(DbCore $db, LanguageCore $language, Tickets $ticketRepo, DatabaseHelper $dbHelper)
    {
        parent::__construct($db, $language, $ticketRepo, $dbHelper);
        $this->dbConnection = $db->getConnection();
    }

    /**
     * Get all canvas boards for a project, defaulting to the wiki canvas type.
     *
     * @param  int  $projectId  Project ID
     * @param  string|null  $type  Canvas type override (defaults to "wikicanvas")
     * @return false|array<int, array<string, mixed>>
     */
    public function getAllCanvas($projectId, $type = null): false|array
    {
        return parent::getAllCanvas((int) $projectId, $type ?: 'wikicanvas');
    }

    /**
     * Create a canvas board, defaulting to the wiki canvas type.
     *
     * @param  array<string, mixed>  $values  Canvas values
     * @param  string|null  $type  Canvas type override (defaults to "wikicanvas")
     */
    public function addCanvas($values, $type = null): false|string
    {
        return parent::addCanvas($values, $type ?: 'wikicanvas');
    }

    /**
     * Get the items for a canvas board, using the wiki comment module.
     *
     * @param  int  $id  Canvas board ID
     * @param  string  $commentModule  Comment module override (defaults to "wikicanvasitem")
     * @return false|array<int, array<string, mixed>>
     */
    public function getCanvasItemsById($id, $commentModule = 'wikicanvasitem'): false|array
    {
        return parent::getCanvasItemsById((int) $id, $commentModule ?: 'wikicanvasitem');
    }

    public function getArticle(int $id, int $projectId): mixed
    {
        $query = $this->dbConnection->table('zp_canvas_items')
            ->select(
                'zp_canvas_items.id',
                'zp_canvas_items.title',
                'zp_canvas_items.description',
                'zp_canvas_items.canvasId',
                'zp_canvas_items.parent',
                'zp_canvas_items.tags',
                'zp_canvas_items.data',
                'zp_canvas_items.status',
                'zp_canvas_items.created',
                'zp_canvas_items.modified',
                'zp_canvas_items.author',
                'zp_canvas_items.milestoneId',
                'zp_user.firstname',
                'zp_user.lastname',
                'zp_user.profileId',
                'zp_canvas_items.sortindex',
                'zp_canvas.projectId',
                'milestone.headline as milestoneHeadline',
                'milestone.editTo as milestoneEditTo'
            )
            ->leftJoin('zp_canvas', 'zp_canvas.id', '=', 'zp_canvas_items.canvasId')
            ->leftJoin('zp_user', 'zp_canvas_items.author', '=', 'zp_user.id')
            ->leftJoin('zp_tickets AS milestone', function ($join) {
                $join->on('zp_canvas_items.milestoneId', '=',
                    $this->dbConnection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('milestone.id'), 'text')));
            })
            ->where('zp_canvas.projectId', $projectId)
            ->where('zp_canvas_items.box', 'article');

        if ($id > 0) {
            $query->where('zp_canvas_items.id', $id);
        } elseif ($id == -1) {
            $query->where('featured', 1);
        }

        $result = $query->limit(1)->first();

        if ($result === null) {
            return false;
        }

        $article = new Article;
        foreach ($result as $key => $value) {
            if (property_exists($article, $key)) {
                $article->$key = $value;
            }
        }

        return $article;
    }

    /**
     * Resolve an article's owning project by its id alone (articles inherit their wiki's project
     * via canvasId -> zp_canvas.projectId). Used by the service to authorize edit/delete/activity
     * against the article's REAL project without trusting a caller-supplied projectId.
     */
    public function getArticleProjectId(int $id): ?int
    {
        $projectId = $this->dbConnection->table('zp_canvas_items')
            ->leftJoin('zp_canvas', 'zp_canvas.id', '=', 'zp_canvas_items.canvasId')
            ->where('zp_canvas_items.id', $id)
            ->where('zp_canvas_items.box', 'article')
            ->value('zp_canvas.projectId');

        return $projectId !== null ? (int) $projectId : null;
    }

    public function getAllProjectWikis(int $projectId): array|false
    {
        $results = $this->dbConnection->table('zp_canvas')
            ->select('id', 'title', 'author', 'created')
            ->where('projectId', $projectId)
            ->where('type', 'wiki')
            ->get();

        return $results->map(function ($row) {
            $wiki = new WikiModel;
            $wiki->id = $row->id;
            $wiki->title = $row->title;
            $wiki->author = $row->author;
            $wiki->created = $row->created;

            return $wiki;
        })->toArray();
    }

    public function getWiki(int $id): mixed
    {
        $result = $this->dbConnection->table('zp_canvas')
            ->select('id', 'title', 'author', 'created', 'projectId')
            ->where('id', $id)
            ->where('type', 'wiki')
            ->first();

        if ($result === null) {
            return false;
        }

        $wiki = new WikiModel;
        $wiki->id = $result->id;
        $wiki->title = $result->title;
        $wiki->author = $result->author;
        $wiki->created = $result->created;
        $wiki->projectId = $result->projectId;

        return $wiki;
    }

    public function getAllWikiHeadlines(int $canvasId, int $userId): false|array
    {
        $results = $this->dbConnection->table('zp_canvas_items')
            ->select('id', 'title', 'parent', 'sortindex', 'status', 'data')
            ->where('canvasId', $canvasId)
            ->where('box', 'article')
            ->where(function ($query) use ($userId) {
                $query->where('status', 'published')
                    ->orWhere(function ($q) use ($userId) {
                        $q->where('status', 'draft')
                            ->where('author', $userId);
                    });
            })
            ->orderBy('parent')
            ->orderBy('title')
            ->get();

        return $results->map(function ($row) {
            $article = new Article;
            $article->id = $row->id;
            $article->title = $row->title;
            $article->parent = $row->parent;
            $article->sortindex = $row->sortindex;
            $article->status = $row->status;
            $article->data = $row->data;

            return $article;
        })->toArray();
    }

    public function createWiki(WikiModel $wiki): false|string
    {
        $id = $this->dbConnection->table('zp_canvas')->insertGetId([
            'title' => $wiki->title,
            'projectId' => $wiki->projectId,
            'author' => $wiki->author,
            'created' => date('Y-m-d'),
            'type' => 'wiki',
        ]);

        return (string) $id;
    }

    public function updateWiki(WikiModel $wiki, int $wikiId): bool
    {
        // type guard: zp_canvas is shared across all canvas types (one id sequence), so scope the
        // write to wiki rows — a non-wiki id can never rename another project's canvas board.
        return $this->dbConnection->table('zp_canvas')
            ->where('id', $wikiId)
            ->where('type', 'wiki')
            ->update(['title' => $wiki->title]) >= 0;
    }

    public function createArticle(Article $article): false|string
    {
        $id = $this->dbConnection->table('zp_canvas_items')->insertGetId([
            'title' => $article->title,
            'description' => $article->description,
            'data' => $article->data,
            'box' => 'article',
            'author' => $article->author,
            'canvasId' => $article->canvasId,
            'parent' => $article->parent,
            'tags' => $article->tags,
            'status' => $article->status,
            'created' => date('Y-m-d'),
            'modified' => date('Y-m-d'),
            'sortindex' => '10',
        ]);

        return (string) $id;
    }

    public function updateArticle(Article $article): bool
    {
        // box guard: zp_canvas_items is shared across all canvas types (one id sequence), so scope
        // the write to article rows — a non-article id can never touch a goal/SWOT/risk item.
        return $this->dbConnection->table('zp_canvas_items')
            ->where('id', $article->id)
            ->where('box', 'article')
            ->update([
                'title' => $article->title,
                'description' => $article->description,
                'data' => $article->data,
                'parent' => $article->parent,
                'tags' => $article->tags,
                'status' => $article->status,
                'modified' => date('Y-m-d'),
                'milestoneId' => $article->milestoneId,
            ]) >= 0;
    }

    public function delArticle(int $id): void
    {
        // box guard (shared zp_canvas_items): only ever delete an article row by this id.
        $this->dbConnection->table('zp_canvas_items')
            ->where('id', $id)
            ->where('box', 'article')
            ->delete();
    }

    public function delWiki(int $id): void
    {
        $this->dbConnection->table('zp_canvas_items')
            ->where('canvasId', $id)
            ->delete();

        // type guard (shared zp_canvas): only ever delete a wiki board by this id.
        $this->dbConnection->table('zp_canvas')
            ->where('id', $id)
            ->where('type', 'wiki')
            ->delete();
    }

    /**
     * Count wiki boards, optionally scoped to a project.
     *
     * @param  int|null  $projectId  Project ID (null counts across all projects)
     * @param  string  $canvasType  Canvas type override (defaults to the "wiki" board type)
     * @return int|mixed
     */
    public function getNumberOfBoards($projectId = null, $canvasType = 'wiki'): mixed
    {
        $query = $this->dbConnection->table('zp_canvas')
            ->where('type', $canvasType ?: 'wiki');

        if ($projectId !== null) {
            $query->where('projectId', $projectId);
        }

        return $query->count();
    }

    /**
     * Count wiki canvas items, optionally scoped to a project.
     *
     * @param  int|null  $projectId  Project ID (null counts across all projects)
     * @param  string  $canvasType  Canvas type override (defaults to the "wiki" board type)
     * @return int|mixed
     */
    public function getNumberOfCanvasItems($projectId = null, $canvasType = 'wiki'): mixed
    {
        $query = $this->dbConnection->table('zp_canvas_items')
            ->leftJoin('zp_canvas AS canvasBoard', 'zp_canvas_items.canvasId', '=', 'canvasBoard.id')
            ->where('canvasBoard.type', $canvasType ?: 'wiki');

        if ($projectId !== null) {
            $query->where('canvasBoard.projectId', $projectId);
        }

        return $query->count('zp_canvas_items.id');
    }
}
