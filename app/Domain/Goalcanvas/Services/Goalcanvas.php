<?php

namespace Leantime\Domain\Goalcanvas\Services;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Goalcanvas\Permissions\GoalcanvasPermissions;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas as GoalcanvaRepository;

/**
 * Goalcanvas (Goals / OKRs) service.
 *
 * Goal boards/items live in the shared zp_canvas (type "goalcanvas") / zp_canvas_items (box
 * "goal") tables. Every by-id board/item operation routes through this service, which resolves
 * the entity's REAL project via the repository's fail-closed resolvers (inherited from the
 * Blueprints repo, scoped to the "goalcanvas" type) and authorizes the matching
 * {@see GoalcanvasPermissions} verb against it. A resolver returning null (missing id, or an id
 * whose board is a different canvas type) is treated as DENY — reads soft-deny (neutral value)
 * so they are not a cross-project existence oracle; writes throw an AuthorizationException
 * without writing. Never falls back to the session project for a by-id entity.
 *
 * @api
 */
class Goalcanvas extends BaseService
{
    /** Database canvas type for goal boards (CANVAS_NAME "goal" + "canvas"). */
    private const CANVAS_TYPE = 'goalcanvas';

    private GoalcanvaRepository $goalRepository;

    public array $reportingSettings = [
        'linkonly',
        'linkAndReport',
        'nolink',
    ];

    public function __construct(GoalcanvaRepository $goalRepository)
    {
        $this->goalRepository = $goalRepository;
    }

    /**
     * List the goals on a board (by board id), authorized for VIEW against the board's project.
     * Returns [] for a missing/foreign/unauthorized board (neutral — no oracle).
     *
     * @api
     */
    #[RequiresPermission(GoalcanvasPermissions::VIEW, entityScoped: true)]
    public function getCanvasItemsById(int $id): array
    {
        $projectId = $this->goalRepository->getCanvasProjectId($id, self::CANVAS_TYPE);
        if ($projectId === null || ! $this->can(GoalcanvasPermissions::VIEW, $projectId)) {
            return [];
        }

        $goals = $this->goalRepository->getCanvasItemsById($id);

        if ($goals) {
            foreach ($goals as &$goal) {
                $progressValue = 0;
                $goal['goalProgress'] = 0;
                $total = $goal['endValue'] - $goal['startValue'];
                // Skip if start and end are the same (no range to measure).
                if ($total == 0) {
                    continue;
                }

                if ($goal['setting'] == 'linkAndReport') {
                    // GetAll Child elements
                    $currentValueSum = $this->getChildGoalsForReporting($goal['id']);

                    $goal['currentValue'] = $currentValueSum;
                    $progressValue = $currentValueSum - $goal['startValue'];
                } else {
                    $progressValue = $goal['currentValue'] - $goal['startValue'];
                }

                $goal['goalProgress'] = max(0, min(100, round($progressValue / $total, 2) * 100));
            }
        }

        return $goals;
    }

    /**
     * Sum the linked children's current values for a parent goal, authorized for VIEW against
     * the PARENT goal's project. The cross-project roll-up of linked children is the feature;
     * the gate is on the parent the caller asked about. Returns 0 for a missing/foreign/
     * unauthorized parent.
     *
     * @return int|mixed
     *
     * @api
     */
    #[RequiresPermission(GoalcanvasPermissions::VIEW, entityScoped: true)]
    public function getChildGoalsForReporting($parentId): mixed
    {
        $projectId = $this->goalRepository->getCanvasItemProjectId((int) $parentId, self::CANVAS_TYPE);
        if ($projectId === null || ! $this->can(GoalcanvasPermissions::VIEW, $projectId)) {
            return 0;
        }

        // Goals come back as rows for levl1 and lvl2 being columns, so
        // goal A | goalChildA
        // goal A | goalChildB
        // goal B
        // Checks if first level is also link+report or just link
        $goals = $this->goalRepository->getCanvasItemsByKPI($parentId);
        $currentValueSum = 0;
        foreach ($goals as $child) {
            if ($child['setting'] == 'linkAndReport') {
                $currentValueSum = $currentValueSum + $child['childCurrentValue'];
            } else {
                $currentValueSum = $currentValueSum + $child['currentValue'];
            }
        }

        return $currentValueSum;
    }

