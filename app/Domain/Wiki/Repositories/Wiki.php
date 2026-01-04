<?php

namespace Leantime\Domain\Wiki\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Canvas\Repositories\Canvas;
use Leantime\Domain\Tickets\Repositories\Tickets;
use Leantime\Domain\Wiki\Models\Article;
use Leantime\Domain\Wiki\Models\Wiki as WikiModel;

class Wiki extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'wiki';

    protected ConnectionInterface $dbConnection;

    public function __construct(DbCore $db, LanguageCore $language, Tickets $ticketRepo, DatabaseHelper $dbHelper)
    {
        parent::__construct($db, $language, $ticketRepo, $dbHelper);
        $this->dbConnection = $db->getConnection();
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
            ->leftJoin('zp_tickets AS milestone', 'milestone.id', '=', 'zp_canvas_items.milestoneId')
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
        return $this->dbConnection->table('zp_canvas')
            ->where('id', $wikiId)
            ->limit(1)
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
            'sortIndex' => '10',
        ]);

        return (string) $id;
    }

    public function updateArticle(Article $article): bool
    {
        return $this->dbConnection->table('zp_canvas_items')
            ->where('id', $article->id)
            ->limit(1)
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
        $this->dbConnection->table('zp_canvas_items')
            ->where('id', $id)
            ->limit(1)
            ->delete();
    }

    public function delWiki(int $id): void
    {
        $this->dbConnection->table('zp_canvas_items')
            ->where('canvasId', $id)
            ->delete();

        $this->dbConnection->table('zp_canvas')
            ->where('id', $id)
            ->limit(1)
            ->delete();
    }

    /**
     * @return int|mixed
     */
    public function getNumberOfBoards($projectId = null): mixed
    {
        $query = $this->dbConnection->table('zp_canvas')
            ->where('type', 'wiki');

        if ($projectId !== null) {
            $query->where('projectId', $projectId);
        }

        return $query->count();
    }

    /**
     * @return int|mixed
     */
    public function getNumberOfCanvasItems($projectId = null): mixed
    {
        $query = $this->dbConnection->table('zp_canvas_items')
            ->leftJoin('zp_canvas AS canvasBoard', 'zp_canvas_items.canvasId', '=', 'canvasBoard.id')
            ->where('canvasBoard.type', 'wiki');

        if ($projectId !== null) {
            $query->where('canvasBoard.projectId', $projectId);
        }

        return $query->count('zp_canvas_items.id');
    }
}
