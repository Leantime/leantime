<?php

namespace Leantime\Domain\Strategy\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;

/**
 * @api
 */
class Strategy
{
    /**
     * @param  BlueprintsService  $blueprintsService  Blueprints service used to read canvas progress and recently updated boards.
     */
    public function __construct(
        private BlueprintsService $blueprintsService
    ) {}

    /**
     * Returns the metadata map for every selectable strategy board (canvas) type.
     *
     * Each entry holds the routing module, the translatable name/description labels,
     * an icon class and the (empty) placeholders used when no board of that type exists yet.
     *
     * @return array<string, array<string, string>> Board type keyed metadata map.
     */
    public function getBoardMetadata(): array
    {
        return [
            'valuecanvas' => ['module' => 'blueprints/value',       'name' => 'label.valuecanvas',  'description' => 'description.valuecanvas', 'icon' => 'fa-solid fa-ranking-star',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'swotcanvas' => ['module' => 'blueprints/swot',     'name' => 'label.swotcanvas', 'description' => 'description.swotcanvas', 'icon' => 'fa-solid fa-dumbbell',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'obmcanvas' => ['module' => 'blueprints/obm',     'name' => 'label.obmcanvas',       'description' => 'description.obmcanvas', 'icon' => 'fa-solid fa-object-group', 'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'leancanvas' => ['module' => 'blueprints/lean',     'name' => 'label.leancanvas',       'description' => 'description.leancanvas', 'icon' => 'fa-solid fa-person-circle-question', 'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'minempathycanvas' => ['module' => 'blueprints/minempathy',       'name' => 'label.minempathycanvas',  'description' => 'description.minempathycanvas', 'icon' => 'fa-solid fa-heart-circle-check',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'sbcanvas' => ['module' => 'blueprints/sb',       'name' => 'label.sbcanvas',  'description' => 'description.sbcanvas',           'icon' => 'fa-solid fa-briefcase',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'riskscanvas' => ['module' => 'blueprints/risks',    'name' => 'label.riskscanvas',  'description' => 'description.riskscanvas',        'icon' => 'fa-solid fa-triangle-exclamation',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'eacanvas' => ['module' => 'blueprints/ea',       'name' => 'label.eacanvas', 'description' => 'description.eacanvas', 'icon' => 'fa-solid fa-seedling',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'lbmcanvas' => ['visible' => '0', 'module' => 'blueprints/lbm',      'name' => 'label.lbmcanvas', 'description' => 'description.lbmcanvas', 'icon' => 'fa-solid fa-building',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'dbmcanvas' => ['visible' => '0', 'module' => 'blueprints/dbm',      'name' => 'label.dbmcanvas', 'description' => 'description.dbmcanvas', 'icon' => 'fa-solid fa-city',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'sqcanvas' => ['visible' => '0', 'module' => 'blueprints/sq',       'name' => 'label.sqcanvas', 'description' => 'description.sqcanvas', 'icon' => 'fa fa-chess',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'insightscanvas' => ['module' => 'blueprints/insights', 'name' => 'label.insightscanvas', 'description' => 'description.insightscanvas',      'icon' => 'fa-solid fa-arrows-down-to-people',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'cpcanvas' => ['visible' => '0', 'module' => 'blueprints/cp',       'name' => 'label.cpcanvas', 'description' => 'description.cpcanvas', 'icon' => 'fa-solid fa-list-check',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'smcanvas' => ['visible' => '0', 'module' => 'blueprints/sm',       'name' => 'label.smcanvas', 'description' => 'description.smcanvas', 'icon' => 'fa-solid fa-comments-dollar',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
            'emcanvas' => ['visible' => '0', 'module' => 'blueprints/em',       'name' => 'label.emcanvas', 'description' => 'description.emcanvas', 'icon' => 'fa-solid fa-hand-holding-heart',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastlastUpdate' => ''],
        ];
    }

    /**
     * Returns the ordered list of strategy board (canvas) types used to query progress and recent activity.
     *
     * @return array<int, string> List of board type keys.
     */
    public function getBoardTypes(): array
    {
        return [
            'emcanvas', 'smcanvas', 'cpcanvas', 'insightscanvas',
            'sqcanvas', 'dbmcanvas', 'lbmcanvas', 'eacanvas', 'riskscanvas', 'sbcanvas',
            'swotcanvas', 'obmcanvas', 'valuecanvas', 'leancanvas', 'minempathycanvas',
        ];
    }

    /**
     * Merges the recently updated canvas boards into the board metadata map.
     *
     * For the first occurrence of a board type the metadata entry is seeded with the
     * latest board's count, title, modified date and id, and that type is removed from the
     * remaining "other" board list. Subsequent occurrences only increment the count.
     *
     * @param  array<int, array<string, mixed>>  $recentlyUpdatedCanvas  Canvas rows ordered by last updated item.
     * @param  array<string, array<string, string>>  $boardMetadata  Board type keyed metadata map (passed by reference so the consumed types are removed).
     * @return array<string, array<string, mixed>> The recently used board metadata keyed by board type.
     */
    public function buildRecentProgressCanvas(array $recentlyUpdatedCanvas, array &$boardMetadata): array
    {
        $recentProgressCanvas = [];

        foreach ($recentlyUpdatedCanvas as $canvas) {
            if (! isset($recentProgressCanvas[$canvas['type']])) {
                $recentProgressCanvas[$canvas['type']] = $boardMetadata[$canvas['type']];
                $recentProgressCanvas[$canvas['type']]['count'] = 1;
                $recentProgressCanvas[$canvas['type']]['lastTitle'] = $canvas['title'];
                $recentProgressCanvas[$canvas['type']]['lastUpdate'] = $canvas['modified'];
                $recentProgressCanvas[$canvas['type']]['lastCanvasId'] = $canvas['id'];
                unset($boardMetadata[$canvas['type']]);
            } else {
                $recentProgressCanvas[$canvas['type']]['count']++;
            }
        }

        return $recentProgressCanvas;
    }

    /**
     * Builds the strategy boards overview for a project.
     *
     * Loads the recently updated boards and board progress for the project, merges the recent
     * activity into the board metadata and returns a ready-to-render structure for the boards page.
     *
     * @param  int  $projectId  Active project identifier.
     * @return array{recentProgressCanvas: array<string, array<string, mixed>>, otherBoards: array<string, array<string, string>>, recentlyUpdatedCanvas: array<int, array<string, mixed>>, canvasProgress: array<string, float|string>} Render-ready overview data.
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function getStrategyBoardsOverview(int $projectId): array
    {
        $boardMetadata = $this->getBoardMetadata();
        $boards = $this->getBoardTypes();

        $recentlyUpdatedCanvas = $this->blueprintsService->getLastUpdatedCanvas($projectId, $boards);

        $recentProgressCanvas = $this->buildRecentProgressCanvas($recentlyUpdatedCanvas, $boardMetadata);

        $canvasProgress = $this->blueprintsService->getBoardProgress((string) $projectId, $boards);

        return [
            'recentProgressCanvas' => $recentProgressCanvas,
            'otherBoards' => $boardMetadata,
            'recentlyUpdatedCanvas' => $recentlyUpdatedCanvas,
            'canvasProgress' => $canvasProgress,
        ];
    }
}
