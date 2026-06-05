<?php

declare(strict_types=1);

namespace Leantime\Domain\Blueprints\Services;

use DOMDocument;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\ContentTemplates\Services\ContentTemplateRegistry;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * Blueprints service - business logic for unified canvas boards.
 *
 * Replaces the old Canvas\Services\Canvas by using the Blueprints repo
 * and TemplateRegistry directly instead of dynamically resolving variant repos.
 *
 * Authorization: canvas boards and items are PROJECT-scoped (each board belongs to one
 * project; items belong to a board). Every by-id board/item operation routes through this
 * service, which resolves the entity's REAL project via the repository's fail-closed
 * resolvers and authorizes the matching {@see BlueprintsPermissions} verb against it. A
 * resolver returning null (missing id, or an id whose board is a different canvas type — the
 * shared `zp_canvas`/`zp_canvas_items` tables hold every variant under one id sequence) is
 * treated as DENY, never as "fall back to the session project". Controllers therefore call
 * these methods instead of the repository directly.
 *
 * @api
 */
class Blueprints extends BaseService
{
    private BlueprintsRepository $blueprintsRepo;

    private TemplateRegistry $templateRegistry;

    private LanguageCore $language;

    private ContentTemplateRegistry $contentTemplates;

    /**
     * @param  BlueprintsRepository  $blueprintsRepo  Blueprints repository
     * @param  TemplateRegistry  $templateRegistry  Canvas template registry
     * @param  LanguageCore  $language  Language service for translations
     * @param  ContentTemplateRegistry  $contentTemplates  Content templates registry — used to auto-apply a blueprint's optional startContent on board creation
     */
    public function __construct(
        BlueprintsRepository $blueprintsRepo,
        TemplateRegistry $templateRegistry,
        LanguageCore $language,
        ContentTemplateRegistry $contentTemplates,
    ) {
        $this->blueprintsRepo = $blueprintsRepo;
        $this->templateRegistry = $templateRegistry;
        $this->language = $language;
        $this->contentTemplates = $contentTemplates;
    }

    // ---------------------------------------------------------------------------------------
    // Secured by-id board/item CRUD chokepoint.
    //
    // Controllers call these instead of the repository so authorization happens against the
    // entity's REAL project. Reads soft-deny (return the same neutral value as "missing") so
    // they never become a cross-project existence oracle; writes fail closed with an
    // AuthorizationException. These are intentionally NOT @api — they are the canvas
    // controllers' write surface, not part of the JSON-RPC API.
    // ---------------------------------------------------------------------------------------

    /**
     * Fetch a single canvas item by id, authorized for VIEW against the item's real project.
     *
     * @param  int  $id  Canvas item id
     * @param  string  $canvasType  Board type the item must belong to (e.g. "swotcanvas")
     * @return array<string, mixed>|false The item, or false when missing/foreign/unauthorized
     */
    public function getCanvasItem(int $id, string $canvasType): array|false
    {
        $projectId = $this->blueprintsRepo->getCanvasItemProjectId($id, $canvasType);
        if ($projectId === null || ! $this->can(BlueprintsPermissions::VIEW, $projectId)) {
            return false;
        }

        return $this->blueprintsRepo->getSingleCanvasItem($id);
    }

    /**
     * Fetch a single canvas board by id, authorized for VIEW against the board's real project.
     *
     * @param  int  $canvasId  Canvas board id
     * @param  string  $canvasType  Board type the board must be of
     * @return array<int, array<string, mixed>>|false Board rows, or false when missing/foreign/unauthorized
     */
    public function getBoard(int $canvasId, string $canvasType): array|false
    {
        $projectId = $this->blueprintsRepo->getCanvasProjectId($canvasId, $canvasType);
        if ($projectId === null || ! $this->can(BlueprintsPermissions::VIEW, $projectId)) {
            return false;
        }

        return $this->blueprintsRepo->getSingleCanvas($canvasId, $canvasType);
    }

    /**
     * List the items of a canvas board, authorized for VIEW against the board's real project.
     * Returns an empty array (the neutral "no items" value) for a missing/foreign/unauthorized
     * board, so a foreign board id is indistinguishable from an empty one.
     *
     * @param  int  $canvasId  Canvas board id
     * @param  string  $canvasType  Board type the board must be of
     * @param  string  $commentModule  Comment module key for the item comment count join
     * @return array<int, array<string, mixed>>
     */
    public function getBoardItems(int $canvasId, string $canvasType, string $commentModule): array
    {
        $projectId = $this->blueprintsRepo->getCanvasProjectId($canvasId, $canvasType);
        if ($projectId === null || ! $this->can(BlueprintsPermissions::VIEW, $projectId)) {
            return [];
        }

        return $this->blueprintsRepo->getCanvasItemsById($canvasId, $commentModule);
    }

