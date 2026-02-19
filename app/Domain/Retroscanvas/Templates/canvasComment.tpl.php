<?php

/**
 * Comments
 */
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'canvasComment'),
    array_merge($__data, ['canvasName' => 'retros'])
)->render();
