<?php

/**
 * Template
 */

defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'minempathy';
?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasTop'),
    array_merge($__data, ['canvasName' => 'minempathy'])
)->render(); ?>

<?php if (count($tpl->get('allCanvas')) > 0) { ?>
    <div id="sortableCanvasKanban" class="sortableTicketList disabled">
        <div class="row-fluid"><div class="column" style="width: 100%; min-width: calc(2 * 250px);">

            <div class="row canvas-row" id="firstRow">

                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'minempathy', 'elementName' => 'minempathy_who'])
                    )->render(); ?>
                </div>
                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'minempathy', 'elementName' => 'minempathy_struggles'])
                    )->render(); ?>
                </div>
            </div>

            <div class="row canvas-row" id="secondRow">
                <div style="width:25%"></div>
                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'minempathy', 'elementName' => 'minempathy_where'])
                    )->render(); ?>
                </div>
                <div style="width:25%"></div>
            </div>

            <div class="row canvas-row" id="thirdRow">
                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'minempathy', 'elementName' => 'minempathy_why'])
                    )->render(); ?>
                </div>
                <div class="column" style="width:50%">
                    <?php echo $tpl->viewFactory->make(
                        $tpl->getTemplatePath('canvas', 'element'),
                        array_merge($__data, ['canvasName' => 'minempathy', 'elementName' => 'minempathy_how'])
                    )->render(); ?>
                </div>
            </div>
        </div></div>
    </div>
    <div class="clearfix"></div>
<?php } ?>

<?php echo $tpl->viewFactory->make(
    $tpl->getTemplatePath('canvas', 'showCanvasBottom'),
    array_merge($__data, ['canvasName' => 'minempathy'])
)->render(); ?>