    /**
     * Create a canvas item, authorized for CREATE against the target board's real project.
     *
     * @param  array<string, mixed>  $values  Item values (must include `canvasId`)
     * @param  string  $canvasType  Board type the target board must be of
     * @return false|string New item id, or false on insert failure
     *
     * @throws AuthorizationException When the target board is unknown/foreign or CREATE is denied.
     */
    public function createCanvasItem(array $values, string $canvasType): false|string
    {
        $projectId = $this->blueprintsRepo->getCanvasProjectId((int) ($values['canvasId'] ?? 0), $canvasType);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(BlueprintsPermissions::CREATE, $projectId);

        return $this->blueprintsRepo->addCanvasItem($values);
    }

    /**
     * Update a canvas item, authorized for EDIT against the item's real project. The board id
     * is resolved from the existing item — `canvasId` in the payload is ignored for scope, so
     * an item cannot be relocated into another project.
     *
     * @param  array<string, mixed>  $values  Item values (must include `itemId` or `id`)
     * @param  string  $canvasType  Board type the item must belong to
     *
     * @throws AuthorizationException When the item is unknown/foreign or EDIT is denied.
     */
    public function updateCanvasItem(array $values, string $canvasType): void
    {
        $itemId = (int) ($values['itemId'] ?? $values['id'] ?? 0);
        $projectId = $this->blueprintsRepo->getCanvasItemProjectId($itemId, $canvasType);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(BlueprintsPermissions::EDIT, $projectId);

        $this->blueprintsRepo->editCanvasItem($values);
    }

    /**
     * Patch allowlisted columns of a canvas item, authorized for EDIT against the item's real
     * project. Used by the inline board-update API.
     *
     * @param  int  $id  Canvas item id
     * @param  array<string, mixed>  $params  Fields to patch (allowlisted in the repository)
     * @param  string  $canvasType  Board type the item must belong to
     * @return bool False when no allowlisted columns were present (a client error, not a denial)
     *
     * @throws AuthorizationException When the item is unknown/foreign or EDIT is denied.
     */
    public function patchCanvasItem(int $id, array $params, string $canvasType): bool
    {
        $projectId = $this->blueprintsRepo->getCanvasItemProjectId($id, $canvasType);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(BlueprintsPermissions::EDIT, $projectId);

        return $this->blueprintsRepo->patchCanvasItem($id, $params);
    }

    /**
     * Delete a canvas item, authorized for DELETE against the item's real project.
     *
     * @param  int  $id  Canvas item id
     * @param  string  $canvasType  Board type the item must belong to
     *
     * @throws AuthorizationException When the item is unknown/foreign or DELETE is denied.
     */
    public function deleteCanvasItem(int $id, string $canvasType): void
    {
        $projectId = $this->blueprintsRepo->getCanvasItemProjectId($id, $canvasType);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(BlueprintsPermissions::DELETE, $projectId);

        $this->blueprintsRepo->delCanvasItem($id);
    }

    /**
     * Create a canvas board, authorized for CREATE against the target project.
     *
     * @param  array<string, mixed>  $values  Board values (must include `projectId`)
     * @param  string  $canvasType  Board type to create
     * @return false|string New board id, or false on insert failure
     *
     * @throws AuthorizationException When projectId is missing or CREATE is denied.
     */
    public function createBoard(array $values, string $canvasType): false|string
    {
        $projectId = (int) ($values['projectId'] ?? 0);
        if ($projectId === 0) {
            throw new AuthorizationException;
        }
        $this->authorize(BlueprintsPermissions::CREATE, $projectId);

        $newId = $this->blueprintsRepo->addCanvas($values, $canvasType);

        if ($newId !== false) {
            $this->applyStartContent((int) $newId, $canvasType);
        }

        return $newId;
    }

