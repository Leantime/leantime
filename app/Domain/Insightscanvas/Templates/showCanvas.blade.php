<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'insights';
?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasTop'),
    array_merge($__data, ['canvasName' => 'insights'])
)->render(); ?>

    <?php if (count($tpl->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
          <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(5 * 250px);">

            <div class="row canvas-row" id="firstRow">
                <?php foreach ($canvasTypes as $key => $box) { ?>
                    <div class="column" style="width:20%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'insights', 'elementName' => $key])
                        )->render(); ?>
                    </div>
                <?php } ?>
            </div>
          </div></div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasBottom'),
    array_merge($__data, ['canvasName' => 'insights'])
)->render(); ?>
