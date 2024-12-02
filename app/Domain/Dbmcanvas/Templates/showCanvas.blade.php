<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'dbm';
?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'delCanvasItem'),
    array_merge($__data, ['canvasName' => 'dbm'])
)->render(); ?>

    <?php if (count($tpl->get('allCanvas')) > 0) { ?>
        <div id="sortableCanvasKanban" class="sortableTicketList disabled">
            <div class="row-fluid">
                <div class="column" style="width: 100%; min-width: calc(8 * 250px);">

                    <div class="row canvas-row" id="firstRow">
                        <div class="column" style="width: 20%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_cs'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_cr'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_ovp'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width: 13.33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_kad'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width: 13.33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_kac'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width: 13.33%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_kao'])
                            )->render(); ?>
                        </div>
                    </div>

                    <div class="row canvas-row" id="secondRow">
                        <div class="column" style="width: 20%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_cj'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_cd'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width: 20%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_ops'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width:40%">
                            <div class="row canvas-row" id="secondRowTop">
                                <div class="column" style="width:50%; padding-top: 0px">
                                    <?php echo $tpl->viewFactory->make(
                                        $tpl->getTemplatePath('canvas', 'element'),
                                        array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_krp'])
                                    )->render(); ?>
                                </div>
                                <div class="column" style="width:50%; padding-top: 0">
                                    <?php echo $tpl->viewFactory->make(
                                        $tpl->getTemplatePath('canvas', 'element'),
                                        array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_krc'])
                                    )->render(); ?>
                                </div>
                            </div>
                            <div class="row canvas-row" id="secondRowBottom">
                                <div class="column" style="width:50%; padding-bottom: 0">
                                    <?php echo $tpl->viewFactory->make(
                                        $tpl->getTemplatePath('canvas', 'element'),
                                        array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_krl'])
                                    )->render(); ?>
                                </div>
                                <div class="column" style="width:50%; padding-bottom: 0">
                                    <?php echo $tpl->viewFactory->make(
                                        $tpl->getTemplatePath('canvas', 'element'),
                                        array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_krs'])
                                    )->render(); ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row canvas-row" id="thirdRow">
                        <div class="column" style="width: 50%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_fr'])
                            )->render(); ?>
                        </div>
                        <div class="column" style="width: 50%">
                            <?php echo $tpl->viewFactory->make(
                                $tpl->getTemplatePath('canvas', 'element'),
                                array_merge($__data, ['canvasName' => 'dbm', 'elementName' => 'dbm_fc'])
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
    array_merge($__data, ['canvasName' => 'dbm'])
)->render(); ?>