    /**
     * If the blueprint for this canvas type declares a startContent
     * reference, look up the matching ContentTemplate and apply it to the
     * freshly-created board. Silent no-op when the blueprint has no
     * starter content or the referenced template can't be found — board
     * creation never fails because of a missing/broken starter.
     */
    private function applyStartContent(int $canvasId, string $canvasType): void
    {
        $blueprint = $this->templateRegistry->get($canvasType);
        if ($blueprint === null || $blueprint->startContent === null) {
            return;
        }

        $contentTpl = $this->contentTemplates->get($canvasType, $blueprint->startContent);
        if ($contentTpl === null) {
            Log::debug(sprintf(
                'Blueprints::createBoard: blueprint "%s" references startContent "%s" but the template was not found.',
                $canvasType,
                $blueprint->startContent
            ));

            return;
        }

        $applier = $this->contentTemplates->applierFor($canvasType);
        if ($applier === null) {
            return;
        }

        try {
            $applier->apply($canvasId, $contentTpl, [
                'userId' => (int) session('userdata.id'),
                'mode' => 'add',
            ]);
        } catch (\Throwable $e) {
            // Don't let a bad starter break board creation. Log + move on.
            Log::warning(sprintf(
                'Blueprints::createBoard: startContent "%s" failed to apply to canvas %d: %s',
                $blueprint->startContent,
                $canvasId,
                $e->getMessage()
            ));
        }
    }

    /**
     * Rename a canvas board, authorized for EDIT against the board's real project.
     *
     * @param  int  $canvasId  Canvas board id
     * @param  string  $title  New title
     * @param  string  $canvasType  Board type the board must be of
     *
     * @throws AuthorizationException When the board is unknown/foreign or EDIT is denied.
     */
    public function renameBoard(int $canvasId, string $title, string $canvasType): mixed
    {
        $projectId = $this->blueprintsRepo->getCanvasProjectId($canvasId, $canvasType);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(BlueprintsPermissions::EDIT, $projectId);

        return $this->blueprintsRepo->updateCanvas(['id' => $canvasId, 'title' => $title]);
    }

    /**
     * Copy a canvas board into a target project. Requires VIEW on the SOURCE board's real
     * project (you must be able to read what you copy) AND CREATE in the target project.
     *
     * @param  int  $sourceCanvasId  Source board id
     * @param  int  $targetProjectId  Destination project id
     * @param  int  $authorId  Author of the new board
     * @param  string  $title  New board title
     * @param  string  $canvasType  Board type the source must be of (and the copy will be)
     * @return int New board id
     *
     * @throws AuthorizationException When the source is unknown/foreign, or VIEW/CREATE is denied.
     */
    public function copyBoard(int $sourceCanvasId, int $targetProjectId, int $authorId, string $title, string $canvasType): int
    {
        $sourceProjectId = $this->blueprintsRepo->getCanvasProjectId($sourceCanvasId, $canvasType);
        if ($sourceProjectId === null || $targetProjectId <= 0) {
            throw new AuthorizationException;
        }
        $this->authorize(BlueprintsPermissions::VIEW, $sourceProjectId);
        $this->authorize(BlueprintsPermissions::CREATE, $targetProjectId);

        return $this->blueprintsRepo->copyCanvas($targetProjectId, $sourceCanvasId, $authorId, $title, $canvasType);
    }

