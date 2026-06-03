<?php

namespace Leantime\Domain\Canvas\Services;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;

/**
 * Thin backwards-compatibility shim over the Blueprints service.
 *
 * The canvas system was consolidated into Leantime\Domain\Blueprints. This class
 * is kept ONLY so plugins (and any code) that still call the old canvas service
 * keep working: every method delegates to the Blueprints service.
 *
 * @deprecated Use Leantime\Domain\Blueprints\Services\Blueprints instead.
 *             Do not build new features on this class.
 *
 * @api
 */
class Canvas
{
    /**
     * import - Import canvas from XML file (delegates to Blueprints).
     *
     * @param  string  $filename  File to import
     * @param  string  $canvasName  Legacy canvas type (e.g. "swotcanvas") or slug
     * @param  int  $projectId  Project identifier
     * @param  int  $authorId  Author identifier
     * @return bool|int False if import failed, otherwise the new canvas id
     *
     * @deprecated use Blueprints service
     *
     * @api
     */
    #[RequiresPermission(BlueprintsPermissions::CREATE, projectIdParam: 'projectId')]
    public function import(string $filename, string $canvasName, int $projectId, int $authorId): bool|int
    {
        // Old callers pass the full type ("swotcanvas"); Blueprints works on the slug ("swot").
        $canvasSlug = str_ends_with($canvasName, 'canvas')
            ? substr($canvasName, 0, -strlen('canvas'))
            : $canvasName;

        return app(BlueprintsService::class)->import($filename, $canvasSlug, $projectId, $authorId);
    }

    /**
     * getBoardProgress - completion percentage per canvas type (delegates to Blueprints).
     *
     * @param  string  $projectId  projectId (optional)
     * @param  array  $boards  Array of project board types
     * @return array List of boards with a progress percentage
     *
     * @deprecated use Blueprints service
     *
     * @api
     */
    #[RequiresPermission(BlueprintsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getBoardProgress(string $projectId = '', array $boards = []): array
    {
        return app(BlueprintsService::class)->getBoardProgress($projectId, $boards);
    }

    /**
     * getLastUpdatedCanvas - canvas boards ordered by last updated item (delegates to Blueprints).
     *
     * @param  string  $projectId  projectId (optional)
     * @param  array  $boards  Array of project board types
     * @return array List of boards
     *
     * @deprecated use Blueprints service
     *
     * @api
     */
    #[RequiresPermission(BlueprintsPermissions::VIEW, projectIdParam: 'projectId')]
    public function getLastUpdatedCanvas(?int $projectId = null, array $boards = []): array
    {
        return app(BlueprintsService::class)->getLastUpdatedCanvas($projectId, $boards);
    }
}
