<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'sm';
?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasTop'),
    array_merge($__data, ['canvasName' => 'sm'])
)->render(); ?>

    <?php if (count($tpl->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(500px);">

                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'sm', 'elementName' => 'sm_qa'])
                            )->render(); ?>
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'sm', 'elementName' => 'sm_qb'])
                            )->render(); ?>
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'sm', 'elementName' => 'sm_qc'])
                            )->render(); ?>
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'sm', 'elementName' => 'sm_qd'])
                            )->render(); ?>
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'sm', 'elementName' => 'sm_qe'])
                            )->render(); ?>
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'sm', 'elementName' => 'sm_qf'])
                            )->render(); ?>
                        </div>
                    </div>
                    <div class="row canvas-row">
                        <div class="column" style="width:100%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'sm', 'elementName' => 'sm_qg'])
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
    array_merge($__data, ['canvasName' => 'sm'])
)->render(); ?>