    /**
     * The child-goal hierarchy for a parent goal, authorized for VIEW against the PARENT goal's
     * project. Returns [] for a missing/foreign/unauthorized parent.
     *
     * @api
     */
    #[RequiresPermission(GoalcanvasPermissions::VIEW, entityScoped: true)]
    public function getChildrenbyKPI($parentId): array
    {
        $projectId = $this->goalRepository->getCanvasItemProjectId((int) $parentId, self::CANVAS_TYPE);
        if ($projectId === null || ! $this->can(GoalcanvasPermissions::VIEW, $projectId)) {
            return [];
        }

        $goals = [];
        // Goals come back as rows for levl1 and lvl2 being columns, so
        // goal A | goalChildA
        // goal A | goalChildB
        // goal B
        // Checks if first level is also link+report or just link
        $children = $this->goalRepository->getCanvasItemsByKPI($parentId);

        foreach ($children as $child) {
            // Added Child already? Look for child of child
            if (! isset($goals[$child['id']])) {
                $goals[$child['id']] = [
                    'id' => $child['id'],
                    'title' => $child['title'],
                    'startValue' => $child['startValue'],
                    'endValue' => $child['endValue'],
                    'currentValue' => $child['currentValue'],
                    'metricType' => $child['metricType'],
                    'boardTitle' => $child['boardTitle'],
                    'canvasId' => $child['canvasId'],
                    'projectName' => $child['projectName'],
                ];
            }

            if ($child['childId'] != '') {
                if (isset($goals[$child['childId']]) === false) {
                    $goals[$child['childId']] = [
                        'id' => $child['childId'],
                        'title' => $child['childTitle'],
                        'startValue' => $child['childStartValue'],
                        'endValue' => $child['childEndValue'],
                        'currentValue' => $child['childCurrentValue'],
                        'metricType' => $child['childMetricType'],
                        'boardTitle' => $child['childBoardTitle'],
                        'canvasId' => $child['childCanvasId'],
                        'projectName' => $child['childProjectName'],
                    ];
                }
            }
        }

        return $goals;
    }

    /**
     * Available parent KPIs for linking, authorized for VIEW against $projectId (dispatch gate).
     *
     * @api
     */
    #[RequiresPermission(GoalcanvasPermissions::VIEW, projectIdParam: 'projectId')]
    public function getParentKPIs($projectId): array
    {
        $kpis = $this->goalRepository->getAllAvailableKPIs($projectId);

        $goals = [];

        // Checks if first level is also link+report or just link
        foreach ($kpis as $kpi) {
            $goals[$kpi['id']] = [
                'id' => $kpi['id'],
                'description' => $kpi['description'],
                'project' => $kpi['projectName'],
                'board' => $kpi['boardTitle'],
            ];
        }

        return $goals;
    }

    /**
     * Goals linked to a milestone. Internal read used by the milestone UI; the data-access is
     * the milestone the caller is already viewing.
     */
    public function getGoalsByMilestone($milestoneId): array
    {
        $goals = $this->goalRepository->getGoalsByMilestone($milestoneId);

        return $goals;
    }

    // ---------------------------------------------------------------------------------------
    // Secured by-id board/item CRUD chokepoint (controllers call these instead of the repo).
    // ---------------------------------------------------------------------------------------

    /**
     * Fetch a single goal item by id, authorized for VIEW against the item's real project.
     *
     * @return array<string, mixed>|false False when missing/foreign/unauthorized.
     */
    public function getGoalItem(int $id): array|false
    {
        $projectId = $this->goalRepository->getCanvasItemProjectId($id, self::CANVAS_TYPE);
        if ($projectId === null || ! $this->can(GoalcanvasPermissions::VIEW, $projectId)) {
            return false;
        }

        return $this->goalRepository->getSingleCanvasItem($id);
    }

    /**
     * Fetch a single goal board by id, authorized for VIEW against the board's real project.
     *
     * @return array<string, mixed>|false False when missing/foreign/unauthorized.
     */
    public function getSingleCanvas($id)
    {
        $projectId = $this->goalRepository->getCanvasProjectId((int) $id, self::CANVAS_TYPE);
        if ($projectId === null || ! $this->can(GoalcanvasPermissions::VIEW, $projectId)) {
            return false;
        }

        return $this->goalRepository->getSingleCanvas((int) $id);
    }

