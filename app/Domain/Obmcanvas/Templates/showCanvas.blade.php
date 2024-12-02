<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'obm';
?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasTop'),
    array_merge($__data, ['canvasName' => 'obm'])
)->render(); ?>

<?php if (count($tpl->get('allCanvas')) > 0) { ?>
    <div id="sortableCanvasKanban" class="sortableTicketList disabled">
        <div class="row-fluid">
            <div class="column" style="width: 100%; min-width: calc(5 * 250px + 50px);">

                <div class="row canvas-row" id="firstRow">

                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'obm', 'elementName' => 'obm_kp'])
                        )->render(); ?>
                    </div>

                    <div class="column" style="width: 20%">
                        <div class="row canvas-row" id="firstRowTop">
                            <div class="column" style="width: 100%; padding-top: 0px">
                                <?php echo $tpl->viewFactory->make(
                                    $tpl->getTemplatePath('canvas', 'element'),
                                    array_merge($__data, ['canvasName' => 'obm', 'elementName' => 'obm_ka'])
                                )->render(); ?>
                            </div>
                        </div>
                        <div class="row canvas-row" id="firstRowBottom">
                            <div class="column" style="width: 100%">
                                <?php echo $tpl->viewFactory->make(
                                    $tpl->getTemplatePath('canvas', 'element'),
                                    array_merge($__data, ['canvasName' => 'obm', 'elementName' => 'obm_kr'])
                                )->render(); ?>
                            </div>
                        </div>
                    </div>

                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'obm', 'elementName' => 'obm_vp'])
                        )->render(); ?>
                    </div>

                    <div class="column" style="width: 20%">
                        <div class="row canvas-row" id="firstRowTop">
                            <div class="column" style="width: 100%; padding-top: 0px">
                                <?php echo $tpl->viewFactory->make(
                                    $tpl->getTemplatePath('canvas', 'element'),
                                    array_merge($__data, ['canvasName' => 'obm', 'elementName' => 'obm_cr'])
                                )->render(); ?>
                            </div>
                        </div>
                        <div class="row canvas-row" id="firstRowBottom">
                            <div class="column" style="width: 100%">
                                <?php echo $tpl->viewFactory->make(
                                    $tpl->getTemplatePath('canvas', 'element'),
                                    array_merge($__data, ['canvasName' => 'obm', 'elementName' => 'obm_ch'])
                                )->render(); ?>
                            </div>
                        </div>
                    </div>

                    <div class="column" style="width: 20%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'obm', 'elementName' => 'obm_cs'])
                        )->render(); ?>
                    </div>

                </div>

                <div class="row canvas-row" id="secondRow">

                    <div class="column" style="width: 50%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'obm', 'elementName' => 'obm_fc'])
                        )->render(); ?>
                    </div>

                    <div class="column" style="width: 50%">
                        <?php echo $tpl->viewFactory->make(
                            $tpl->getTemplatePath('canvas', 'element'),
                            array_merge($__data, ['canvasName' => 'obm', 'elementName' => 'obm_fr'])
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
    array_merge($__data, ['canvasName' => 'obm'])
)->render(); ?>
