<?php

namespace Leantime\Domain\ContentTemplates\Services\Appliers;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\ContentTemplates\Contracts\Applier;
use Leantime\Domain\ContentTemplates\Models\ContentTemplate;

/**
 * Applier for wiki content templates.
 *
 * Wiki articles share the zp_canvas_items table with all canvas types,
 * distinguished by box='article'. Articles can nest via the `parent` column,
 * which the template's `children:` arrays drive.
 *
 * Expected payload shape:
 *
 *     articles:
 *       - title: "Project Overview"
 *         content: "<h1>...</h1>"     # HTML body
 *         children:
 *           - title: "Subtopic A"
 *             content: "..."
 *             children: []             # recurses arbitrarily deep
 *
 * Mode 'replace' wipes all box='article' rows under the wiki before inserting.
 * Mode 'add' (default) appends, preserving any existing articles.
 */
class WikiApplier implements Applier
{
    /** Resolved on first use so a stubbed DbCore in unit tests doesn't fault. */
    private ?ConnectionInterface $db = null;

    public function __construct(private DbCore $dbCore) {}

    public function supports(string $appliesTo): bool
    {
        return $appliesTo === 'wiki';
    }

    public function apply(int $targetId, ContentTemplate $template, array $options = []): int
    {
        if ($targetId <= 0 || ! $template->isUsable()) {
            return 0;
        }

        $articles = (array) ($template->payload['articles'] ?? []);
        if ($articles === []) {
            return 0;
        }

        $userId = (int) ($options['userId'] ?? 0);
        $mode = $options['mode'] ?? 'add';

        if ($mode === 'replace') {
            $this->db()->table('zp_canvas_items')
                ->where('canvasId', $targetId)
                ->where('box', 'article')
                ->delete();
        }

        return $this->insertArticles($articles, $targetId, $userId, parent: 0);
    }

    /**
     * Insert a list of articles under a given parent, recursing into children.
     *
     * @param  list<array<string, mixed>>  $articles
     * @param  int  $parent  Parent article id (0 for top-level).
     * @return int Total articles created (this level + all descendants).
     */
    private function insertArticles(array $articles, int $wikiId, int $userId, int $parent): int
    {
        $created = 0;
        $sortBase = 10;
        $db = $this->db();

        foreach ($articles as $offset => $article) {
            if (! is_array($article)) {
                continue;
            }
            $id = (int) $db->table('zp_canvas_items')->insertGetId([
                'canvasId' => $wikiId,
                'box' => 'article',
                'title' => (string) ($article['title'] ?? ''),
                'description' => (string) ($article['content'] ?? ''),
                'parent' => $parent,
                'author' => $userId,
                'created' => now(),
                'modified' => now(),
                'sortindex' => $sortBase + $offset * 10,
            ]);
            $created++;

            $children = (array) ($article['children'] ?? []);
            if ($id > 0 && $children !== []) {
                $created += $this->insertArticles($children, $wikiId, $userId, $id);
            }
        }

        return $created;
    }

    private function db(): ConnectionInterface
    {
        return $this->db ??= $this->dbCore->getConnection();
    }
}
