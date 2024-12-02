<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'ea';
?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasTop'),
    array_merge($__data, ['canvasName' => 'ea'])
)->render(); ?>

    <?php if (count($tpl->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(4 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width:33.33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'ea', 'elementName' => 'ea_political'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width:33.33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'ea', 'elementName' => 'ea_economic'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width:33.33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'ea', 'elementName' => 'ea_societal'])
                            )->render(); ?>
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width:33.33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'ea', 'elementName' => 'ea_technological'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width:33.33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'ea', 'elementName' => 'ea_legal'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width:33.33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'ea', 'elementName' => 'ea_ecological'])
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
    array_merge($__data, ['canvasName' => 'ea'])
)->render(); ?>