    /**
     * Create a goal board, authorized for CREATE against the target project.
     *
     * @throws AuthorizationException When projectId is missing or CREATE is denied.
     */
    public function createGoalboard($values)
    {
        $projectId = (int) ($values['projectId'] ?? 0);
        if ($projectId === 0) {
            throw new AuthorizationException;
        }
        $this->authorize(GoalcanvasPermissions::CREATE, $projectId);

        return $this->goalRepository->addCanvas($values);
    }

    /**
     * Rename a goal board, authorized for EDIT against the board's real project.
     *
     * @throws AuthorizationException When the board is unknown/foreign or EDIT is denied.
     */
    public function updateGoalboard($values)
    {
        $projectId = $this->goalRepository->getCanvasProjectId((int) ($values['id'] ?? 0), self::CANVAS_TYPE);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(GoalcanvasPermissions::EDIT, $projectId);

        return $this->goalRepository->updateCanvas($values);
    }

    /**
     * Copy a goal board into a target project. Requires VIEW on the source board's project and
     * CREATE in the target project.
     *
     * @throws AuthorizationException When the source is unknown/foreign, or VIEW/CREATE is denied.
     */
    public function copyGoalBoard(int $sourceCanvasId, int $targetProjectId, int $authorId, string $title): int
    {
        $sourceProjectId = $this->goalRepository->getCanvasProjectId($sourceCanvasId, self::CANVAS_TYPE);
        if ($sourceProjectId === null || $targetProjectId <= 0) {
            throw new AuthorizationException;
        }
        $this->authorize(GoalcanvasPermissions::VIEW, $sourceProjectId);
        $this->authorize(GoalcanvasPermissions::CREATE, $targetProjectId);

        return $this->goalRepository->copyCanvas($targetProjectId, $sourceCanvasId, $authorId, $title);
    }

