<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'lbm';
?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasTop'),
    array_merge($__data, ['canvasName' => 'lbm'])
)->render(); ?>

    <?php if (count($tpl->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(3 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 33.33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'lbm', 'elementName' => 'lbm_customers'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width: 33.33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'lbm', 'elementName' => 'lbm_offerings'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width: 33.33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'lbm', 'elementName' => 'lbm_capabilities'])
                            )->render(); ?>
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 100%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'lbm', 'elementName' => 'lbm_financials'])
                            )->render(); ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    <?php } ?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasBottom'),
    array_merge($__data, ['canvasName' => 'lbm'])
)->render(); ?>
