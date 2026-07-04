<?php

namespace Leantime\Domain\ContentTemplates\Services\Appliers;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\ContentTemplates\Contracts\Applier;
use Leantime\Domain\ContentTemplates\Models\ContentTemplate;

/**
 * Applier for canvas-typed content templates.
 *
 * Handles any canvas type whose items live in zp_canvas_items (Logic Model,
 * Goal Canvas, Lean Canvas, SWOT, etc.). The appliesTo string is the canvas
 * type slug — same slug Blueprints' TemplateRegistry uses. supports() returns
 * true for everything except known non-canvas appliesTo values (e.g. "wiki"),
 * so this is the catch-all canvas applier.
 *
 * Expected payload shape:
 *
 *     items:
 *       - box: "lm_inputs"        # required, canvas-type box key
 *         title: "..."            # populates description (the bold line)
 *         description: "..."      # populates conclusion (the supporting prose)
 *         status: "status_draft"  # optional, defaults to ''
 *         sortindex: 10           # optional, auto-assigned by insertion order if absent
 *
 * The unusual title→description / description→conclusion mapping matches the
 * Canvas item display convention: description is rendered as the bold card
 * title, conclusion as the lighter supporting text.
 */
class CanvasItemsApplier implements Applier
{
    /** Non-canvas appliesTo values this applier explicitly refuses. */
    private const NON_CANVAS = ['wiki'];

    /** Resolved on first use so a stubbed DbCore in unit tests doesn't fault. */
    private ?ConnectionInterface $db = null;

    public function __construct(private DbCore $dbCore) {}

    public function supports(string $appliesTo): bool
    {
        return $appliesTo !== '' && ! in_array($appliesTo, self::NON_CANVAS, true);
    }

    public function apply(int $targetId, ContentTemplate $template, array $options = []): int
    {
        if ($targetId <= 0 || ! $template->isUsable()) {
            return 0;
        }

        $items = (array) ($template->payload['items'] ?? []);
        if ($items === []) {
            return 0;
        }

        $db = $this->db();
        $userId = (int) ($options['userId'] ?? 0);
        $mode = $options['mode'] ?? 'add';

        if ($mode === 'replace') {
            $db->table('zp_canvas_items')
                ->where('canvasId', $targetId)
                ->delete();
        }

        $sortBase = $this->nextSortIndex($targetId);
        $created = 0;

        foreach ($items as $offset => $item) {
            if (! is_array($item) || empty($item['box'])) {
                continue;
            }
            $db->table('zp_canvas_items')->insert([
                'canvasId' => $targetId,
                'box' => (string) $item['box'],
                'description' => (string) ($item['title'] ?? ''),
                'conclusion' => (string) ($item['description'] ?? ''),
                'status' => (string) ($item['status'] ?? ''),
                'author' => $userId,
                'created' => now(),
                'sortindex' => (int) ($item['sortindex'] ?? ($sortBase + $offset * 10)),
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * Find the next free sortindex on this canvas, leaving room between entries.
     * Returns at least 10 so the first item is sortindex >= 10.
     */
    private function nextSortIndex(int $canvasId): int
    {
        $max = (int) $this->db()->table('zp_canvas_items')
            ->where('canvasId', $canvasId)
            ->max('sortindex');

        return $max > 0 ? $max + 10 : 10;
    }

    private function db(): ConnectionInterface
    {
        return $this->db ??= $this->dbCore->getConnection();
    }
}