    /**
     * Merge a source goal board's items into a target board. Requires EDIT on the target board's
     * project and VIEW on the source board's project — both resolved by id.
     *
     * @throws AuthorizationException When either board is unknown/foreign, or EDIT/VIEW is denied.
     */
    public function mergeGoalBoard(int $targetCanvasId, int $sourceCanvasId): bool
    {
        $targetProjectId = $this->goalRepository->getCanvasProjectId($targetCanvasId, self::CANVAS_TYPE);
        $sourceProjectId = $this->goalRepository->getCanvasProjectId($sourceCanvasId, self::CANVAS_TYPE);
        if ($targetProjectId === null || $sourceProjectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(GoalcanvasPermissions::EDIT, $targetProjectId);
        $this->authorize(GoalcanvasPermissions::VIEW, $sourceProjectId);

        return $this->goalRepository->mergeCanvas($targetCanvasId, $sourceCanvasId);
    }

    /**
     * Delete a goal board (and its items), authorized for DELETE against the board's real project.
     *
     * @throws AuthorizationException When the board is unknown/foreign or DELETE is denied.
     */
    public function deleteGoalBoard(int $canvasId): void
    {
        $projectId = $this->goalRepository->getCanvasProjectId($canvasId, self::CANVAS_TYPE);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(GoalcanvasPermissions::DELETE, $projectId);

        $this->goalRepository->deleteCanvas($canvasId);
    }

    /**
     * Create a goal item, authorized for CREATE against the target board's real project.
     *
     * @param  array<string, mixed>  $values  Item values (must include `canvasId`)
     * @return false|string New item id, or false on insert failure
     *
     * @throws AuthorizationException When the target board is unknown/foreign or CREATE is denied.
     *
     * @api
     */
    #[RequiresPermission(GoalcanvasPermissions::CREATE, entityScoped: true)]
    public function createGoal($values)
    {
        $projectId = $this->goalRepository->getCanvasProjectId((int) ($values['canvasId'] ?? 0), self::CANVAS_TYPE);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(GoalcanvasPermissions::CREATE, $projectId);

        return $this->goalRepository->createGoal($values);
    }

    /**
     * Create a goal item (controller add-item path), authorized for CREATE against the target
     * board's real project.
     *
     * @param  array<string, mixed>  $values  Item values (must include `canvasId`)
     * @return false|string New item id, or false on insert failure
     *
     * @throws AuthorizationException When the target board is unknown/foreign or CREATE is denied.
     */
    public function createGoalItem(array $values): false|string
    {
        $projectId = $this->goalRepository->getCanvasProjectId((int) ($values['canvasId'] ?? 0), self::CANVAS_TYPE);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(GoalcanvasPermissions::CREATE, $projectId);

        return $this->goalRepository->addCanvasItem($values);
    }

    /**
     * Update a goal item, authorized for EDIT against the item's real project. The project is
     * resolved from the existing item's id, so the payload's canvasId cannot relocate it.
     *
     * @param  array<string, mixed>  $values  Item values (must include `itemId` or `id`)
     *
     * @throws AuthorizationException When the item is unknown/foreign or EDIT is denied.
     */
    public function updateGoalItem(array $values): void
    {
        $itemId = (int) ($values['itemId'] ?? $values['id'] ?? 0);
        $projectId = $this->goalRepository->getCanvasItemProjectId($itemId, self::CANVAS_TYPE);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(GoalcanvasPermissions::EDIT, $projectId);

        $this->goalRepository->editCanvasItem($values);
    }

    /**
     * Patch allowlisted columns of a goal item, authorized for EDIT against the item's real
     * project.
     *
     * @param  array<string, mixed>  $params  Fields to patch (allowlisted in the repository)
     *
     * @throws AuthorizationException When the item is unknown/foreign or EDIT is denied.
     */
    public function patchGoalItem(int $id, array $params): bool
    {
        $projectId = $this->goalRepository->getCanvasItemProjectId($id, self::CANVAS_TYPE);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(GoalcanvasPermissions::EDIT, $projectId);

        return $this->goalRepository->patchCanvasItem($id, $params);
    }

    /**
     * Delete a goal item, authorized for DELETE against the item's real project.
     *
     * @throws AuthorizationException When the item is unknown/foreign or DELETE is denied.
     */
    public function deleteGoalItem(int $id): void
    {
        $projectId = $this->goalRepository->getCanvasItemProjectId($id, self::CANVAS_TYPE);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(GoalcanvasPermissions::DELETE, $projectId);

        $this->goalRepository->delCanvasItem($id);
    }

    /**
     * Poll all goals the user can access (optionally scoped to a project/board). The repository
     * query already filters to the user's accessible projects; the dispatch gate adds the
     * capability check.
     *
     * @return array
     *
     * @api
     */
    #[RequiresPermission(GoalcanvasPermissions::VIEW, projectIdParam: 'projectId')]
    public function pollGoals(?int $projectId = null, ?int $board = null)
    {
        $goals = $this->goalRepository->getAllAccountGoals($projectId, $board);

        foreach ($goals as $key => $goal) {
            $goals[$key] = $this->prepareDatesForApiResponse($goal);
        }

        return $goals;
    }

    /**
     * @return array
     *
     * @api
     */
    #[RequiresPermission(GoalcanvasPermissions::VIEW, projectIdParam: 'projectId')]
    public function pollForUpdatedGoals(?int $projectId = null, ?int $board = null): array|false
    {
        $goals = $this->goalRepository->getAllAccountGoals($projectId, $board);

        foreach ($goals as $key => $goal) {
            $goals[$key] = $this->prepareDatesForApiResponse($goal);
            $goals[$key]['id'] = $goal['id'].'-'.$goal['modified'];
        }

        return $goals;
    }

    private function prepareDatesForApiResponse($goal)
    {
        if (dtHelper()->isValidDateString($goal['created'])) {
            $goal['created'] = dtHelper()->parseDbDateTime($goal['created'])->toIso8601ZuluString();
        } else {
            $goal['created'] = null;
        }

        if (dtHelper()->isValidDateString($goal['modified'])) {
            $goal['modified'] = dtHelper()->parseDbDateTime($goal['modified'])->toIso8601ZuluString();
        } else {
            $goal['modified'] = null;
        }

        if (dtHelper()->isValidDateString($goal['startDate'])) {
            $goal['startDate'] = dtHelper()->parseDbDateTime($goal['startDate'])->toIso8601ZuluString();
        } else {
            $goal['startDate'] = null;
        }

        if (dtHelper()->isValidDateString($goal['endDate'])) {
            $goal['endDate'] = dtHelper()->parseDbDateTime($goal['endDate'])->toIso8601ZuluString();
        } else {
            $goal['endDate'] = null;
        }

        return $goal;
    }
}
