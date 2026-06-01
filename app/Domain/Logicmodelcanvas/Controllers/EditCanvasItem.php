<?php

/**
 * Controller / Edit Canvas Item
 */

namespace Leantime\Domain\Logicmodelcanvas\Controllers;

use Leantime\Domain\Logicmodelcanvas\Repositories\Logicmodelcanvas as LogicmodelcanvasRepository;

class EditCanvasItem extends \Leantime\Domain\Canvas\Controllers\EditCanvasItem
{
    protected const CANVAS_NAME = 'logicmodel';

    /**
     * Persist the Stage (box) and Priority (impact) selects on save.
     *
     * The shared Canvas save path (parent::post → repo::editCanvasItem) never
     * writes `box` and does not forward `impact`, so without this the Stage
     * dropdown would be a no-op and saving would reset Priority. Patch both
     * after the base save for existing items; new items already receive their
     * box on insert via addCanvasItem.
     *
     * @param  array  $params  Request parameters
     */
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
                app()->make(LogicmodelcanvasRepository::class)
                    ->patchCanvasItem((int) $params['itemId'], $patch);
            }
        }

        return $response;
    }
}