    /**
     * Merge a source board's items into a target board. Requires EDIT on the TARGET board's
     * real project and VIEW on the SOURCE board's real project — both resolved by id, so
     * neither can cross a project boundary.
     *
     * @param  int  $targetCanvasId  Board receiving the items
     * @param  int  $sourceCanvasId  Board whose items are copied
     * @param  string  $canvasType  Board type both boards must be of
     *
     * @throws AuthorizationException When either board is unknown/foreign, or EDIT/VIEW is denied.
     */
    public function mergeBoard(int $targetCanvasId, int $sourceCanvasId, string $canvasType): bool
    {
        $targetProjectId = $this->blueprintsRepo->getCanvasProjectId($targetCanvasId, $canvasType);
        $sourceProjectId = $this->blueprintsRepo->getCanvasProjectId($sourceCanvasId, $canvasType);
        if ($targetProjectId === null || $sourceProjectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(BlueprintsPermissions::EDIT, $targetProjectId);
        $this->authorize(BlueprintsPermissions::VIEW, $sourceProjectId);

        return $this->blueprintsRepo->mergeCanvas($targetCanvasId, $sourceCanvasId);
    }

    /**
     * Delete a canvas board (and its items), authorized for DELETE against the board's real
     * project.
     *
     * @param  int  $canvasId  Canvas board id
     * @param  string  $canvasType  Board type the board must be of
     *
     * @throws AuthorizationException When the board is unknown/foreign or DELETE is denied.
     */
    public function deleteBoard(int $canvasId, string $canvasType): void
    {
        $projectId = $this->blueprintsRepo->getCanvasProjectId($canvasId, $canvasType);
        if ($projectId === null) {
            throw new AuthorizationException;
        }
        $this->authorize(BlueprintsPermissions::DELETE, $projectId);

        $this->blueprintsRepo->deleteCanvas($canvasId);
    }

    /**
     * Import a canvas board from an XML file.
     *
     * Parses the XML, validates its structure, then creates a new canvas board
     * with all items from the file.
     *
     * @param  string  $filename  Path to the XML file
     * @param  string  $canvasSlug  Canvas type slug (e.g., "swot", "lean")
     * @param  int  $projectId  Project identifier
     * @param  int  $authorId  Author user identifier
     * @return bool|int False on failure, or the new canvas board ID on success
     *
     * @throws BindingResolutionException
     * @throws AuthorizationException When the user cannot create canvases in $projectId.
     *
     * @api
     */
    #[RequiresPermission(BlueprintsPermissions::CREATE, entityScoped: true)]
    public function import(string $filename, string $canvasSlug, int $projectId, int $authorId): bool|int
    {
        // Authorize CREATE against the TARGET project (the destination of the import), not the
        // session project — import is reachable via RPC with an arbitrary projectId.
        $this->authorize(BlueprintsPermissions::CREATE, $projectId);

        $template = $this->templateRegistry->get($canvasSlug);
        if ($template === null) {
            Log::error("Blueprints import failed: unknown canvas slug '{$canvasSlug}'");

            return false;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $users = app()->make(UserRepository::class);

        $canvasData = file_get_contents($filename);
        if ($canvasData === false) {
            return false;
        }

        $oldErrorReporting = error_reporting(error_reporting() & ~E_WARNING);
        $status = $dom->loadXML($canvasData);
        error_reporting($oldErrorReporting);
        if ($status === false) {
            return false;
        }

        $canvasAry = ['projectId' => $projectId, 'author' => $authorId];
        $recordsAry = [];

        $canvasNodeList = $dom->getElementsByTagName('canvas');
        if ($canvasNodeList->count() !== 1) {
            return false;
        }

        $importedCanvasName = $canvasNodeList->item(0)->getAttribute('key');

        $titleNodeList = $canvasNodeList->item(0)->getElementsByTagName('title');
        if ($titleNodeList->count() !== 1) {
            return false;
        }
        $canvasAry['title'] = $titleNodeList->item(0)->nodeValue;

        $dataNodeList = $canvasNodeList->item(0)->getElementsByTagName('content');
        if ($dataNodeList->count() !== 1) {
            return false;
        }

        $elementNodeList = $dataNodeList->item(0)->getElementsByTagName('element');

        foreach ($elementNodeList as $elementNode) {
            if (! $elementNode->hasAttribute('key')) {
                return false;
            }
            $elementKey = $elementNode->getAttribute('key');

            $itemNodeList = $elementNode->getElementsByTagName('item');
            foreach ($itemNodeList as $itemName) {
                $authorNodeList = $itemName->getElementsByTagName('author');
                if ($authorNodeList->count() !== 1) {
                    return false;
                }
                if (! $authorNodeList->item(0)->hasAttribute('firstname')) {
                    return false;
                }
                $authorFirstname = $authorNodeList->item(0)->getAttribute('firstname');
                if (! $authorNodeList->item(0)->hasAttribute('lastname')) {
                    return false;
                }
                $authorLastname = $authorNodeList->item(0)->getAttribute('lastname');
                $author = $users->getUserIdByName($authorFirstname, $authorLastname);
                if ($author === false) {
                    $author = $authorId;
                }

                $descriptionNodeList = $itemName->getElementsByTagName('description');
                if ($descriptionNodeList->count() !== 1) {
                    return false;
                }
                $description = $descriptionNodeList->item(0)->nodeValue;

                $statusNodeList = $itemName->getElementsByTagName('status');
                if ($statusNodeList->count() !== 1) {
                    return false;
                }
                if (! $statusNodeList->item(0)->hasAttribute('key')) {
                    return false;
                }
                $statusKey = $statusNodeList->item(0)->getAttribute('key');

                $relatesNodeList = $itemName->getElementsByTagName('relates');
                if ($relatesNodeList->count() !== 1) {
                    return false;
                }
                if (! $relatesNodeList->item(0)->hasAttribute('key')) {
                    return false;
                }
                $relates = $relatesNodeList->item(0)->getAttribute('key');

                $assumptionsNodeList = $itemName->getElementsByTagName('assumptions');
                if ($assumptionsNodeList->count() !== 1) {
                    return false;
                }
                $assumptions = empty($assumptionsNodeList->item(0)->nodeValue) ? '' :
                    $dom->saveHTML($assumptionsNodeList->item(0)->firstChild);

                $importDataNodeList = $itemName->getElementsByTagName('data');
                if ($importDataNodeList->count() !== 1) {
                    return false;
                }
                $data = empty($importDataNodeList->item(0)->nodeValue) ? '' :
                    $dom->saveHTML($importDataNodeList->item(0)->firstChild);

                $conclusionNodeList = $itemName->getElementsByTagName('conclusion');
                if ($conclusionNodeList->count() !== 1) {
                    return false;
                }
                $conclusion = empty($conclusionNodeList->item(0)->nodeValue) ? '' :
                    $dom->saveHTML($conclusionNodeList->item(0)->firstChild);

                $recordsAry[] = [
                    'description' => $description,
                    'assumptions' => $assumptions,
                    'data' => $data,
                    'conclusion' => $conclusion,
                    'box' => $elementKey,
                    'author' => $author,
                    'status' => $statusKey,
                    'relates' => $relates,
                    'milestoneId' => '',
                ];
            }
        }

        $expectedCanvasKey = $template->getDatabaseType();
        if ($expectedCanvasKey !== $importedCanvasName) {
            return false;
        }

        $canvasType = $template->getDatabaseType();

        $canvasAry['title'] .= ' [imported]';
        if ($this->blueprintsRepo->existCanvas($projectId, $canvasAry['title'], $canvasType)) {
            return false;
        }

        $canvasId = $this->blueprintsRepo->addCanvas($canvasAry, $canvasType);
        if ($canvasId === false) {
            return false;
        }

        foreach ($recordsAry as $record) {
            $record['canvasId'] = $canvasId;
            $this->blueprintsRepo->addCanvasItem($record);
        }

        return (int) $canvasId;
    }

    /**
     * Get progress percentages for canvas boards in a project.
     *
     * Counts items per box type for each canvas and calculates what fraction
     * of box types have at least one item.
     *
     * @param  string  $projectId  Project identifier (empty string for all)
     * @param  array<int, string>  $boards  Array of database canvas types to check
     * @return array<string, float> Map of canvas type to max progress (0.0 to 1.0)
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    #[RequiresPermission(BlueprintsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getBoardProgress(string $projectId = '', array $boards = []): array
    {
        $values = $this->blueprintsRepo->getCanvasProgressCount((int) $projectId, $boards);

        $results = [];

        foreach ($values as $row) {
            $canvasType = $row['canvasType'];

            if (! isset($results[$canvasType])) {
                $results[$canvasType] = [];
            }

            if (! isset($results[$canvasType][$row['canvasId']])) {
                $template = $this->templateRegistry->getByDatabaseType($canvasType);
                $results[$canvasType][$row['canvasId']] = [];

                if ($template !== null) {
                    foreach ($template->boxes as $type => $box) {
                        $results[$canvasType][$row['canvasId']][$type] = 0;
                    }
                }
            }

            if ($row['box'] != '' && $row['boxItems'] > 0) {
                $results[$canvasType][$row['canvasId']][$row['box']]++;
            }
        }

        $progressResults = [];

        foreach ($results as $key => &$canvas) {
            $template = $this->templateRegistry->getByDatabaseType($key);
            $numOfBoxes = $template !== null ? count($template->boxes) : 1;

            if (! isset($progressResults[$key])) {
                $progressResults[$key] = '';
            }

            $maxProgress = 0;
            foreach ($canvas as $canvasId => $singleCanvas) {
                $numOfBoxesFilled = 0;
                foreach ($singleCanvas as $box) {
                    if ($box > 0) {
                        $numOfBoxesFilled++;
                    }
                }
                $progress = $numOfBoxesFilled / $numOfBoxes;
                if ($progress > $maxProgress) {
                    $maxProgress = $progress;
                }
            }

            $progressResults[$key] = $maxProgress;
        }

        return $progressResults;
    }

    /**
     * Get canvas boards ordered by last updated item.
     *
     * @param  int|null  $projectId  Project identifier (null for all)
     * @param  array<int, string>  $boards  Array of database canvas types to filter by
     * @return array<int, array<string, mixed>> List of canvas boards with modification dates
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    #[RequiresPermission(BlueprintsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getLastUpdatedCanvas(?int $projectId = null, array $boards = []): array
    {
        return $this->blueprintsRepo->getLastUpdatedCanvas((int) $projectId, $boards);
    }

    /**
     * Translate the box labels from a CanvasTemplate.
     *
     * Returns the boxes array with title values run through the language service.
     *
     * @param  CanvasTemplate  $template  Canvas template
     * @return array<string, array<string, mixed>> Translated box definitions
     */
    public function getTranslatedBoxes(CanvasTemplate $template): array
    {
        $boxes = $template->boxes;
        foreach ($boxes as $key => $data) {
            if (isset($data['title'])) {
                $boxes[$key]['title'] = $this->language->__($data['title']);
            }
        }

        return $boxes;
    }

    /**
     * Translate the status labels from a CanvasTemplate.
     *
     * @param  CanvasTemplate  $template  Canvas template
     * @return array<string, array<string, mixed>> Translated status labels
     */
    public function getTranslatedStatusLabels(CanvasTemplate $template): array
    {
        $statusLabels = $template->statusLabels;
        foreach ($statusLabels as $key => $data) {
            if (isset($data['title'])) {
                $statusLabels[$key]['title'] = $this->language->__($data['title']);
            }
        }

        return $statusLabels;
    }

    /**
     * Translate the relates labels from a CanvasTemplate.
     *
     * @param  CanvasTemplate  $template  Canvas template
     * @return array<string, array<string, mixed>> Translated relates labels
     */
    public function getTranslatedRelatesLabels(CanvasTemplate $template): array
    {
        $relatesLabels = $template->relatesLabels;
        foreach ($relatesLabels as $key => $data) {
            if (isset($data['title'])) {
                $relatesLabels[$key]['title'] = $this->language->__($data['title']);
            }
        }

        return $relatesLabels;
    }

    /**
     * Translate the data labels from a CanvasTemplate.
     *
     * @param  CanvasTemplate  $template  Canvas template
     * @return array<int, array<string, mixed>> Translated data labels
     */
    public function getTranslatedDataLabels(CanvasTemplate $template): array
    {
        $dataLabels = $template->dataLabels;
        foreach ($dataLabels as $key => $data) {
            if (isset($data['title'])) {
                $dataLabels[$key]['title'] = $this->language->__($data['title']);
            }
        }

        return $dataLabels;
    }

    /**
     * Translate the disclaimer string from a CanvasTemplate.
     *
     * @param  CanvasTemplate  $template  Canvas template
     * @return string Translated disclaimer, or empty string if none
     */
    public function getTranslatedDisclaimer(CanvasTemplate $template): string
    {
        if (empty($template->disclaimer)) {
            return '';
        }

        return $this->language->__($template->disclaimer);
    }

    /**
     * Returns the metadata map for every selectable blueprint board (canvas) type.
     *
     * Each entry holds the routing module, the translatable name/description labels,
     * an icon class and the (empty) placeholders used when no board of that type exists yet.
     *
     * @return array<string, array<string, string>> Board type keyed metadata map.
     */
    public function getBoardMetadata(): array
    {
        return [
            'logicmodelcanvas' => ['module' => 'logicmodelcanvas',  'name' => 'label.logicmodelcanvas', 'description' => 'description.logicmodelcanvas', 'icon' => 'fa-solid fa-diagram-project', 'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'valuecanvas' => ['module' => 'blueprints/value',       'name' => 'label.valuecanvas',  'description' => 'description.valuecanvas', 'icon' => 'fa-solid fa-ranking-star',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'swotcanvas' => ['module' => 'blueprints/swot',     'name' => 'label.swotcanvas', 'description' => 'description.swotcanvas', 'icon' => 'fa-solid fa-dumbbell',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'obmcanvas' => ['module' => 'blueprints/obm',     'name' => 'label.obmcanvas',       'description' => 'description.obmcanvas', 'icon' => 'fa-solid fa-object-group', 'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'leancanvas' => ['module' => 'blueprints/lean',     'name' => 'label.leancanvas',       'description' => 'description.leancanvas', 'icon' => 'fa-solid fa-person-circle-question', 'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'minempathycanvas' => ['module' => 'blueprints/minempathy',       'name' => 'label.minempathycanvas',  'description' => 'description.minempathycanvas', 'icon' => 'fa-solid fa-heart-circle-check',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'sbcanvas' => ['module' => 'blueprints/sb',       'name' => 'label.sbcanvas',  'description' => 'description.sbcanvas',           'icon' => 'fa-solid fa-briefcase',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'riskscanvas' => ['module' => 'blueprints/risks',    'name' => 'label.riskscanvas',  'description' => 'description.riskscanvas',        'icon' => 'fa-solid fa-triangle-exclamation',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'eacanvas' => ['module' => 'blueprints/ea',       'name' => 'label.eacanvas', 'description' => 'description.eacanvas', 'icon' => 'fa-solid fa-seedling',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'lbmcanvas' => ['visible' => '0', 'module' => 'blueprints/lbm',      'name' => 'label.lbmcanvas', 'description' => 'description.lbmcanvas', 'icon' => 'fa-solid fa-building',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'dbmcanvas' => ['visible' => '0', 'module' => 'blueprints/dbm',      'name' => 'label.dbmcanvas', 'description' => 'description.dbmcanvas', 'icon' => 'fa-solid fa-city',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'sqcanvas' => ['visible' => '0', 'module' => 'blueprints/sq',       'name' => 'label.sqcanvas', 'description' => 'description.sqcanvas', 'icon' => 'fa fa-chess',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'insightscanvas' => ['module' => 'blueprints/insights', 'name' => 'label.insightscanvas', 'description' => 'description.insightscanvas',      'icon' => 'fa-solid fa-arrows-down-to-people',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'cpcanvas' => ['visible' => '0', 'module' => 'blueprints/cp',       'name' => 'label.cpcanvas', 'description' => 'description.cpcanvas', 'icon' => 'fa-solid fa-list-check',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'smcanvas' => ['visible' => '0', 'module' => 'blueprints/sm',       'name' => 'label.smcanvas', 'description' => 'description.smcanvas', 'icon' => 'fa-solid fa-comments-dollar',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
            'emcanvas' => ['visible' => '0', 'module' => 'blueprints/em',       'name' => 'label.emcanvas', 'description' => 'description.emcanvas', 'icon' => 'fa-solid fa-hand-holding-heart',  'numberOfBoards' => '', 'lastTitle' => '', 'lastCanvasId' => '', 'lastUpdate' => ''],
        ];
    }

    /**
     * Returns the ordered list of blueprint board (canvas) types used to query progress and recent activity.
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
        // Note: logicmodelcanvas is intentionally absent. It is its own domain (no
        // Blueprints YAML template), so it can't go through the template-based
        // progress/recent computation here — it would hit undefined box keys
        // (e.g. "lm_inputs"). It still appears in the hub via getBoardMetadata().
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
     * Builds the blueprints boards overview for a project.
     *
     * Loads the recently updated boards and board progress for the project, merges the recent
     * activity into the board metadata and returns a ready-to-render structure for the boards page.
     *
     * @param  int  $projectId  Active project identifier.
     * @return array{recentProgressCanvas: array<string, array<string, mixed>>, otherBoards: array<string, array<string, string>>, recentlyUpdatedCanvas: array<int, array<string, mixed>>, canvasProgress: array<string, float|string>} Render-ready overview data.
     *
     * @throws BindingResolutionException
     */
    public function getBoardsOverview(int $projectId): array
    {
        $boardMetadata = $this->getBoardMetadata();
        $boards = $this->getBoardTypes();

        $recentlyUpdatedCanvas = $this->getLastUpdatedCanvas($projectId, $boards);

        $recentProgressCanvas = $this->buildRecentProgressCanvas($recentlyUpdatedCanvas, $boardMetadata);

        $canvasProgress = $this->getBoardProgress((string) $projectId, $boards);

        return [
            'recentProgressCanvas' => $recentProgressCanvas,
            'otherBoards' => $boardMetadata,
            'recentlyUpdatedCanvas' => $recentlyUpdatedCanvas,
            'canvasProgress' => $canvasProgress,
        ];
    }
}
