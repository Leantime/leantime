<?php

/**
 * Delete Canvas
 */

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'delCanvas'),
    array_merge($__data, ['canvasName' => 'insights'])
)->render();
