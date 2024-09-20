<?php

/**
 * Dialog.
 */
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'canvasDialog'),
    array_merge($__data, ['canvasName' => 'insights'])
)->render();
