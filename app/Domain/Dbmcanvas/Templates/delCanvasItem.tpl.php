<?php

/**
 * Delete Item.
 */
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'delCanvasItem'),
    array_merge($__data, ['canvasName' => 'dbm'])
)->render();
