<?php

/**
 * Controller / Edit Canvas Item
 */

namespace Leantime\Domain\Logicmodelcanvas\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Domain\Blueprints\Permissions\BlueprintsPermissions;
use Leantime\Domain\Blueprints\Services\Blueprints as BlueprintsService;

class EditCanvasItem extends \Leantime\Domain\Canvas\Controllers\EditCanvasItem
{
    protected const CANVAS_NAME = 'logicmodel';

    /**
     * Persist the Stage (box) and Priority (impact) selects on save.
     *
     * The shared Canvas save path (parent::post → service::updateCanvasItem) never
     * writes `box` and does not forward `impact`, so without this the Stage
     * dropdown would be a no-op and saving would reset Priority. Patch both
     * after the base save for existing items; new items already receive their
     * box on insert via the create path.
     *
     * The base post() already EDIT-authorizes the item against its real project; the extra
     * patch is routed through the same project-authorized service method for consistency.
     *
     * @param  array  $params  Request parameters
     */
    #[RequiresPermission(BlueprintsPermissions::EDIT, entityScoped: true)]
    public function post($params)
    {
        $response = parent::post($params);

        $isExistingItemSave = isset($params['changeItem'])
            && ! empty($params['itemId'])
            && ! empty($params['description']);

        if ($isExistingItemSave) {
            $patch = [];

            if (! empty($params['box'])) {
                $patch['box'] = $params['box'];
            }

            if (array_key_exists('impact', $params)) {
                $patch['impact'] = $params['impact'];
            }

            if ($patch !== []) {
                app()->make(BlueprintsService::class)
                    ->patchCanvasItem((int) $params['itemId'], $patch, static::CANVAS_NAME.'canvas');
            }
        }

        return $response;
    }
}
