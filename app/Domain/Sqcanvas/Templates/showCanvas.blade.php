<?php

/**
 * Template
 */
defined('RESTRICTED') or exit('Restricted access');
foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$canvasName = 'sq';
?>

<?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'showCanvasTop'), array_merge($__data, ['canvasName' => 'sq']))->render(); ?>

<?php if (count($allCanvas) > 0) { ?>
<div id="sortableCanvasKanban" class="sortableTicketList disabled">
    <div class="row-fluid">
        <div class="column" style="width: 100%; min-width: calc(500px);">

            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'sq', 'elementName' => 'sq_qa']))->render(); ?>
                </div>
            </div>
            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'sq', 'elementName' => 'sq_qb']))->render(); ?>
                </div>
            </div>
            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'sq', 'elementName' => 'sq_qc']))->render(); ?>
                </div>
            </div>
            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'sq', 'elementName' => 'sq_qd']))->render(); ?>
                </div>
            </div>
            <div class="row canvas-row">
                <div class="column" style="width:100%">
                    <?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'element'), array_merge($__data, ['canvasName' => 'sq', 'elementName' => 'sq_qe']))->render(); ?>
                </div>
            </div>

        </div>
    </div>
</div>
<div class="clearfix"></div>
<?php } ?>

<?php echo $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'showCanvasBottom'), array_merge($__data, ['canvasName' => 'sq']))->render(); ?>
