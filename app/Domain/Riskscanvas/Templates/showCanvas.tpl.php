<?php

/**
 * Template
 */
defined('RESTRICTED') or exit('Restricted access');

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}

$canvasName = 'risks';
?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasTop'),
    array_merge($__data, ['canvasName' => 'risks'])
)->render(); ?>

    <?php if (count($tpl->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
          <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(2 * 250px);">

            <div class="row canvas-row" id="firstRow">
                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'risks', 'elementName' => 'risks_imp_low_pro_high'])
                    )->render(); ?>
                </div>
                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'risks', 'elementName' => 'risks_imp_high_pro_high'])
                    )->render(); ?>
                </div>
            </div>

            <div class="row canvas-row" id="secondRow">
                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'risks', 'elementName' => 'risks_imp_low_pro_low'])
                    )->render(); ?>
                </div>
                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'risks', 'elementName' => 'risks_imp_high_pro_low'])
                    )->render(); ?>
                </div>
            </div>
          </div></div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasBottom'),
    array_merge($__data, ['canvasName' => 'risks'])
)->render(); ?>
