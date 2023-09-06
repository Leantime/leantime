<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}

$canvasName = 'retros';
?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasTop'),
    array_merge($__data, ['canvasName' => 'retros'])
)->render(); ?>

    <?php if (count($tpl->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(3 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'retros', 'elementName' => 'well'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width: 33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'retros', 'elementName' => 'notwell'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width: 33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'retros', 'elementName' => 'startdoing'])
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
    array_merge($__data, ['canvasName' => 'retros'])
)->render(); ?>
